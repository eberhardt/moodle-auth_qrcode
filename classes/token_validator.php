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

/**
 * Token validator class.
 */
class token_validator {
    /**
     * Validates the token.
     *
     * @param string|null $token
     * @return bool
     */
    public static function validate(?string $token): bool {
        if (empty($token)) {
            return false;
        }

        // Todo: Check if token exists.

        return true;
    }

    /**
     * Confirms the token.
     *
     * @param string $token
     * @return void
     */
    public static function confirm(string $token) {
        // ToDo: Confirm token by setting the userid.
    }

    /**
     * Removes/cancels the token.
     *
     * @param string $token
     * @return void
     */
    public static function cancel(string $token) {
        // ToDo: Remove token.
    }
}
