import '../css/animations.css';

// Todas las animaciones del login y de los dashboards viven en este archivo.

/**
 * Contadores que suben hasta su valor: <span data-count-up="42" data-decimals="1">
 */
const initializeCountUp = () => {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ease = (t) => 1 - Math.pow(1 - t, 4);

    document.querySelectorAll('[data-count-up]').forEach((el) => {
        const target = parseFloat(el.dataset.countUp);
        const decimals = parseInt(el.dataset.decimals ?? '0', 10);

        if (Number.isNaN(target) || reduce) {
            return;
        }

        const duration = 1200;
        const start = performance.now() + 250;

        el.textContent = (0).toFixed(decimals);

        const tick = (now) => {
            const t = Math.min(Math.max((now - start) / duration, 0), 1);
            el.textContent = (target * ease(t)).toFixed(decimals);

            if (t < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
    });
};

/**
 * Luz que sigue al cursor en la tarjeta de vidrio del login.
 */
const initializeGlassGlow = () => {
    document.querySelectorAll('.auth-glass').forEach((card) => {
        card.addEventListener('pointermove', (event) => {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            card.style.setProperty('--my', `${event.clientY - rect.top}px`);
        });
    });
};

/**
 * Las figuras del login siguen suavemente al cursor (parallax).
 */
const initializeFloaters = () => {
    const items = document.querySelectorAll('.auth-float[data-depth]');

    if (!items.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    let frame = null;

    window.addEventListener('pointermove', (event) => {
        if (frame) {
            return;
        }

        frame = requestAnimationFrame(() => {
            const x = event.clientX / window.innerWidth - 0.5;
            const y = event.clientY / window.innerHeight - 0.5;

            items.forEach((item) => {
                const depth = parseFloat(item.dataset.depth);
                item.style.setProperty('--px', `${(-x * depth * 2).toFixed(1)}px`);
                item.style.setProperty('--py', `${(-y * depth * 2).toFixed(1)}px`);
            });

            frame = null;
        });
    });
};

/**
 * Mascota Sabio (búho): los ojos persiguen el cursor, se tapa los ojos con la
 * contraseña, mira el campo que escribes y la lámpara se apaga al hacer clic.
 */
const initializeMascot = () => {
    const scene = document.querySelector('[data-mascot-scene]');

    if (!scene) {
        return;
    }

    const eyes = [...scene.querySelectorAll('[data-eye]')].map((eye) => ({
        ball: eye.querySelector('[data-eye-ball]'),
        pupils: eye.querySelectorAll('[data-pupil], [data-pupil-shine]'),
    }));

    const MAX = 5.5;

    const lookAt = (x, y) => {
        eyes.forEach(({ ball, pupils }) => {
            const rect = ball.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const dx = x - cx;
            const dy = y - cy;
            const dist = Math.hypot(dx, dy) || 1;
            const factor = Math.min(dist / 160, 1) * MAX;

            pupils.forEach((pupil) => {
                pupil.style.transform = `translate(${((dx / dist) * factor).toFixed(2)}px, ${((dy / dist) * factor).toFixed(2)}px)`;
            });
        });
    };

    window.addEventListener('pointermove', (event) => lookAt(event.clientX, event.clientY));

    // Al escribir, mira hacia el campo activo
    document.addEventListener('focusin', (event) => {
        const field = event.target;

        if (!(field instanceof HTMLInputElement)) {
            return;
        }

        scene.classList.toggle('is-shy', field.type === 'password');

        if (field.type !== 'password') {
            const rect = field.getBoundingClientRect();
            lookAt(rect.left + 40, rect.top + rect.height / 2);
        }
    });

    // Si muestras la contraseña, ya no se tapa los ojos
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-password-toggle]');

        if (toggle) {
            requestAnimationFrame(() => {
                const input = document.getElementById(toggle.dataset.passwordToggle);
                scene.classList.toggle('is-shy', input?.type === 'password');
            });
        }
    });

    document.addEventListener('focusout', (event) => {
        if (event.target instanceof HTMLInputElement && event.target.type === 'password') {
            scene.classList.remove('is-shy');
        }
    });

    scene.querySelector('[data-lamp-toggle]')?.addEventListener('click', () => {
        scene.classList.toggle('lamp-off');
    });

    // Mirada inicial hacia el formulario
    lookAt(window.innerWidth * 0.7, window.innerHeight * 0.5);
};

/**
 * Las cards de los dashboards se iluminan donde está el cursor.
 */
const initializeSpotlight = () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    let frame = null;

    document.addEventListener('pointermove', (event) => {
        const card = event.target instanceof Element ? event.target.closest('.el-card') : null;

        if (!card || frame) {
            return;
        }

        frame = requestAnimationFrame(() => {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            card.style.setProperty('--my', `${event.clientY - rect.top}px`);
            frame = null;
        });
    });
};

/**
 * Anillos de progreso: <circle class="el-ring" data-ring="75" r="..."> 
 */
const initializeRings = () => {
    document.querySelectorAll('[data-ring]').forEach((circle) => {
        const r = parseFloat(circle.getAttribute('r'));
        const c = 2 * Math.PI * r;
        const value = Math.max(0, Math.min(100, parseFloat(circle.dataset.ring)));

        circle.style.strokeDasharray = c;
        circle.style.strokeDashoffset = c;

        requestAnimationFrame(() => {
            setTimeout(() => {
                circle.style.strokeDashoffset = c * (1 - value / 100);
            }, 350);
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initializeCountUp();
    initializeGlassGlow();
    initializeFloaters();
    initializeMascot();
    initializeSpotlight();
    initializeRings();
});
