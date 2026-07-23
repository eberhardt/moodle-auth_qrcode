/**
 * Modal to enter the confirmation code.
 *
 * @module     auth_qrcode/confirmationcode_input_modal
 * @copyright  2026 Lars Bonczek (@innoCampus, TU Berlin)
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Modal from 'core/modal';
import * as ModalRegistry from 'core/modal_registry';
import * as ModalEvents from 'core/modal_events';
import * as KeyCodes from 'core/key_codes';
import Notification from 'core/notification';

export default class ConfirmationCodeInputModal extends Modal {
    static TYPE = 'auth_qrcode/confirmationcode_input_modal';
    static TEMPLATE = 'auth_qrcode/confirmationcode_input_modal';

    /**
     * Set the required length of the confirmation code.
     *
     * @param {number} length
     */
    setLength(length) {
        this.length = length;
        this.input.attr('maxlength', length);
        // Make space for `length` characters (with .5ch letter spacing) and the input field's padding (1ch on either side).
        this.input.css('max-width', `calc(${length * 1.5 + 1.5}ch + 2 * var(--bs-border-width))`);
        this.input.attr('placeholder', '_'.repeat(length));
    }

    /**
     * Set the callback to be called when the code is entered.
     *
     * @param {Function<Promise>} callback
     */
    setCallback(callback) {
        this.callback = callback;
    }

    /**
     * Register all event listeners.
     */
    registerEventListeners() {
        this.input = this.getBody().find('#confirmationcode-input');

        // Prevent Escape key closing the modal.
        // This has to be done BEFORE the super.registerEventListeners() call.
        this.getRoot().on('keydown', e => {
            if (e.keyCode === KeyCodes.escape) {
                e.stopImmediatePropagation();
            }
        });

        super.registerEventListeners();
        this.registerCloseOnCancel();

        // Prevent clicking outside the modal from closing it.
        this.getRoot().on(ModalEvents.outsideClick, e => {
            e.preventDefault();
        });

        // Hide the close button.
        this.getModal().find('[data-action="hide"]').addClass('d-none');

        // Focus the input when the modal is shown.
        this.getRoot().on(ModalEvents.shown, () => {
            this.input.focus();
        });

        // Handle input events.
        this.input.on('input', e => {
            // Remove non-digit characters from code.
            const code = e.target.value.replace(/\D/g, '');
            e.target.value = code;

            this.clearError();
            if (code.length === this.length && this.callback) {
                this.disableInput();
                this.callback(code).then(errorMessage => {
                    if (errorMessage) {
                        this.displayError(errorMessage);
                        this.resetInput();
                    }
                    return true;
                }).catch(async e => {
                    await Notification.exception(e);
                    this.resetInput();
                });
            }
        });
    }

    /**
     * Display an error message in the modal.
     *
     * @param {string} message
     */
    displayError(message) {
        const errorContainer = this.getBody().find('#confirmationcode-error');
        errorContainer.removeClass('hidden').text(message);
    }

    /**
     * Reset the input field and enable it.
     */
    resetInput() {
        this.input.prop('disabled', false).val('').focus();
    }

    /**
     * Disable the input field.
     */
    disableInput() {
        this.input.prop('disabled', true);
    }

    /**
     * Clear the error message.
     */
    clearError() {
        const errorContainer = this.getBody().find('#confirmationcode-error');
        errorContainer.addClass('hidden').text('');
    }
}

ModalRegistry.register(ConfirmationCodeInputModal.TYPE, ConfirmationCodeInputModal, ConfirmationCodeInputModal.TEMPLATE);
