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
const initializeAdminSidebar = () => {

    const sidebar =
        document.querySelector(
            '[data-admin-sidebar]'
        );

    const overlay =
        document.querySelector(
            '[data-admin-sidebar-overlay]'
        );

    const toggles =
        document.querySelectorAll(
            '[data-admin-sidebar-toggle]'
        );


    if (!sidebar || !overlay) {
        return;
    }


    const openSidebar = () => {

        sidebar.classList.remove(
            '-translate-x-full'
        );

        overlay.classList.remove(
            'hidden'
        );

        document.body.classList.add(
            'overflow-hidden'
        );
    };


    const closeSidebar = () => {

        sidebar.classList.add(
            '-translate-x-full'
        );

        overlay.classList.add(
            'hidden'
        );

        document.body.classList.remove(
            'overflow-hidden'
        );
    };


    toggles.forEach((button) => {

        button.addEventListener(
            'click',
            openSidebar
        );

    });


    overlay.addEventListener(
        'click',
        closeSidebar
    );


    document.addEventListener(
        'keydown',
        (event) => {

            if (event.key === 'Escape') {
                closeSidebar();
            }

        }
    );

};


document.addEventListener(
    'DOMContentLoaded',
    initializeAdminSidebar
);