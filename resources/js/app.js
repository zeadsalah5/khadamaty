import './bootstrap';

/* ========== قائمة الموبايل ========== */
function initMobileMenu() {
    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('mobile-menu');
    if (!toggle || !menu) return;

    const setOpen = (open) => {
        menu.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
    };

    toggle.addEventListener('click', () => setOpen(menu.classList.contains('hidden')));
    menu.querySelectorAll('[data-close-menu]').forEach((link) =>
        link.addEventListener('click', () => setOpen(false))
    );
}

/* ========== سلايدر الهيرو (RTL، تشغيل تلقائي، أسهم، نقط، سحب) ========== */
function initSlider(root) {
    const track = root.querySelector('[data-slider-track]');
    const slides = Array.from(root.querySelectorAll('.slider-slide'));
    const dots = Array.from(root.querySelectorAll('[data-slider-dot]'));
    const btnNext = root.querySelector('[data-slider-next]');
    const btnPrev = root.querySelector('[data-slider-prev]');
    const viewport = root.querySelector('.slider-viewport');
    const n = slides.length;
    if (!track || n < 2) return;

    const DELAY = 3000;
    let i = 0;
    let timer = null;

    const go = (idx) => {
        i = (idx + n) % n;
        // RTL: إزاحة موجبة تُظهر الشريحة التالية (اللي على الشمال)
        track.style.transform = `translateX(${i * 100}%)`;
        slides.forEach((s, k) => (k === i ? s.removeAttribute('aria-hidden') : s.setAttribute('aria-hidden', 'true')));
        dots.forEach((d, k) => {
            d.classList.toggle('is-active', k === i);
            d.setAttribute('aria-current', k === i ? 'true' : 'false');
        });
    };
    const next = () => go(i + 1);
    const prev = () => go(i - 1);
    const stop = () => { if (timer) { clearInterval(timer); timer = null; } };
    // لف تلقائي دايماً. تحت "تقليل الحركة" الـ CSS بيشيل حركة الانزلاق فيتبدّل فوراً.
    const start = () => { stop(); timer = setInterval(next, DELAY); };

    btnNext && btnNext.addEventListener('click', () => { next(); start(); });
    btnPrev && btnPrev.addEventListener('click', () => { prev(); start(); });
    dots.forEach((d, k) => d.addEventListener('click', () => { go(k); start(); }));

    // الوقوف عند الـ hover
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);

    // السحب بالإصبع
    let x0 = null;
    viewport.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; stop(); }, { passive: true });
    viewport.addEventListener('touchend', (e) => {
        if (x0 === null) return;
        const dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); } // RTL: سحب لليسار = التالي
        x0 = null;
        start();
    }, { passive: true });

    go(0);
    start();
}

/* ========== زراير "اطلب الخدمة": تختار الخدمة في الفورم تلقائياً ========== */
function initServicePreselect() {
    document.querySelectorAll('[data-service]').forEach((link) => {
        link.addEventListener('click', () => {
            const value = link.getAttribute('data-service');
            const select = document.getElementById('service');
            if (!select) return;
            const match = Array.from(select.options).find((o) => o.value === value);
            if (match) {
                select.value = value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
}

/* ========== معالج تشخيص العطل (العرض ← الاحتمالات ← التوصية) ========== */
function initDiagnose(root) {
    const step1 = root.querySelector('[data-step1]');
    const results = Array.from(root.querySelectorAll('[data-result]'));
    const psteps = Array.from(root.querySelectorAll('[data-progress] [data-pstep]'));
    if (!step1 || results.length === 0) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const scrollTop = () => root.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    const show = (el, on) => { if (el) el.hidden = !on; };
    const setProgress = (n) =>
        psteps.forEach((p) => p.classList.toggle('is-active', Number(p.dataset.pstep) <= n));

    // الحالة الابتدائية: الخطوة ١ بس، والنتايج والتوصيات مخفية.
    const resetRecos = () =>
        root.querySelectorAll('[data-show-reco]').forEach((b) => (b.hidden = false));
    results.forEach((r) => {
        show(r, false);
        show(r.querySelector('[data-sub-reco]'), false);
    });
    setProgress(1);

    root.querySelectorAll('[data-go]').forEach((btn) =>
        btn.addEventListener('click', () => {
            const id = btn.dataset.go;
            show(step1, false);
            resetRecos();
            results.forEach((r) => {
                const match = r.dataset.result === id;
                show(r, match);
                if (match) show(r.querySelector('[data-sub-reco]'), false);
            });
            setProgress(2);
            scrollTop();
        })
    );

    root.querySelectorAll('[data-show-reco]').forEach((btn) =>
        btn.addEventListener('click', () => {
            const result = btn.closest('[data-result]');
            show(result.querySelector('[data-sub-reco]'), true);
            btn.hidden = true;
            setProgress(3);
        })
    );

    root.querySelectorAll('[data-reset]').forEach((btn) =>
        btn.addEventListener('click', () => {
            results.forEach((r) => show(r, false));
            resetRecos();
            show(step1, true);
            setProgress(1);
            scrollTop();
        })
    );
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    document.querySelectorAll('[data-slider]').forEach(initSlider);
    initServicePreselect();
    document.querySelectorAll('[data-diagnose]').forEach(initDiagnose);
});
