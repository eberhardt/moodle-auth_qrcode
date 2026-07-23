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
 * Upgrade code for auth_qrcode.
 *
 * @package    auth_qrcode
 * @copyright  2026 Lars Bonczek (@innoCampus, TU Berlin)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for auth_qrcode.
 *
 * @param int $oldversion
 * @return bool
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_auth_qrcode_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026072102) {
        // Alter table auth_qrcode.
        $table = new xmldb_table('auth_qrcode');

        $confirmationcode = new xmldb_field('confirmationcode', XMLDB_TYPE_CHAR, precision: 10, notnull: null, default: null);
        if (!$dbman->field_exists($table, $confirmationcode)) {
            $dbman->add_field($table, $confirmationcode);
        }

        $failedattempts = new xmldb_field('failedattempts', XMLDB_TYPE_INTEGER, precision: 10, notnull: true, default: 0);
        if (!$dbman->field_exists($table, $failedattempts)) {
            $dbman->add_field($table, $failedattempts);
        }

        upgrade_plugin_savepoint(true, 2026072102, 'auth', 'qrcode');
    }

    return true;
}
