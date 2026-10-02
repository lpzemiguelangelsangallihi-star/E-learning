import './bootstrap';

import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import '@fortawesome/fontawesome-free/css/all.min.css';

window.Swal = Swal;


/**
 * Mostrar / ocultar contraseña
 */
const initializePasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {

        button.addEventListener('click', () => {

            const input = document.getElementById(
                button.dataset.passwordToggle
            );

            const icon = button.querySelector(
                '[data-password-icon]'
            );

            if (!input || !icon) {
                return;
            }

            const isPassword = input.type === 'password';

            input.type = isPassword
                ? 'text'
                : 'password';


            if (isPassword) {

                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');

                button.setAttribute(
                    'aria-label',
                    'Ocultar contraseña'
                );

                button.setAttribute(
                    'aria-pressed',
                    'true'
                );

            } else {

                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');

                button.setAttribute(
                    'aria-label',
                    'Mostrar contraseña'
                );

                button.setAttribute(
                    'aria-pressed',
                    'false'
                );
            }

        });

    });
};

const initializeLoadingForms = () => {

    document
        .querySelectorAll('[data-loading-form]')
        .forEach((form) => {

            form.addEventListener('submit', () => {

                const button = form.querySelector(
                    '[data-submit-button]'
                );

                const icon = form.querySelector(
                    '[data-submit-icon]'
                );

                const text = form.querySelector(
                    '[data-submit-text]'
                );

                if (!button) {
                    return;
                }


                button.disabled = true;


                if (icon) {
                    icon.className =
                        'fa-solid fa-spinner fa-spin';
                }


                if (text) {

                    text.textContent =
                        form.dataset.loadingText
                        ?? 'Procesando...';

                }

            });

        });

};


document.addEventListener('DOMContentLoaded', () => {

    initializePasswordToggles();

    initializeLoadingForms();

});