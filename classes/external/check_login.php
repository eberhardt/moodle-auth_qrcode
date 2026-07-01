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

namespace auth_qrcode\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/login/lib.php');

use auth_qrcode\db\model\qrcode;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Web Service to check if this session has been authorized from another device and log in the user.
 *
 * @package     auth_qrcode
 * @copyright   2025 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_login extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Check if this session has been authorized from another device and log in the user.
     *
     * @return array{status: 'waiting_auth'|'authorized'|'not_authorized'|'token_not_found'}
     */
    public static function execute(): array {
        global $SESSION;

        if (isset($SESSION->auth_qrcode_token)) {
            $canlogin = qrcode::can_user_login($SESSION->auth_qrcode_token, session_id());

            if (is_object($canlogin)) {
                // The other session authorized this token to login as the user that was returned.
                complete_user_login($canlogin, ['auth_qrcode_login' => true]);
                return [
                    'status' => 'authorized',
                    'wantsurl' => \core_login_get_return_url(),
                ];
            }
            if ($canlogin === 'waiting') {
                return [
                    'status' => 'waiting_auth',
                ];
            }
            if ($canlogin === 'denied') {
                return [
                    'status' => 'not_authorized',
                ];
            }
        }

        return [
            'status' => 'token_not_found',
        ];
    }

    /**
     * Describe the return structure of the external service.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the login check.'),
            'wantsurl' => new external_value(PARAM_LOCALURL, 'URL to redirect to', VALUE_OPTIONAL, null, NULL_ALLOWED),
        ]);
    }
}
