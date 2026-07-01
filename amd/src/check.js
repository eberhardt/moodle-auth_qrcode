/**
 * Periodically checks the status of the QR code authentication.
 *
 * @module     auth_qrcode/check
 * @copyright  2025 Your Name <you@example.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

/**
 * Setup the periodic check of the QR code authentication status.
 */
export function init() {
    setInterval(checkNow, 2000);
}

/**
 * Ask the server to check the status of the QR code authentication.
 */
async function checkNow() {
    const status = await Ajax.call([{
        methodname: 'auth_qrcode_check_login',
        args: {}
    }])[0];
    window.console.log("QR login status: ", status);
}
