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

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/auth/qrcode/view.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('login_via_qrcode', 'auth_qrcode'));
$PAGE->set_heading(get_string('login_via_qrcode', 'auth_qrcode'));

echo $OUTPUT->header();

$token = required_param('token', PARAM_ALPHANUMEXT);

// Check if the token should be denied.
if (optional_param('deny', false, PARAM_BOOL)) {
    \auth_qrcode\db\model\qrcode::deny($token);
    echo $OUTPUT->notification(get_string('token_denied', 'auth_qrcode'), 'info', false);
    echo $OUTPUT->footer();
    exit;
}

// Check if the token should be allowed.
if (optional_param('allow', false, PARAM_BOOL)) {
    \auth_qrcode\db\model\qrcode::allow($USER->id, $token);
    echo $OUTPUT->notification(get_string('token_allowed', 'auth_qrcode'), 'success', false);
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

// Confirmation message.
echo html_writer::tag('div', get_string('confirmation', 'auth_qrcode'), ['class' => 'confirmation-message mt-3 mb-3']);
echo html_writer::start_tag('ul', ['class' => 'token-info']);
echo html_writer::tag('li', get_string('ip', 'auth_qrcode') . ':' . $tokeninfo['ip']);
echo html_writer::tag('li', get_string('os', 'auth_qrcode') . ':' . $tokeninfo['os']);
echo html_writer::tag('li', get_string('browser', 'auth_qrcode') . ':' . $tokeninfo['browser']);
echo html_writer::end_tag('ul');

// Confirmation buttons.
echo html_writer::start_tag('div', ['class' => 'confirmation-buttons']);
echo html_writer::tag('a', get_string('yes', 'core'), [
    'href' => new moodle_url('/auth/qrcode/view.php', ['token' => $token, 'allow' => 1]),
    'class' => 'btn btn-primary w-50 mb-3 me-3',
]);
echo html_writer::tag('a', get_string('no', 'core'), [
    'href' => new moodle_url('/auth/qrcode/view.php', ['token' => $token, 'deny' => 1]),
    'class' => 'btn btn-secondary w-50 mb-3 me-3',
]);
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
