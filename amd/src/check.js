/**
 * Periodically checks the status of the QR code authentication.
 *
 * @module     auth_qrcode/check
 * @copyright  2025 Your Name <you@example.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

// Interval ID.
let checkInterval;

/**
 * Setup the periodic check of the QR code authentication status.
 */
export function init() {
    checkInterval = setInterval(checkNow, 2000);
}

/**
 * Ask the server to check the status of the QR code authentication.
 */
async function checkNow() {
    const check = await Ajax.call([{
        methodname: 'auth_qrcode_check_login',
        args: {}
    }])[0];
    if (check.status === 'authorized') {
        clearInterval(checkInterval);
        window.location.href = check.wantsurl;
    } else if (check.status === 'not_authorized') {
        removeQRCode();
        // Display the rejection message.
        const expiration = document.getElementById('rejected-container');
        if (expiration) {
            expiration.classList.remove('hidden');
        }
    } else if (check.status === 'token_not_found') {
        removeQRCode();
        // Display the expiration message.
        const expiration = document.getElementById('expired-container');
        if (expiration) {
            expiration.classList.remove('hidden');
        }
    }
}

/**
 * Remove QRCode and stop the periodic check.
 */
function removeQRCode() {
    // Stop the periodic check.
    clearInterval(checkInterval);

    // Hide the QR code.
    const qrcode = document.getElementById('qrcode-container');
    if (qrcode) {
        qrcode.remove();
    }
}
