<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace auth_qrcode\db\model;

use core\exception\coding_exception;
use core\invalid_persistent_exception;
use core\persistent;
use dml_exception;
use Random\RandomException;
use stdClass;

/**
 * Persistent model representing a QR code login attempt.
 *
 * This class handles the storage and retrieval of QR code tokens and their
 * associated session and user information.
 *
 * @package   auth_qrcode
 * @author    Stefan Dani (stefan.dani@ffhs.ch)
 * @copyright 2026 MoodleMootDACH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qrcode extends persistent {
    /**
     * {@inheritDoc}
     */
    const TABLE = 'auth_qrcode';
    /** @var int Maximum number of failed attempts when entering the confirmation code. */
    const CONFIRMATIONCODE_ATTEMPTS = 3;
    /** @var int Number of digits in confirmation code. */
    const CONFIRMATIONCODE_LENGTH = 4;

    /**
     * Creates a new QR code login record.
     *
     * @param string $token The unique token.
     * @param string $sid The session ID string that requested the QR code.
     * @param string|null $useragent The user agent string to parse for OS and browser. Defaults to current UA.
     * @param int|null $duration Optional duration in seconds from now. If not set it defaults to value of
     * auth_qrcode/expirationtime setting.
     * @return self|null The created persistent object, or null if token already exists.
     * @throws coding_exception
     * @throws invalid_persistent_exception
     * @throws dml_exception
     */
    public static function create_record(
        string $token,
        string $sid,
        ?string $useragent = null,
        ?int $duration = null
    ): self|false {
        $existing = self::get_record([
            'token' => $token,
        ]);

        if ($existing) {
            return false;
        }

        $sessionid = self::get_session_id($sid);
        if ($sessionid === null) { // Check like this because sessionid could be 0 (zero).
            return false;
        }

        $ua = $useragent ?? \core_useragent::get_user_agent_string() ?: '';
        $env = self::detect_environment($ua);

        $record = new self();
        $record->set('token', $token);
        $record->set('initial_sessionid', $sessionid);
        $record->set('requester_os', $env['os']);
        $record->set('requester_browser', $env['browser']);
        $record->set('status', 'created');
        $record->set('failedattempts', 0);
        $record->set('timecreated', time());
        $record->set('timeexpires', self::calculate_expiry($duration));
        $record->create();

        return $record;
    }

    /**
     * Sets the user ID for a given token and updates status to authorized. Also resets the expiration timer.
     *
     * @param int $userid
     * @param string $token
     * @param int|null $duration Optional expiration duration in seconds from now. If not set it defaults to value of
     * auth_qrcode/expirationtime setting.
     * @return self|null
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_persistent_exception
     * @throws RandomException
     */
    public static function allow(int $userid, string $token, ?int $duration = null): false|self {
        $existing = self::get_record([
            'token' => $token,
            'status' => 'in_use',
        ]);
        if (!$existing) {
            return false;
        }
        if (self::is_record_expired($existing)) {
            return false;
        }
        $existing->set('userid', $userid);
        $existing->set('status', 'allowed');
        if (get_config('auth_qrcode', 'useconfirmationcode')) {
            $existing->set('confirmationcode', self::generate_confirmation_code());
            $existing->set('failedattempts', 0);
        }
        $existing->set('timeexpires', self::calculate_expiry($duration)); // Extend timer.
        $existing->update();
        return $existing;
    }

    /**
     * Denies a QR code login attempt.
     *
     * @param string $token The unique token.
     * @return bool Whether the QR code login attempt was denied.
     * @throws coding_exception
     * @throws invalid_persistent_exception
     * @throws dml_exception
     */
    public static function deny(string $token): bool {
        $existing = self::get_record([
            'token' => $token,
        ]);
        // It's possible to deny allowed login requests waiting for confirmation.
        if (!$existing || !in_array($existing->get('status'), ['in_use', 'allowed'])) {
            return false;
        }

        $existing->set('status', 'denied');
        $existing->set('timeexpires', self::calculate_expiry(10)); // Set expire to 10 seconds.
        $existing->update();
        return true;
    }

    /**
     * Marks a login attempt as in use. Also resets the expiration timer.
     *
     * @param string $token The unique token.
     * @param int|null $duration Optional expiration duration in seconds from now. If not set it defaults to value of
     * auth_qrcode/expirationtime setting.
     * @return bool true if marked as in use, or false if not found or expired.
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_persistent_exception
     */
    public static function set_in_use(string $token, ?int $duration = null): bool {
        $existing = self::get_record([
            'token' => $token,
            'status' => 'created',
        ]);
        if (!$existing) {
            return false;
        }
        if (self::is_record_expired($existing)) {
            return false;
        }

        $existing->set('status', 'in_use');
        $existing->set('timeexpires', self::calculate_expiry($duration)); // Extend timer.
        $existing->update();
        return true;
    }

    /**
     * Retrieves information about a login attempt.
     *
     * @param string $token The unique token.
     * @return array|false Array with ip, os, and browser, or false if not found.
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_persistent_exception
     */
    public static function get_loginattempt_info(string $token): array|false {
        global $DB;
        $existing = self::get_record(['token' => $token]);
        if (!$existing) {
            return false;
        }

        $session = $DB->get_record('sessions', ['id' => $existing->get('initial_sessionid')], 'lastip');

        return [
            'ip' => $session ? $session->lastip : 'Unknown',
            'os' => $existing->get('requester_os'),
            'browser' => $existing->get('requester_browser'),
        ];
    }

    /**
     * Check whether the confirmation code that was entered is valid. Increments the failed attempts count if an invalid (non-null)
     * code is passed.
     *
     * @param string $token
     * @param string|null $confirmationcode
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_persistent_exception
     */
    public static function check_confirmationcode(string $token, string|null $confirmationcode): bool {
        if (!get_config('auth_qrcode', 'useconfirmationcode')) {
            return true;
        }
        if (is_null($confirmationcode)) {
            return false;
        }
        $existing = self::get_record([
            'token' => $token,
            'status' => 'allowed',
        ]);
        if (!$existing) {
            return false;
        }
        if (self::is_record_expired($existing)) {
            return false;
        }
        $failed = $existing->get('failedattempts');
        if ($failed >= self::CONFIRMATIONCODE_ATTEMPTS) {
            return false;
        }
        if ($existing->get('confirmationcode') !== $confirmationcode) {
            $existing->set('failedattempts', $failed + 1);
            $existing->update();
            return false;
        }
        return true;
    }

    /**
     * Get the remaining number of attempts for a QR code confirmation code.
     *
     * @param string $token
     * @return int|false Remaining number of attempts or false if the record does not exist or is expired.
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_remaining_attempts(string $token): int|false {
        if (!get_config('auth_qrcode', 'useconfirmationcode')) {
            return false;
        }
        $existing = self::get_record([
            'token' => $token,
            'status' => 'allowed',
        ]);
        if (!$existing) {
            return false;
        }
        if (self::is_record_expired($existing)) {
            return false;
        }
        $failed = $existing->get('failedattempts');
        return max(0, self::CONFIRMATIONCODE_ATTEMPTS - $failed);
    }

    /**
     * Checks if a user is allowed to login based on the QR code token and session.
     *
     * @param string $token The unique token.
     * @param string $sid The session ID string.
     * @return string|stdClass 'waiting', 'denied', 'expired' or the user object.
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function can_user_login(string $token, string $sid): string|stdClass {
        $sessionid = self::get_session_id($sid);
        if ($sessionid === null) { // Check like this because sessionid could be 0 (zero).
            return 'expired';
        }
        $existing = self::get_record([
            'token' => $token,
            'initial_sessionid' => $sessionid,
        ]);
        if (!$existing || self::is_record_expired($existing)) {
            return 'expired';
        }
        return match ($existing->get('status')) {
            'allowed' => $existing->get_user_record(),
            'created', 'in_use' => 'waiting',
            'denied' => 'denied',
            default => 'expired',
        };
    }

    /**
     * Returns the user associated with this QR login attempt.
     *
     * @return stdClass|null
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_user_record(): ?stdClass {
        global $DB;

        $userid = $this->get('userid');
        if (!$userid) {
            return null;
        }
        return $DB->get_record('user', ['id' => $userid]);
    }

    /**
     * Delete expired records.
     *
     * @param int|null $timestamp The timestamp to compare against. If null, current time is used.
     * @return void
     * @throws dml_exception
     */
    public static function delete_expired(?int $timestamp = null): void {
        global $DB;
        $timestamp = $timestamp ?? time();
        $DB->delete_records_select(self::TABLE, 'timeexpires < ?', [$timestamp]);
    }

    /**
     * Retrieves the database ID for a session ID string.
     *
     * @param string $sid The session ID string.
     * @return int|null The session database ID or null if not found.
     * @throws dml_exception
     */
    private static function get_session_id(string $sid): ?int {
        global $DB;
        $session = $DB->get_record('sessions', ['sid' => $sid], 'id');
        return $session ? (int) $session->id : null;
    }

    /**
     * Calculates an expiry timestamp.
     *
     * @param int|null $duration Optional duration in seconds from now. Defaults to value of auth_qrcode/expirationtime setting.
     * @return int The calculated expiry timestamp.
     * @throws dml_exception
     */
    private static function calculate_expiry(?int $duration = null): int {
        if ($duration === null) {
            $duration = intval(get_config('auth_qrcode', 'expirationtime') ?: 60);
        }
        return time() + $duration;
    }

    /**
     * Generates a numeric confirmation code.
     *
     * @param int $digits The number of digits to generate.
     * @return string The confirmation code (left-padded with zeros).
     * @throws RandomException
     */
    private static function generate_confirmation_code(int $digits = self::CONFIRMATIONCODE_LENGTH): string {
        $max = pow(10, $digits) - 1;
        $code = random_int(0, $max);
        return str_pad(strval($code), $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Checks if a record is expired and deletes it if so.
     *
     * @param self $record The record to check.
     * @return bool True if expired, false otherwise.
     * @throws coding_exception
     */
    private static function is_record_expired(self $record): bool {
        if ($record->get('timeexpires') < time()) {
            $record->delete();
            return true;
        }
        return false;
    }

    /**
     * Detects the OS and Browser from a User Agent string.
     *
     * This avoids affecting the global core_useragent singleton.
     *
     * @param string $ua The user agent string.
     * @return array Contains 'os' and 'browser' keys.
     */
    private static function detect_environment(string $ua): array {
        $browser = 'Unknown';
        if (preg_match('/Edg\/|Edge\//i', $ua)) {
            $browser = 'Edge';
        } else if (preg_match('/Chrome|CriOS/i', $ua)) {
            $browser = 'Chrome';
        } else if (preg_match('/Firefox|Iceweasel/i', $ua)) {
            $browser = 'Firefox';
        } else if (preg_match('/Safari/i', $ua) && !preg_match('/Chrome|CriOS/i', $ua)) {
            $browser = 'Safari';
        } else if (preg_match('/Opera|OPR\//i', $ua)) {
            $browser = 'Opera';
        } else if (preg_match('/MSIE|Trident\//i', $ua)) {
            $browser = 'Internet Explorer';
        }

        $os = 'Unknown';
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $os = 'iOS';
        } else if (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } else if (preg_match('/Windows/i', $ua)) {
            $os = 'Windows';
        } else if (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $os = 'Mac OS';
        } else if (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        return ['os' => $os, 'browser' => $browser];
    }

    /**
     * {@inheritDoc}
     */
    protected static function define_properties(): array {
        return [
            'token' => ['type' => PARAM_ALPHANUMEXT],
            'initial_sessionid' => ['type' => PARAM_INT],
            'status' => ['type' => PARAM_ALPHAEXT],
            'userid' => ['type' => PARAM_INT, 'null' => NULL_ALLOWED, 'default' => null],
            'confirmationcode' => ['type' => PARAM_ALPHANUM, 'null' => NULL_ALLOWED, 'default' => null],
            'failedattempts' => ['type' => PARAM_INT],
            'timecreated' => ['type' => PARAM_INT],
            'timeexpires' => ['type' => PARAM_INT],
            'requester_os' => ['type' => PARAM_TEXT],
            'requester_browser' => ['type' => PARAM_TEXT],
        ];
    }
}
