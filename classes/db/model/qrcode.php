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
use core\user;

/**
 * Persistent model representing a QR code login attempt.
 *
 * This class handles the storage and retrieval of QR code tokens and their
 * associated session and user information.
 *
 * @package    auth_qrcode
 * @copyright  2026 Stefan Dani, Fernfachhochschule Schweiz (FFHS) <stefan.dani@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qrcode extends persistent {

    /**
     * {@inheritDoc}
     */
    const string TABLE = 'auth_qrcode';

    /**
     * Creates a new QR code login record.
     *
     * @param string $token The unique token.
     * @param string $sid The session ID string that requested the QR code.
     * @param string|null $useragent The user agent string to parse for OS and browser. Defaults to current UA.
     * @param int|null $expires Expiry timestamp.
     * @return self|null The created persistent object, or null if token already exists.
     * @throws \coding_exception
     * @throws invalid_persistent_exception
     */
    public static function create_record(
        string $token,
        string $sid,
        ?string $useragent = null,
        ?int $expires = null
    ): self|false {
        global $DB;

        $existing = self::get_record([
            'token' => $token,
        ]);

        if ($existing) {
            mtrace("Existing token found.");
            return false;
        }

        $session = $DB->get_record('sessions', ['sid' => $sid], 'id');
        if (!$session) {
            mtrace("Session not found for sid");
            return false;
        }

        $ua = $useragent ?? \core_useragent::get_user_agent_string() ?: '';
        $env = self::detect_environment($ua);

        $record = new self();
        $record->set('token', $token);
        $record->set('initial_sessionid', $session->id);
        $record->set('requester_os', $env['os']);
        $record->set('requester_browser', $env['browser']);
        $record->set('status', 'created');
        $record->set('timecreated', time());
        $record->set('timeexpires', $expires ?? (time() + 60)); // Default 1 min.
        $record->create();

        mtrace("New QR login record created.");
        return $record;
    }

    /**
     * Sets the user ID for a given token and updates status to authorized.
     *
     * @param int $userid
     * @param string $token
     * @return self|null
     */
    public static function allow(int $userid, string $token): false|self {
        $existing = self::get_record([
            'token' => $token,
            'status' => 'in_use'
        ]);
        if ($existing) {
            if ($existing->get('timeexpires') < time()) {
                mtrace("Token expired.");
                $existing->delete();
                return false;
            }
            $existing->set('userid', $userid);
            $existing->set('status', 'allow_login');
            $existing->set('timeexpires', time() + 60); // Extend timer.
            $existing->update();
            mtrace("User ID added to token.");
            return $existing;
        }
        return false;
    }

    public static function deny(string $token): void {
        $existing = self::get_record([
            'token' => $token,
            'status' => 'in_use'
        ]);
        if ($existing) {
            mtrace("Token denied.");
            $existing->delete();
        }
    }

    public static function get_loginattemp_info(string $token): array|false {
        global $DB;

        $existing = self::get_record([
            'token' => $token,
            'status' => 'created'
        ]);
        if ($existing) {
            if ($existing->get('timeexpires') < time()) {
                mtrace("Token expired.");
                $existing->delete();
                return false;
            }

            $existing->set('status', 'in_use');
            $existing->set('timeexpires', time() + 60); // Extend timer 1min.
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
     * Returns the user associated with this QR login attempt.
     *
     * @return \stdClass|null
     */
    public function get_user_record(): ?\stdClass {
        $userid = $this->get('userid');
        if (!$userid) {
            return null;
        }
        return user::get_user_by_id($userid);
    }

    /**
     * Delete expired records.
     *
     * @return void
     */
    public static function delete_expired(): void {
        global $DB;
        $DB->delete_records_select(self::TABLE, 'timeexpires < ?', [time()]);
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
            'status' => ['type' => PARAM_ALPHA],
            'userid' => ['type' => PARAM_INT, 'null' => NULL_ALLOWED],
            'timecreated' => ['type' => PARAM_INT],
            'timeexpires' => ['type' => PARAM_INT],
            'requester_os' => ['type' => PARAM_TEXT],
            'requester_browser' => ['type' => PARAM_TEXT],
        ];
    }
}
