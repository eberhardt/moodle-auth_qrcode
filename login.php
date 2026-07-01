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
use auth_qrcode\token_creator;
use moodle_url;

require_once(__DIR__ . '/../../config.php');

if (isloggedin() && !isguestuser()) {
    // Already logged in. Let login page handle it.
    redirect(new moodle_url('/login/index.php'));
}

$context = context_system::instance();

$PAGE->set_context($context);

$PAGE->set_url('/auth/qrcode/login.php');
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('pluginname', 'auth_qrcode'));
$PAGE->set_heading(get_string('pluginname', 'auth_qrcode'));

echo $OUTPUT->header();
$logo = $OUTPUT->get_logo_url();
if ($logo) {
    echo $OUTPUT->render_from_template("auth_qrcode/logo", ["logo_url" => $logo, "site_fullname" => $SITE->fullname]);
}

// QR-Code.
$url = new moodle_url('/auth/qrcode/view.php', ["token" => token_creator::create()]);
$template_data = [
    "qrcode_data" => qrcode_generator::generate_qrcode_data($url)
];
echo $OUTPUT->render_from_template("auth_qrcode/login", $template_data);

$PAGE->requires->js_call_amd('auth_qrcode/check', 'init');

echo $OUTPUT->footer();
