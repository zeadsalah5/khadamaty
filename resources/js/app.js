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

/* ========== الهيدر الشفّاف فوق الهيرو (الرئيسية): يصير أبيض عند التمرير ========== */
function initTransparentHeader() {
    const header = document.querySelector('[data-transparent-header]');
    if (!header) return;

    const THRESHOLD = 40;
    const sync = () => header.classList.toggle('is-solid', window.scrollY > THRESHOLD);
    sync(); // الحالة الصح لو الصفحة محمّلة وهي متمرّرة
    window.addEventListener('scroll', sync, { passive: true });
}

/* ========== سلايدر الهيرو (crossfade، RTL، تشغيل/إيقاف، أسهم، نقط، سحب، a11y) ========== */
function initHeroSlider(root) {
    const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));
    const btnNext = root.querySelector('[data-hero-next]');
    const btnPrev = root.querySelector('[data-hero-prev]');
    const btnPlay = root.querySelector('[data-hero-playpause]');
    const area = root.querySelector('[data-hero-slides]');
    const n = slides.length;
    if (n < 2) return; // شريحة واحدة: تفضل ظاهرة من غير تحكّم

    const DELAY = 5000;
    let i = 0;
    let timer = null;
    let paused = false; // تشغيل تلقائي دايماً (مع زر إيقاف يدوي) — طلب صاحب الموقع

    const render = () => {
        slides.forEach((s, k) => {
            const active = k === i;
            s.classList.toggle('is-active', active);
            if (active) {
                s.removeAttribute('aria-hidden');
                s.removeAttribute('inert');
            } else {
                s.setAttribute('aria-hidden', 'true');
                s.setAttribute('inert', '');
            }
        });
        dots.forEach((d, k) => {
            d.classList.toggle('is-active', k === i);
            k === i ? d.setAttribute('aria-current', 'true') : d.removeAttribute('aria-current');
        });
    };

    const go = (idx) => { i = (idx + n) % n; render(); };
    const next = () => go(i + 1);
    const prev = () => go(i - 1);

    const stop = () => { if (timer) { clearInterval(timer); timer = null; } };
    const run = () => { if (paused) return; stop(); timer = setInterval(next, DELAY); };

    const setPaused = (p) => {
        paused = p;
        if (btnPlay) {
            btnPlay.classList.toggle('is-paused', p);
            btnPlay.setAttribute('aria-label', p ? 'تشغيل التبديل التلقائي' : 'إيقاف التبديل التلقائي');
        }
        p ? stop() : run();
    };

    btnNext && btnNext.addEventListener('click', () => { next(); run(); });
    btnPrev && btnPrev.addEventListener('click', () => { prev(); run(); });
    dots.forEach((d, k) => d.addEventListener('click', () => { go(k); run(); }));
    btnPlay && btnPlay.addEventListener('click', () => setPaused(!paused));

    // ملاحظة: مفيش وقوف عند مرور الماوس — الهيرو بيملا الشاشة فالوقوف كان بيمنع
    // التشغيل التلقائي على الكمبيوتر. الإيقاف متاح يدوياً من زر التشغيل/الإيقاف.

    // السحب بالإصبع (RTL: سحب لليسار = التالي)
    let x0 = null;
    area && area.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
    area && area.addEventListener('touchend', (e) => {
        if (x0 === null) return;
        const dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); run(); }
        x0 = null;
    }, { passive: true });

    render();
    setPaused(false); // يبدأ التشغيل التلقائي ويضبط الزر
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

/* ========== حاسبة قدرة التكييف المناسب للغرفة (تقديرية) ========== */
function initAcCalculator() {
    const form = document.querySelector('[data-ac-calc]');
    if (!form) return;

    const byId = (id) => document.getElementById(id);
    const result = document.querySelector('[data-ac-result]');
    const errEl = form.querySelector('[data-ac-error]');
    const hpEl = result.querySelector('[data-ac-hp]');
    const btuEl = result.querySelector('[data-ac-btu]');
    const areaEl = result.querySelector('[data-ac-area]');
    const noteEl = result.querySelector('[data-ac-note]');

    // مقاسات السوق المصري (BTU ← الحصان)، تصاعدي.
    const sizes = [
        { btu: 9000, hp: '١ حصان' },
        { btu: 12000, hp: '١.٥ حصان' },
        { btu: 18000, hp: '٢.٢٥ حصان' },
        { btu: 24000, hp: '٣ حصان' },
        { btu: 30000, hp: '٤ حصان' },
        { btu: 36000, hp: '٥ حصان' },
    ];
    const sunFactor = { low: 0.9, medium: 1.0, high: 1.15 };
    const fmt = (n) => n.toLocaleString('ar-EG');

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const L = parseFloat(byId('acLength').value);
        const W = parseFloat(byId('acWidth').value);
        let H = parseFloat(byId('acHeight').value);
        const sun = byId('acSun').value;
        const people = parseInt(byId('acPeople').value, 10);

        if (!(L > 0) || !(W > 0)) {
            errEl.textContent = 'اكتب طول وعرض الغرفة بالمتر الأول.';
            errEl.classList.remove('hidden');
            result.classList.add('hidden');
            (!(L > 0) ? byId('acLength') : byId('acWidth')).focus();
            return;
        }
        errEl.classList.add('hidden');
        if (!(H > 0)) { H = 2.8; }

        const area = L * W;
        const heightFactor = Math.max(1, H / 2.8);
        const sf = sunFactor[sun] || 1;
        let btu = area * 600 * heightFactor * sf;
        if (people > 2) { btu += (people - 2) * 600; }

        const btuRounded = Math.round(btu / 500) * 500;
        const overMax = btu > 36000;
        const pick = sizes.find((s) => btu <= s.btu) || sizes[sizes.length - 1];

        hpEl.textContent = overMax ? 'أكتر من ٥ حصان' : pick.hp;
        btuEl.textContent = '≈ ' + fmt(btuRounded) + ' BTU';
        areaEl.textContent = fmt(Math.round(area * 10) / 10) + ' م²';
        noteEl.textContent = overMax
            ? 'المساحة كبيرة — غالباً محتاج أكتر من وحدة أو قدرة عالية مع توزيع صح. ننصح بمعاينة فني لتحديد أفضل حل.'
            : 'ده تقدير للقدرة الأنسب. لو غرفتك آخر دور أو واجهتها زجاج كبير، فكّر في القدرة الأعلى اللي فوقها مباشرة. المعاينة بتأكّد الاختيار.';

        result.classList.remove('hidden');
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        result.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' });
    });

    form.addEventListener('reset', () => {
        errEl.classList.add('hidden');
        result.classList.add('hidden');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initTransparentHeader();
    initAcCalculator();
    document.querySelectorAll('[data-hero-slider]').forEach(initHeroSlider);
    initServicePreselect();
    document.querySelectorAll('[data-diagnose]').forEach(initDiagnose);
});
