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

defined("MOODLE_INTERNAL") || die();

use context_system;

/**
 * Registration base event for module context
 *
 * @package auth_qrcode
 * @copyright 2026
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logged_in extends \core\event\base {

	/**
	 *
	 * @return string
	 */
	public static function get_name() : string {
		return "User authenticated via QR code";
	}

	/**
	 * (non-PHPdoc)
	 * @see \core\event\base::get_description()
	 */
	public function get_description() : string {
		return "The user with id '$this->userid' logged in via QR code with token '{$this->other['token']}'.";
	}

	/**
	 * (non-PHPdoc)
	 * @see \core\event\base::get_url()
	 */
	public function get_url() {
		return NULL;
	}

	/**
     * (non-PHPdoc)
     * @see \core\event\base::init()
	 */
	protected function init() {
		$this->data["crud"] = "r";
		$this->data["edulevel"] = self::LEVEL_OTHER;
		$this->data["objecttable"] = "auth_qrcode";
        $this->context = context_system::instance();
	}
}
