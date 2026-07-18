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
 * Token validator.
 *
 * @package   auth_qrcode
 * @author    Sascha Vogel (sascha.vogel@ffhs.ch)
 * @copyright 2026 MoodleMootDACH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_qrcode;

use coding_exception;
use core\invalid_persistent_exception;
use dml_exception;

/**
 * Token validator class.
 */
class token_creator {
    /**
     * Creates a new token.
     *
     * This always creates a new token even if the user already has one.
     *
     * @return string new token
     * @throws invalid_persistent_exception
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function create(): string {
        global $SESSION;

        // Each random byte will be mapped into a number between 0 and 61, that means an entropy of 62.
        // Therefore, a length of 34 will provide a security of log(62, 2) * 34 ~ 202 bits.
        $token = random_string(34);
        if (db\model\qrcode::create_record($token, session_id())) {
            $SESSION->auth_qrcode_token = $token;
            return $token;
        }
        throw new coding_exception('Could not create token');
    }
}
