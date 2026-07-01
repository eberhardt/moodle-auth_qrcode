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

// Timer ID.
let expirationTimer;

/**
 * Setup the periodic check of the QR code authentication status.
 */
export function init() {
    checkInterval = setInterval(checkNow, 2000);

    // Expire the QR code after 60 seconds.
    expireQRCode(60);
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
        window.location.href = check.wantsurl;
    } else if (check.status === 'not_authorized') {
        displayRejection();
    }
}

/**
 * Remove QRCode and display an expiration message when the QR code has expired.
 *
 * @param {number} delay - Delay in seconds before the QR code expires
 * @returns void
 */
function expireQRCode(delay = 60) {
    expirationTimer = setTimeout(() => {
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
        clearInterval(checkInterval);
    }, delay * 1000);
}

/**
 * Hide QRCode and display a rejection message when the QR code is rejected.
 */
function displayRejection() {

    // Stop the periodic check.
    clearInterval(checkInterval);

    // Clear the expiration timer.
    clearTimeout(expirationTimer);

    // Hide the QR code.
    const qrcode = document.getElementById('qrcode-container');
    if (qrcode) {
        qrcode.remove();
    }
    // Display the rejection message.
    const expiration = document.getElementById('rejected-container');
    if (expiration) {
        expiration.classList.remove('hidden');
    }
}
