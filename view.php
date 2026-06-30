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

/**
 * auth_qrcode view.php description here.
 *
 * @package    auth_qrcode
 * @copyright  2026  <>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

// Validate the sesskey (CSRF protection).
// require_sesskey(); // Prevents unauthorized requests.

$token = required_param('token', PARAM_ALPHANUM);

try {
    //todo show message if i am sure to log in that session.
    echo('your token is:');
    echo($token);
} catch (dml_missing_record_exception $e) {
    throw new moodle_exception('ivalid', 'error', '', 'Invalid course'); // Throw error, invalid custom token
}
