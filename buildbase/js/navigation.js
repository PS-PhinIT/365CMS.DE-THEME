/**
 * BuildBase Theme – Navigation & UI
 *
 * Mobil-Menü und Suchpanel als Disclosure (aria-expanded/aria-controls); Escape schließt das jeweils
 * offene Element und gibt den Fokus an seinen Schalter zurück. Nur DOM-APIs, keine HTML-Strings.
 */
(function () {
    'use strict';
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const header  = document.getElementById('masthead');
    const mToggle = document.getElementById('mobileMenuToggle');
    const nav     = document.querySelector('.main-navigation');
    const srchBtn = document.getElementById('searchToggle');
    const srchPnl = document.getElementById('searchPanel');
    const srchCls = document.getElementById('searchClose');

    if (header) {
        const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 20);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    if (mToggle && nav) {
        const isOpen = () => mToggle.getAttribute('aria-expanded') === 'true';
        const setOpen = (open) => {
            mToggle.setAttribute('aria-expanded', String(open));
            // Beschriftungen kommen aus dem Markup (Sprache der Seite), nicht aus dem Script
            mToggle.setAttribute('aria-label', (open ? mToggle.dataset.labelClose : mToggle.dataset.labelOpen) || (open ? 'Menü schließen' : 'Menü öffnen'));
            nav.classList.toggle('open', open);
        };
        mToggle.addEventListener('click', () => {
            setOpen(!isOpen());
            if (isOpen()) {
                nav.querySelector('a')?.focus();
            }
        });
        document.addEventListener('click', (e) => {
            if (isOpen() && !nav.contains(e.target) && !mToggle.contains(e.target)) {
                setOpen(false);
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen()) {
                const focusInside = nav.contains(document.activeElement);
                setOpen(false);
                if (focusInside) {
                    mToggle.focus();
                }
            }
        });
    }

    if (srchBtn && srchPnl) {
        const closeSearch = () => {
            srchPnl.setAttribute('hidden', '');
            srchBtn.setAttribute('aria-expanded', 'false');
            srchBtn.focus();
        };
        srchBtn.addEventListener('click', () => {
            if (!srchPnl.hasAttribute('hidden')) {
                closeSearch();
                return;
            }
            srchPnl.removeAttribute('hidden');
            srchBtn.setAttribute('aria-expanded', 'true');
            srchPnl.querySelector('input')?.focus();
        });
        srchCls?.addEventListener('click', closeSearch);
        srchPnl.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.stopPropagation();
                closeSearch();
            }
        });
    }

    if (!prefersReducedMotion && 'IntersectionObserver' in window) {
        // Einblend-Animation nur mit laufendem Script (ohne JS, z. B. auf der Wartungsseite, bleiben Karten sichtbar).
        document.documentElement.classList.add('bb-js');
        const obs = new IntersectionObserver((entries) => { entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-visible'); obs.unobserve(e.target); } }); }, { threshold: 0.1 });
        document.querySelectorAll('.bb-card').forEach(el => obs.observe(el));
    }
})();
