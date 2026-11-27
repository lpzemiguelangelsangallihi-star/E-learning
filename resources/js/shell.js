import '../css/shell.css';

/**
 * Shell del dashboard: sidebar contraíble, drawer móvil, búsqueda rápida y
 * luz que sigue al cursor en las cards.
 */

const html = document.documentElement;
const LS_KEY = 'el.sidebar';

const store = {
    get: () => { try { return localStorage.getItem(LS_KEY); } catch (e) { return null; } },
    set: (v) => { try { localStorage.setItem(LS_KEY, v); } catch (e) { /* sin storage */ } },
};

/* ---------- Modo del sidebar según el ancho ---------- */
const syncMode = () => {
    const w = window.innerWidth;
    const collapsed = store.get() === 'collapsed';

    html.classList.toggle('sb-drawer', w < 768);
    html.classList.toggle('sb-tablet', w >= 768 && w < 1024);
    html.classList.toggle('sb-rail', w >= 768 && (w < 1024 || collapsed));

    if (w >= 768) {
        closeDrawer();
    }
};

/* ---------- Drawer móvil ---------- */
const sidebar = () => document.querySelector('[data-shell-sidebar]');

function openDrawer() {
    sidebar()?.classList.add('is-open');
    document.body.classList.add('shell-locked');
}

function closeDrawer() {
    sidebar()?.classList.remove('is-open');
    document.body.classList.remove('shell-locked');
}

const toggleCollapse = () => {
    if (html.classList.contains('sb-drawer') || html.classList.contains('sb-tablet')) {
        return;
    }

    const next = html.classList.contains('sb-rail') ? 'open' : 'collapsed';
    store.set(next);
    html.classList.toggle('sb-rail', next === 'collapsed');
};

/* ---------- Búsqueda rápida ---------- */
const initJump = () => {
    const links = [...document.querySelectorAll('[data-shell-link]')].map((a) => ({
        label: a.dataset.label,
        href: a.getAttribute('href'),
        icon: a.querySelector('i')?.className ?? 'fa-solid fa-circle',
    }));

    if (!links.length) {
        return;
    }

    const root = document.createElement('div');
    root.className = 'shell-jump';
    root.setAttribute('role', 'dialog');
    root.setAttribute('aria-modal', 'true');
    root.setAttribute('aria-label', 'Ir a una sección');
    root.innerHTML = `
        <div class="shell-jump-box">
            <div class="shell-jump-input">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="text" placeholder="¿A dónde quieres ir?" autocomplete="off" aria-label="Buscar sección">
            </div>
            <div class="shell-jump-list" role="listbox"></div>
        </div>`;
    document.body.appendChild(root);

    const input = root.querySelector('input');
    const list = root.querySelector('.shell-jump-list');
    let shown = [];
    let sel = 0;

    const render = () => {
        const q = input.value.trim().toLowerCase();
        shown = links.filter((l) => l.label.toLowerCase().includes(q));
        sel = Math.min(sel, Math.max(shown.length - 1, 0));

        list.innerHTML = shown.length
            ? shown.map((l, i) => `
                <a href="${l.href}" role="option" class="shell-jump-item${i === sel ? ' is-sel' : ''}">
                    <i class="${l.icon}" aria-hidden="true"></i>${l.label}
                </a>`).join('')
            : '<p class="shell-jump-empty">No hay secciones con ese nombre.</p>';
    };

    const open = () => {
        input.value = '';
        sel = 0;
        render();
        root.classList.add('is-open');
        input.focus();
    };
    const close = () => root.classList.remove('is-open');

    input.addEventListener('input', () => { sel = 0; render(); });
    root.addEventListener('mousedown', (e) => { if (e.target === root) close(); });

    root.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { close(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); sel = (sel + 1) % Math.max(shown.length, 1); render(); }
        if (e.key === 'ArrowUp') { e.preventDefault(); sel = (sel - 1 + shown.length) % Math.max(shown.length, 1); render(); }
        if (e.key === 'Enter' && shown[sel]) { window.location.href = shown[sel].href; }
    });

    document.querySelectorAll('[data-shell-jump]').forEach((b) => b.addEventListener('click', open));

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            root.classList.contains('is-open') ? close() : open();
        }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            toggleCollapse();
        }
    });
};

/* ---------- Luz que sigue al cursor en las cards ---------- */
const initCardGlow = () => {
    document.addEventListener('pointermove', (e) => {
        const card = e.target.closest?.('.el-card');
        if (!card) { return; }

        const r = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${e.clientX - r.left}px`);
        card.style.setProperty('--my', `${e.clientY - r.top}px`);
    }, { passive: true });
};

document.addEventListener('DOMContentLoaded', () => {
    syncMode();
    window.addEventListener('resize', syncMode);

    document.querySelectorAll('[data-shell-open]').forEach((b) => b.addEventListener('click', openDrawer));
    document.querySelectorAll('[data-shell-collapse]').forEach((b) => b.addEventListener('click', toggleCollapse));
    document.querySelector('[data-shell-overlay]')?.addEventListener('click', closeDrawer);
    document.querySelectorAll('[data-shell-link]').forEach((a) => a.addEventListener('click', closeDrawer));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeDrawer(); } });

    initJump();
    initCardGlow();
});
