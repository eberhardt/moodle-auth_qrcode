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
 * auth_qrcode admin settings.
 *
 * @package    auth_qrcode
 * @copyright  2026 Lars Bonczek (@innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpUnhandledExceptionInspection}
 */

defined('MOODLE_INTERNAL') || die();

global $ADMIN; // Do NOT declare $settings here as it's not a global variable.
isset($settings) || die('$settings variable not set');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'auth_qrcode/expirationtime',
        new lang_string('expirationtime', 'auth_qrcode'),
        new lang_string('expirationtime_desc', 'auth_qrcode'),
        60,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'auth_qrcode/useconfirmationcode',
        new lang_string('useconfirmationcode', 'auth_qrcode'),
        new lang_string('useconfirmationcode_desc', 'auth_qrcode'),
        '1'
    ));
}
