/**
 * E-Learning · Login animado
 * - Los ojos de Sabio siguen el cursor y "leen" lo que escribes en el correo
 * - Se tapa los ojos con la contraseña y destapa uno si la muestras
 * - Ojos felices al enviar, sacude la cabeza si hay error
 * - Cordón de la lámpara: clic para apagar / encender
 * - Las figuras flotantes se mueven con el cursor (parallax)
 */

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const MESSAGES = {
    typing: 'Te sigo con la mirada.',
    cover: 'No miro, lo prometo.',
    peek: 'Solo echaré un vistazo…',
    happy: '¡Vamos a estudiar!',
    error: 'Revisa tus datos e inténtalo de nuevo.',
    lampOff: 'Se fue la luz. Jala el cordón.',
};

const TEXT_TYPES = ['text', 'email', 'search', 'tel', 'url'];

const initAuthScene = () => {
    const bg = document.querySelector('.auth-bg');
    const scene = document.querySelector('[data-mascot-scene]');

    if (!bg || !scene) {
        return;
    }

    const eyes = [...scene.querySelectorAll('[data-eye]')];
    const floaters = [...bg.querySelectorAll('.auth-float[data-depth]')];
    const messageEl = scene.querySelector('[data-mascot-message] span');
    const defaultMessage = messageEl ? messageEl.textContent.trim() : '';
    const MAX_LOOK = 7;

    let mode = 'idle';
    let lampOff = false;
    let timer = 0;

    /* ---------- Mensaje del búho ---------- */
    const say = (text) => {
        if (!messageEl || messageEl.textContent.trim() === text) {
            return;
        }

        messageEl.textContent = text;
        messageEl.classList.remove('pop');
        void messageEl.offsetWidth; // reinicia la animación
        messageEl.classList.add('pop');
    };

    /* ---------- Mirada ---------- */
    const lookAt = (x, y) => {
        eyes.forEach((eye) => {
            const rect = eye.getBoundingClientRect();
            const dx = x - (rect.left + rect.width / 2);
            const dy = y - (rect.top + rect.height / 2);
            const dist = Math.hypot(dx, dy) || 1;
            const force = Math.min(dist / 120, 1) * MAX_LOOK;
            const mx = (dx / dist) * force;
            const my = (dy / dist) * force;

            eye.querySelectorAll('[data-pupil], [data-pupil-shine]').forEach((el) => {
                el.style.transform = `translate(${mx}px, ${my}px)`;
            });

            eye.querySelectorAll('[data-eye-ball]').forEach((el) => {
                el.style.transform = `translate(${mx * 0.3}px, ${my * 0.3}px)`;
            });
        });
    };

    const lookAtInput = (input) => {
        const rect = input.getBoundingClientRect();
        const progress = Math.min(input.value.length / 30, 1);

        lookAt(rect.left + 24 + progress * Math.max(rect.width - 48, 0), rect.top + rect.height / 2);
    };

    /* ---------- Estados ---------- */
    const STATE_CLASSES = ['is-cover', 'is-peek', 'is-happy', 'is-error'];

    const setMode = (next) => {
        window.clearTimeout(timer);
        mode = next;

        scene.classList.remove(...STATE_CLASSES);

        if (next === 'cover') scene.classList.add('is-cover');
        if (next === 'peek') scene.classList.add('is-cover', 'is-peek');
        if (next === 'happy') scene.classList.add('is-happy');
        if (next === 'error') scene.classList.add('is-error');

        if (next === 'idle') {
            say(lampOff ? MESSAGES.lampOff : defaultMessage);
        } else {
            say(MESSAGES[next] ?? defaultMessage);
        }
    };

    const passwordInputFor = (el) => {
        if (el.matches('[data-password-toggle]')) {
            return document.getElementById(el.dataset.passwordToggle);
        }

        if (el instanceof HTMLInputElement && el.id && document.querySelector(`[data-password-toggle="${CSS.escape(el.id)}"]`)) {
            return el;
        }

        return null;
    };

    const syncFromFocus = () => {
        if (mode === 'happy') {
            return;
        }

        const el = document.activeElement;

        if (!el || !el.closest || !el.closest('.auth-glass')) {
            setMode('idle');
            return;
        }

        const password = passwordInputFor(el);

        if (password) {
            setMode(password.type === 'text' ? 'peek' : 'cover');
            return;
        }

        if (el instanceof HTMLInputElement && TEXT_TYPES.includes(el.type)) {
            setMode('typing');
            lookAtInput(el);
            return;
        }

        setMode('idle');
    };

    document.addEventListener('focusin', syncFromFocus);
    document.addEventListener('focusout', () => window.setTimeout(syncFromFocus, 0));

    document.addEventListener('input', (event) => {
        if (mode === 'typing' && event.target instanceof HTMLInputElement) {
            lookAtInput(event.target);
        }
    });

    // Safari no enfoca los botones al hacer clic, así que lo resolvemos aquí.
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest && event.target.closest('[data-password-toggle]');

        if (!toggle) {
            return;
        }

        const input = document.getElementById(toggle.dataset.passwordToggle);

        if (input) {
            setMode(input.type === 'text' ? 'peek' : 'cover');
        }
    });

    document.addEventListener('submit', (event) => {
        if (event.target.closest && event.target.closest('.auth-glass')) {
            setMode('happy');
        }
    });

    /* ---------- Lámpara ---------- */
    const lampToggle = scene.querySelector('[data-lamp-toggle]');

    if (lampToggle) {
        lampToggle.addEventListener('click', () => {
            lampOff = scene.classList.toggle('lamp-off');
            scene.classList.add('is-pulled');
            window.setTimeout(() => scene.classList.remove('is-pulled'), 180);

            if (mode === 'idle') {
                say(lampOff ? MESSAGES.lampOff : defaultMessage);
            }
        });
    }

    /* ---------- Cursor: mirada + parallax ---------- */
    let frame = 0;
    let pointerX = 0;
    let pointerY = 0;

    const onFrame = () => {
        frame = 0;

        if (!reduceMotion) {
            const nx = pointerX / window.innerWidth - 0.5;
            const ny = pointerY / window.innerHeight - 0.5;

            floaters.forEach((el) => {
                const depth = Number(el.dataset.depth) || 0;
                el.style.transform = `translate3d(${(-nx * depth).toFixed(1)}px, ${(-ny * depth).toFixed(1)}px, 0)`;
            });
        }

        if (mode === 'idle') {
            lookAt(pointerX, pointerY);
        }
    };

    window.addEventListener(
        'pointermove',
        (event) => {
            if (event.pointerType === 'touch') {
                return;
            }

            pointerX = event.clientX;
            pointerY = event.clientY;

            if (!frame) {
                frame = window.requestAnimationFrame(onFrame);
            }
        },
        { passive: true },
    );

    /* ---------- Al cargar ---------- */
    const hasError = bg.querySelector('.auth-glass [role="alert"], .auth-glass .text-red-300');

    window.requestAnimationFrame(() => {
        syncFromFocus();

        if (hasError) {
            window.setTimeout(() => {
                setMode('error');
                timer = window.setTimeout(syncFromFocus, 1800);
            }, 700);
        }
    });
};

document.addEventListener('DOMContentLoaded', initAuthScene);
