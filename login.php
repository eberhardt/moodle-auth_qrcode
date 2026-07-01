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
 * auth_qrcode login.php description here.
 *
 * @package    auth_qrcode
 * @copyright  2026 <>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_qrcode\qrcode_generator;

require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();

$PAGE->set_context($context);

$PAGE->set_url('/auth/qrcode/login.php');
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('pluginname', 'auth_qrcode'));
$PAGE->set_heading(get_string('pluginname', 'auth_qrcode'));

echo $OUTPUT->header();

echo html_writer::start_tag('div', ['class' => 'text-center']);

$logo = $OUTPUT->get_logo_url();
if ($logo) {
    echo html_writer::start_tag('div', ['id' => 'loginlogo', 'class' => 'd-flex justify-content-center mb-4']);

    echo html_writer::empty_tag('img', [
        'id' => 'logoimage',
        'src' => $logo,
        'alt' => $SITE->fullname,
        'class' => 'img-fluid',
    ]);

    echo html_writer::end_tag('div');
}

echo $OUTPUT->heading(get_string('loginto', 'core', $SITE->fullname));

// QR-Code.
echo html_writer::start_tag('div', ['class' => 'qrcode-container text-center mt-5 mb-3']);

$url = 'todo';
echo(qrcode_generator::generate_qrcode($url));

echo html_writer::end_tag('div');

// Instructions.
echo html_writer::tag('div', get_string('qrcode_instructions', 'auth_qrcode'), ['class' => 'text-center mb-3']);

// Back to login.
echo html_writer::start_tag('div', ['class' => 'text-center']);
echo html_writer::tag('a', 'Return to Login', [
    'href' => (new moodle_url('/login/index.php'))->out(),
    'class' => 'btn btn-secondary w-75',
]);
echo html_writer::end_tag('div');

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
