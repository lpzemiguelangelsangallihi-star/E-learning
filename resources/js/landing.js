/** Página de inicio: apertura, parallax, texto que se escribe, figuritas, Sabio y secciones animadas */
document.addEventListener('DOMContentLoaded', () => {
    const hero = document.getElementById('inicio');
    if (!hero) return;
    const root = document.documentElement;
    const still = matchMedia('(prefers-reduced-motion: reduce)').matches;
    root.classList.add('lp-js');

    /* 1) Apertura: dos puertas se abren y revelan el inicio */
    if (!still) {
        root.classList.add('lp-intro-on');
        const intro = document.createElement('div');
        intro.className = 'lp-intro';
        intro.innerHTML = '<div class="lp-door t"></div><div class="lp-door b"></div><div class="lp-intro-logo"><i class="fa-solid fa-graduation-cap"></i>E-Learning</div>';
        document.body.append(intro);
        setTimeout(() => intro.classList.add('open'), 700);
        setTimeout(() => intro.remove(), 2400);
    }

    /* 2) Título: palabras que suben y frase que se escribe */
    const h1 = hero.querySelector('h1');
    const typed = h1.querySelector('span');
    typed.classList.add('lp-typed');
    [...h1.childNodes].filter((n) => n.nodeType === 3).forEach((n) => {
        const f = document.createDocumentFragment();
        n.textContent.trim().split(/\s+/).forEach((w, i) => {
            const s = Object.assign(document.createElement('span'), { className: 'lp-w', textContent: w });
            s.style.setProperty('--w', i);
            f.append(s);
        });
        n.replaceWith(f);
    });
    if (!still) {
        const phrases = ['en un solo lugar', 'a tu propio ritmo', 'con la ayuda de Sabio'];
        const caret = Object.assign(document.createElement('i'), { className: 'lp-caret' });
        typed.textContent = '';
        typed.append(caret);
        const text = document.createTextNode('');
        typed.prepend(text);
        let p = 0, n = 0, erasing = false;
        const tick = () => {
            const t = phrases[p];
            n += erasing ? -1 : 1;
            text.nodeValue = t.slice(0, n);
            let wait = erasing ? 28 : 60;
            if (!erasing && n === t.length) { erasing = true; wait = 1900; }
            else if (erasing && n === 0) { erasing = false; p = (p + 1) % phrases.length; wait = 350; }
            setTimeout(tick, wait);
        };
        setTimeout(tick, 2300);
    }

    /* 3) Scroll: progreso del hero (parallax), barra y encabezado */
    const bar = Object.assign(document.createElement('div'), { className: 'lp-progress' });
    document.body.prepend(bar);
    const header = document.querySelector('header.fixed');
    const onScroll = () => {
        const max = root.scrollHeight - innerHeight;
        bar.style.transform = `scaleX(${max > 0 ? scrollY / max : 0})`;
        hero.style.setProperty('--p', Math.min(scrollY / hero.offsetHeight, 1).toFixed(3));
        header?.classList.toggle('is-scrolled', scrollY > 10);
    };
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* 4) Secciones: revelado, foco de luz que sigue al cursor y menú activo */
    document.querySelectorAll('#caracteristicas .mx-auto > div:first-child, #materias .mx-auto > div:first-child, #roles .mx-auto > div:first-child, #caracteristicas .grid > *, #materias .grid > *')
        .forEach((el, i) => { el.setAttribute('data-reveal', ''); el.style.setProperty('--i', i % 6); });
    document.querySelectorAll('#roles .grid > *').forEach((el, i) => el.setAttribute('data-reveal', i % 2 ? 'right' : 'left'));
    const io = new IntersectionObserver((es) => es.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .15, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('[data-reveal]').forEach((el) => io.observe(el));
    document.querySelectorAll(':is(#caracteristicas, #materias, #roles) .grid > *').forEach((card) => card.addEventListener('pointermove', (e) => {
        const r = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${e.clientX - r.left}px`);
        card.style.setProperty('--my', `${e.clientY - r.top}px`);
    }));
    const links = [...document.querySelectorAll('header nav a[href*="#"]')];
    const spy = new IntersectionObserver((es) => es.forEach((e) => {
        if (e.isIntersecting) links.forEach((a) => a.classList.toggle('is-active', a.getAttribute('href').endsWith('#' + e.target.id)));
    }), { rootMargin: '-45% 0px -50% 0px' });
    document.querySelectorAll('section[id]').forEach((s) => spy.observe(s));

    /* 5) Figuritas flotantes */
    const figs = [['fa-atom', 6, 26, 46, 24], ['fa-calculator', 84, 24, 50, 18], ['π', 14, 62, 52, 30], ['fa-flask', 78, 58, 52, 20, 1], ['fa-lightbulb', 92, 40, 46, 26, 1], ['E=mc²', 3, 44, 72, 18]];
    const els = figs.map(([c, l, t, s, d, gold], i) => {
        const el = document.createElement('div');
        el.className = 'lp-fig' + (gold ? ' is-gold' : '');
        el.dataset.depth = d;
        el.style.cssText = `left:${l}%;top:${t}%`;
        const inner = document.createElement('div');
        inner.style.cssText = `width:${s}px;height:${s}px;font-size:${Math.round(s * .38)}px;--d:${i * .5}s;--dur:${6 + (i % 4)}s;--r0:${i % 2 ? 6 : -8}deg;--r1:${i % 2 ? -6 : 8}deg`;
        inner.innerHTML = c.startsWith('fa-') ? `<i class="fa-solid ${c}"></i>` : `<span>${c}</span>`;
        el.append(inner);
        return el;
    });
    hero.prepend(...els);
    if (!still) hero.addEventListener('pointermove', (e) => {
        const r = hero.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        els.forEach((el) => { const d = +el.dataset.depth; el.style.transform = `translate(${-x * d}px, ${-y * d}px)`; });
    });

    /* 6) Sabio: ojos que siguen el cursor y habla al hacer clic */
    const sabio = document.querySelector('[data-sabio]');
    if (!sabio) return;
    sabio.hidden = false;
    const eyes = [...sabio.querySelectorAll('[data-eye]')];
    addEventListener('pointermove', (e) => eyes.forEach((eye) => {
        const r = eye.getBoundingClientRect(), dx = e.clientX - (r.left + r.width / 2), dy = e.clientY - (r.top + r.height / 2);
        const d = Math.hypot(dx, dy) || 1, f = Math.min(d / 120, 1) * 7;
        eye.querySelectorAll('[data-pupil],[data-pupil-shine]').forEach((el) => { el.style.transform = `translate(${dx / d * f}px, ${dy / d * f}px)`; });
    }), { passive: true });
    const out = sabio.querySelector('[data-sabio-text]');
    let token = 0;
    const say = (msg) => {
        const id = ++token;
        if (still) { out.textContent = msg; return; }
        out.textContent = '';
        let i = 0;
        (function next() { if (id !== token) return; out.textContent = msg.slice(0, ++i); if (i < msg.length) setTimeout(next, 28); })();
    };
    const lines = ['¿Listo para aprender algo nuevo?', 'Matemáticas, física, química… ¡tú eliges!', 'Tu progreso se guarda siempre.', '¡Cada día cuenta! Sigue tu racha.'];
    let k = 0;
    sabio.querySelector('button').addEventListener('click', () => {
        sabio.classList.remove('is-hop'); void sabio.offsetWidth; sabio.classList.add('is-hop');
        say(lines[k++ % lines.length]);
    });
    setTimeout(() => say('¡Hola! Soy Sabio, tu guía de estudio.'), 3200);
});
