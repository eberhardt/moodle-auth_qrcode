<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Authentication class for qr_login is defined here.
 *
 * @package     auth_qrcode
 * @copyright   2026 MoodleMootDACH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/authlib.php');

use core\notification;

// For further information about authentication plugins please read
// https://docs.moodle.org/dev/Authentication_plugins.
//
// The base class auth_plugin_base is located at /lib/authlib.php.
// Override functions as needed.

/**
 * Authentication class for qr_login.
 */
class auth_plugin_qrcode extends auth_plugin_base {
    /**
     * Set the properties of the instance.
     */
    public function __construct() {
        $this->authtype = 'qrcode';
        $this->userfields = [];
    }

    /**
     * {inheritDoc}
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password): bool {
        // Always return false since we do not want to use this as an authentication method.
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's password.
     *
     * @return bool
     */
    public function can_change_password(): bool {
        return false;
    }

    /**
     * Returns true if this authentication plugin can edit the users' profile.
     *
     * @return bool
     */
    public function can_edit_profile(): bool {
        return false;
    }

    /**
     * Returns true if this authentication plugin is "internal".
     *
     * Internal plugins use password hashes from Moodle user table for authentication.
     *
     * @return bool
     */
    public function is_internal(): bool {
        return true;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool.
     */
    public function can_reset_password(): bool {
        return false;
    }

    /**
     * Returns true if plugin allows signup and user creation.
     *
     * @return bool
     */
    public function can_signup(): bool {
        return false;
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm(): bool {
        return false;
    }

    /**
     * Returns whether or not this authentication plugin can be manually set
     * for users, for example, when bulk uploading users.
     *
     * This should be overriden by authentication plugins where setting the
     * authentication method manually is allowed.
     *
     * @return bool
     */
    public function can_be_manually_set(): bool {
        return false;
    }

    /**
     * Return a list of identity providers to display on the login page.
     *
     * @param string|moodle_url $wantsurl The requested URL.
     * @return array List of arrays with keys url, iconurl and name.
     * @throws coding_exception
     */
    public function loginpage_idp_list($wantsurl): array {
        // This is the URL the user will be sent to when clicking the button.
        $url = new moodle_url('/auth/qrcode/login.php');

        // Specify an icon to be displayed on the button.
        $iconurl = new moodle_url('/auth/qrcode/pix/qr.png');

        return [
            [
                'url' => $url,
                'iconurl' => $iconurl,
                'name' => get_string('login_using_smartphone', 'auth_qrcode'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $olduser Userobject before modifications    (without system magic quotes)
     * @param mixed $newuser Userobject new modified userobject (without system magic quotes)
     * @return boolean true if updated or update ignored; false if error
     * @throws moodle_exception If an error occurs during the update process.
     */
    public function user_update($olduser, $newuser): bool {
        if ($newuser->auth == $this->authtype) {
            $authinst = get_auth_plugin($olduser->auth);
            $newuser->auth = $authinst->authtype;
            $a = [
                "auth" => $authinst->get_title(),
                "name" => fullname($newuser),
            ];
            $message = get_string('cannot_use_as_login_method', 'auth_qrcode', $a);
            notification::add($message, notification::ERROR);
        }
        return parent::user_update($olduser, $newuser);
    }
}
