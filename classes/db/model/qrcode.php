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

use core\invalid_persistent_exception;
use core\persistent;

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

    /**
     * Creates a new QR code login record.
     *
     * @param string $token The unique token.
     * @param string $sid The session ID string that requested the QR code.
     * @param string|null $useragent The user agent string to parse for OS and browser. Defaults to current UA.
     * @param int|null $duration Optional duration in seconds from now. If not set it defaults to 60.
     * @return self|null The created persistent object, or null if token already exists.
     * @throws \coding_exception
     * @throws invalid_persistent_exception
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
        $record->set('timecreated', time());
        $record->set('timeexpires', self::calculate_expiry($duration));
        $record->create();

        return $record;
    }

    /**
     * Sets the user ID for a given token and updates status to authorized.
     *
     * @param int $userid
     * @param string $token
     * @param int|null $duration Optional duration in seconds from now. If not set it defaults to 60.
     * @return self|null
     */
    public static function allow(int $userid, string $token, ?int $duration = null): false|self {
        $existing = self::get_record([
            'token' => $token,
            'status' => 'in_use',
        ]);
        if ($existing) {
            if (self::is_record_expired($existing)) {
                return false;
            }
            $existing->set('userid', $userid);
            $existing->set('status', 'allowed');
            $existing->set('timeexpires', self::calculate_expiry($duration)); // Extend timer.
            $existing->update();
            return $existing;
        }
        return false;
    }

    /**
     * Denies a QR code login attempt.
     *
     * @param string $token The unique token.
     * @return void
     */
    public static function deny(string $token): void {
        $existing = self::get_record([
            'token' => $token,
            'status' => 'in_use',
        ]);
        if ($existing) {
            $existing->set('status', 'denied');
            $existing->set('timeexpires', self::calculate_expiry(10)); // Set expire to 10 seconds.
            $existing->update();
        }
    }

    /**
     * Retrieves information about a login attempt and marks it as in use.
     *
     * @param string $token The unique token.
     * @param int|null $duration Optional duration in seconds from now. If not set it defaults to 60.
     * @return array|false Array with ip, os, and browser, or false if not found or expired.
     */
    public static function get_loginattemp_info(string $token, ?int $duration = null): array|false {
        global $DB;

        $existing = self::get_record([
            'token' => $token,
            'status' => 'created',
        ]);
        if ($existing) {
            if (self::is_record_expired($existing)) {
                return false;
            }

            $existing->set('status', 'in_use');
            $existing->set('timeexpires', self::calculate_expiry($duration)); // Extend timer.
            $existing->update();

            $session = $DB->get_record('sessions', ['id' => $existing->get('initial_sessionid')], 'lastip');

            return [
                'ip' => $session ? $session->lastip : 'Unknown',
                'os' => $existing->get('requester_os'),
                'browser' => $existing->get('requester_browser'),
            ];
        }
        return false;
    }

    /**
     * Checks if a user is allowed to login based on the QR code token and session.
     *
     * @param string $token The unique token.
     * @param string $sid The session ID string.
     * @return string|\stdClass 'waiting', 'denied', 'expired' or the user object.
     */
    public static function can_user_login(string $token, string $sid): string|\stdClass {
        $sessionid = self::get_session_id($sid);
        if ($sessionid === null) { // Check like this because sessionid could be 0 (zero).
            return 'expired';
        }
        $existing = self::get_record([
            'token' => $token,
            'initial_sessionid' => $sessionid,
        ]);
        if ($existing) {
            if (self::is_record_expired($existing)) {
                return 'expired';
            }
            if ($existing->get('status') === 'allowed') {
                return $existing->get_user_record();
            }
            if ($existing->get('status') === 'created' || $existing->get('status') === 'in_use') {
                return 'waiting';
            }
            if ($existing->get('status') === 'denied') {
                return 'denied';
            }
        }
        return 'expired';
    }

    /**
     * Returns the user associated with this QR login attempt.
     *
     * @return \stdClass|null
     */
    public function get_user_record(): ?\stdClass {
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
     */
    private static function get_session_id(string $sid): ?int {
        global $DB;
        $session = $DB->get_record('sessions', ['sid' => $sid], 'id');
        return $session ? (int) $session->id : null;
    }

    /**
     * Calculates an expiry timestamp.
     *
     * @param int|null $duration Optional duration in seconds from now. Defaults to 60.
     * @return int The calculated expiry timestamp.
     */
    private static function calculate_expiry(?int $duration = null): int {
        return time() + ($duration ?? 60);
    }

    /**
     * Checks if a record is expired and deletes it if so.
     *
     * @param self $record The record to check.
     * @return bool True if expired, false otherwise.
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
    protected static function define_properties() {
        return [
            'token' => ['type' => PARAM_ALPHANUMEXT],
            'initial_sessionid' => ['type' => PARAM_INT],
            'status' => ['type' => PARAM_ALPHAEXT],
            'userid' => ['type' => PARAM_INT, 'null' => NULL_ALLOWED, 'default' => null],
            'timecreated' => ['type' => PARAM_INT],
            'timeexpires' => ['type' => PARAM_INT],
            'requester_os' => ['type' => PARAM_TEXT],
            'requester_browser' => ['type' => PARAM_TEXT],
        ];
    }
}
