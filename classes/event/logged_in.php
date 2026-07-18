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

namespace auth_qrcode\event;

use context_system;
use core\exception\coding_exception;
use dml_exception;

/**
 * Registration base event for module context
 *
 * @package auth_qrcode
 * @copyright 2026
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logged_in extends \core\event\base {
    /**
     * Returns localised general event name.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_name(): string {
        return get_string('userauthenticated', 'auth_qrlocal_mosescode');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '$this->userid' logged in via QR code with token '{$this->other['token']}'.";
    }

    /**
     * Init method.
     *
     * @throws dml_exception
     * @see \core\event\base::init()
     */
    protected function init(): void {
        $this->data["crud"] = "r";
        $this->data["edulevel"] = self::LEVEL_OTHER;
        $this->data["objecttable"] = "auth_qrcode";
        $this->context = context_system::instance();
    }
}
