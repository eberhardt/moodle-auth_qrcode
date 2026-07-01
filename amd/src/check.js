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
    const checkInterval = setInterval(checkNow, 2000);

    // Expire the QR code after 60 seconds.
    expireQRCode(checkInterval, 60);
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

/**
 * Remove QRCode and display an expiration message when the QR code has expired.
 *
 * @param {number} interval - ID des setInterval-Timers
 * @param {number} delay - Delay in seconds before the QR code expires
 * @returns void
 */
function expireQRCode(interval, delay = 60) {
    setTimeout(() => {
        // Hide the QR code.
        const qrcode = document.getElementById('qrcode-container');
        if (qrcode) {
            qrcode.remove();
        }
        // Display the expiration message.
        const expiration = document.getElementById('expired-container');
        if (expiration) {
            expiration.classList.remove('hidden');
        }

        // Stop the periodic check.
        window.console.log("QR code expired, stopping periodic check.");
        clearInterval(interval);
    }, delay * 1000);
}
