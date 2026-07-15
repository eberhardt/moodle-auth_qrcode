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
 * Display the confirmation page after a user has scanned a QR-Code.
 *
 * @package    auth_qrcode
 * @copyright  2026 MoodleMootDACH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login(autologinguest: false);

if (!is_enabled_auth('qrcode')) {
    throw new moodle_exception(get_string('pluginisdisabled', 'auth_qrcode'));
}

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/auth/qrcode/confirm.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('login_via_qrcode', 'auth_qrcode'));
$PAGE->set_heading(get_string('login_via_qrcode', 'auth_qrcode'));

echo $OUTPUT->header();

$token = required_param('token', PARAM_ALPHANUM);

// Check if user is allowed to log in with their primary method.
if ($USER->auth == 'nologin' || !is_enabled_auth($USER->auth)) {
    // Suspended accounts will lose their session, no need to check for this explicitly.
    echo $OUTPUT->notification(get_string('login_rejected', 'auth_qrcode'), 'error', false);
    echo $OUTPUT->footer();
    exit;
}

// Check if the token should be denied.
if (optional_param('deny', false, PARAM_BOOL)) {
    require_sesskey();
    \auth_qrcode\db\model\qrcode::deny($token);
    echo $OUTPUT->notification(get_string('login_cancelled', 'auth_qrcode'), 'info', false);
    echo $OUTPUT->footer();
    exit;
}

// Check if the token should be allowed.
if (optional_param('allow', false, PARAM_BOOL)) {
    require_sesskey();
    $isallowed = \auth_qrcode\db\model\qrcode::allow($USER->id, $token);
    if (is_object($isallowed)) {
        echo $OUTPUT->notification(get_string('login_confirmed', 'auth_qrcode'), 'success', false);
        $event = \auth_qrcode\event\login_authorized::create([
            "userid" => $USER->id,
            "objectid" => $USER->id,
            "other" => ['token' => $token]
        ]);
        $event->trigger();
    } else {
        echo $OUTPUT->notification(get_string('expired_or_rejected', 'auth_qrcode'), 'error', false);
    }
    echo $OUTPUT->footer();
    exit;
}

// Check if the token is valid.
$tokeninfo = \auth_qrcode\db\model\qrcode::get_loginattemp_info($token);
if (!$tokeninfo) {
    echo $OUTPUT->notification(get_string('invalid_token', 'auth_qrcode'), 'danger', false);
    echo $OUTPUT->footer();
    exit;
}

$tokeninfo['sesskey'] = sesskey();

echo $OUTPUT->render_from_template('auth_qrcode/confirmation', $tokeninfo);
echo $OUTPUT->footer();
