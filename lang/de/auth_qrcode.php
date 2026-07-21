<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     auth_qrcode
 * @category    string
 * @copyright   2026 MoodleMootDACH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['browser'] = 'Browser';
$string['cannot_use_as_login_method'] = 'Die Anmeldemethode \'{$a->auth}\' für den Benutzer {$a->name} wurde wiederhergestellt, da die QR-Code-Authentifizierung nicht für normales Anmelden verwendet werden kann.';
$string['confirmation'] = 'Versuchen Sie gerade, sich per QR-Code bei Moodle anzumelden?';
$string['expirationtime'] = 'Ablaufzeit des QR-Codes';
$string['expirationtime_desc'] = 'Die Dauer in Sekunden, für die ein generierter Anmelde-QR-Code gültig ist. Der Ablauf-Timer wird einmalig auf diesen Wert zurückgesetzt, wenn der/die Nutzer/in die Bestätigungsseite auf dem Mobilgerät zum ersten Mal aufruft. Niedrigere Werte lassen möglicherweise nicht genügend Zeit, um den QR-Code zu scannen, höhere Werte sind möglicherweise ein Sicherheitsrisiko.';
$string['expired_or_rejected'] = 'Ihre Anmelde-Anfrage ist entweder abgelaufen oder abgelehnt worden. Bitte scannen Sie einen neuen QR-Code.';
$string['get_new_qrcode'] = 'Neuen QR-Code anfordern';
$string['instruction_1'] = 'Öffnen Sie auf Ihrem Smartphone eine Kamera-App, die QR-Codes scannen kann.';
$string['instruction_2'] = 'Scannen Sie den Code.';
$string['instruction_3'] = 'Folgen Sie den Anweisungen auf Ihrem Smartphone und bestätigen Sie die Anmeldung.';
$string['invalid_token'] = 'Ungültiges Token';
$string['ip'] = 'IP-Adresse';
$string['login_cancelled'] = 'Anmeldung abgelehnt.';
$string['login_confirmed'] = 'Anmeldung bestätigt. Bitte wechseln Sie zum anderen Gerät.';
$string['login_denied'] = 'Anmeldung abgelehnt.';
$string['login_rejected'] = 'Ihre Anmeldung wurde abgelehnt.';
$string['login_using_smartphone'] = 'Anmeldung per Smartphone';
$string['login_via_qrcode'] = 'Login mit QR-Code';
$string['os'] = 'Betriebssystem';
$string['pluginisdisabled'] = 'Das Plugin auth_qrcode ist deaktiviert.';
$string['pluginname'] = 'QR-Code';
$string['privacy:metadata'] = 'Das Authentifizierungs-Plugin QR-Code speichert keine personenbezogenen Daten.';
$string['qrcode_expired'] = 'Der QR-Code ist abgelaufen.';
$string['qrcode_for_login'] = 'QR-Code für die Anmeldung.';
$string['qrcode_instructions'] = 'Scannen Sie den QR-Code mit Ihrem mobilen Gerät.';
$string['return_to_login'] = 'Zurück zur Anmeldung';
$string['task:cleanup_expired_qrcode'] = 'Abgelaufene QR-Login-Einträge bereinigen';
$string['userauthenticated'] = 'Nutzer/in per QR-Code authentifiziert';
$string['userauthorizedlogin'] = 'Nutzer/in hat die Anmeldung per QR-Code autorisiert';
