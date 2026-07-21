/**
 * Periodically checks the status of the QR code authentication.
 *
 * @module     auth_qrcode/check
 * @copyright  2025 Your Name <you@example.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import ConfirmationCodeInputModal from 'auth_qrcode/confirmationcode_input_modal';
import * as ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';

/**
 * Interval ID.
 * @type {number}
 */
let checkInterval;

/**
 * Length of the confirmation code.
 * @type {number}
 */
let confirmationCodeLength;

/**
 * Number of remaining attempts.
 * @type {number}
 */
let remainingAttempts;

/**
 * Modal to enter the confirmation code.
 * @type {ConfirmationCodeInputModal}
 */
let confirmationCodeModal;

/**
 * Setup the periodic check of the QR code authentication status.
 */
export function init() {
    checkInterval = setInterval(checkNow, 2000);
}

/**
 * Ask the server to check the status of the QR code authentication.
 * @param {String|null} confirmationcode
 */
async function checkNow(confirmationcode = null) {
    const check = await Ajax.call([{
        methodname: 'auth_qrcode_check_login',
        args: {
            confirmationcode: confirmationcode
        }
    }])[0];
    if (check.status === 'authorized') {
        // Login has been completed, redirect the user.
        clearInterval(checkInterval);
        window.location.href = check.wantsurl;
        return true;
    }
    if (check.status === 'confirmationcode_required') {
        // Wrong confirmation code (or none entered). Prompt the user.
        confirmationCodeLength = check.confirmationcode_length;
        remainingAttempts = check.remaining_attempts;
        if (remainingAttempts > 0) {
            await showConfirmationCodeModal();
        } else {
            // No more attempts left.
            showRejected();
        }
    } else if (check.status === 'not_authorized') {
        // Login attempt rejected on smartphone.
        showRejected();
    } else if (check.status === 'token_not_found') {
        // Token expired.
        showExpired();
    }
    return false;
}

/**
 * Initialize and show the modal to enter the confirmation code.
 */
async function showConfirmationCodeModal() {
    if (confirmationCodeModal) {
        // This modal cannot be reopened after being hidden.
        return;
    }

    // Create the modal.
    confirmationCodeModal = await ConfirmationCodeInputModal.create({
        title: await getString('enterconfirmationcode', 'auth_qrcode'),
        show: true,
        isVerticallyCentered: true,
    });
    confirmationCodeModal.setLength(confirmationCodeLength);
    confirmationCodeModal.setCallback(async(code) => {
        // Code has been entered, check it.
        // If it's correct, the checkNow function will redirect the user automatically.
        const authorized = await checkNow(code);
        if (!authorized) {
            return getString('invalidconfirmationcode', 'auth_qrcode', remainingAttempts);
        }
        return null;
    });

    // Show rejection message if modal is closed (the login is not actually rejected, but without the modal it cannot be completed).
    confirmationCodeModal.getRoot().on(ModalEvents.cancel, () => {
        showRejected();
    });
}

/**
 * Display the rejection message.
 */
function showRejected() {
    clearQRCode();
    const rejection = document.getElementById('rejected-container');
    if (rejection) {
        rejection.classList.remove('hidden');
    }
}

/**
 * Display the expiration message.
 */
function showExpired() {
    clearQRCode();
    const expiration = document.getElementById('expired-container');
    if (expiration) {
        expiration.classList.remove('hidden');
    }
}

/**
 * Remove QRCode and stop the periodic check.
 */
function clearQRCode() {
    // Stop the periodic check.
    clearInterval(checkInterval);

    // Hide confirmation modal if open.
    if (confirmationCodeModal) {
        confirmationCodeModal.hide();
    }

    // Hide the QR code.
    const qrcode = document.getElementById('qrcode-container');
    if (qrcode) {
        qrcode.remove();
    }
}
