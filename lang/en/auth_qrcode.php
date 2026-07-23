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
 * Plugin strings are defined here.
 *
 * @package     auth_qrcode
 * @category    string
 * @copyright   2026 MoodleMootDACH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['browser'] = 'Browser';
$string['cancel_login'] = 'Cancel login';
$string['cannot_use_as_login_method'] = 'The \'{$a->auth}\' login method for user {$a->name} has been restored because the QR code authentication method cannot be used for standard logins. ';
$string['confirmation'] = 'Do you try to log in to {$a} via QR-Code?';
$string['confirmationcode'] = 'Confirmation code';
$string['confirmationcodeexplanation'] = 'Please enter the following confirmation code on the other device:';
$string['enterconfirmationcode'] = 'Enter confirmation code';
$string['enterconfirmationcodeexplanation'] = 'Please enter the confirmation code from your smartphone.';
$string['expirationtime'] = 'QR-Code expiration time';
$string['expirationtime_desc'] = 'The duration for which a generated login QR-Code is valid, in seconds. The expiration timer is reset to this value once when the user accesses the confirmation page on their mobile device for the first time. Lower values may not leave enough time for the user to scan the QR-Code, higher values may impact security.';
$string['expired_or_rejected'] = 'Your login request either expired or was rejected, please scan a new QR code';
$string['get_new_qrcode'] = 'Get a new QR-Code';
$string['instruction_1'] = 'Open a camera app on your smartphone, which is able to scan QR codes.';
$string['instruction_2'] = 'Scan the code.';
$string['instruction_3'] = 'Follow the instructions on your smartphone and confirm the login.';
$string['invalid_token'] = 'Invalid token';
$string['invalidconfirmationcode'] = 'The confirmation code is invalid. Please try again. ({$a} attempts remaining)';
$string['ip'] = 'IP address';
$string['login_cancelled'] = 'Login cancelled.';
$string['login_confirmed'] = 'Login confirmed. Please switch to the other device.';
$string['login_denied'] = 'Login denied.';
$string['login_rejected'] = 'Your login was rejected';
$string['login_using_smartphone'] = 'Log in using smartphone';
$string['login_via_qrcode'] = 'Log in via QR-Code';
$string['os'] = 'Operating System';
$string['pluginisdisabled'] = 'The auth_qrcode plugin is disabled.';
$string['pluginname'] = 'QR-Code';
$string['privacy:metadata'] = 'The QR-Code authentication plugin does not store any personal data.';
$string['qrcode_expired'] = 'The QR-Code has expired.';
$string['qrcode_for_login'] = 'QR-Code for login.';
$string['qrcode_instructions'] = 'Scan the QR-Code with your mobile device.';
$string['return_to_login'] = 'Return to login';
$string['task:cleanup_expired_qrcode'] = 'Cleanup expired QR login records';
$string['useconfirmationcode'] = 'Request confirmation code';
$string['useconfirmationcode_desc'] = 'Whether a confirmation code has to be entered on the device trying to log in after the login has been confirmed on the mobile device. If this is turned off, the device is logged in immediately after confirming.';
$string['userauthenticated'] = 'User authenticated via QR code';
$string['userauthorizedlogin'] = 'User authorized the login via QR code';
