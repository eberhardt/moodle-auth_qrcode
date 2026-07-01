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
     * @param int $initialsessionid The ID of the session that requested the QR code.
     * @param string|null $useragent The user agent string to parse for OS and browser. Defaults to current UA.
     * @param int|null $expires Expiry timestamp.
     * @return self|null The created persistent object, or null if token already exists.
     * @throws \coding_exception
     * @throws invalid_persistent_exception
     */
    public static function create_record(
        string $token,
        int $initialsessionid,
        ?string $useragent,
        ?int $expires = null
    ): ?self {
        $existing = self::get_record([
            'token' => $token,
        ]);

        if ($existing) {
            mtrace("Existing token found.");
            return null;
        }

        $env = self::detect_environment($useragent);

        $record = new self();
        $record->set('token', $token);
        $record->set('initial_sessionid', $initialsessionid);
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
    public static function set_userid(int $userid, string $token): ?self {
        $existing = self::get_record([
            'token' => $token,
            'status' => 'in_use'
        ]);
        if ($existing) {
            $existing->set('userid', $userid);
            $existing->set('status', 'allow_login');
            $existing->update();
            mtrace("User ID added to token.");
            return $existing;
        }
        return null;
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
