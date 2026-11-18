/**
 * CMS Newspaper Theme – Navigation & UI
 *
 * - Sticky-Header scroll state class
 * - Mobile drawer toggle (aria-expanded, hidden, Fokus auf ersten Link / zurück zum Schalter)
 * - Outside-click + Escape close
 * - Optional search panel toggle with focus restore
 * - Optional IntersectionObserver-based reveal (motion-allowed only)
 *
 * Vanilla ES2017+, IIFE, no jQuery, no inline handlers.
 */
(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const header     = document.getElementById('news-masthead');
    const mToggle    = document.getElementById('newsMobileToggle');
    const drawer     = document.getElementById('newsMobileDrawer');
    const overlay    = document.getElementById('newsMobileOverlay');
    const searchBtn  = document.getElementById('newsSearchToggle');
    const searchPanel = document.getElementById('newsSearchPanel');
    const searchClose = document.getElementById('newsSearchClose');

    // ── Sticky header scroll state ──────────────────────────────────────
    if (header) {
        const onScroll = function () {
            header.classList.toggle('scrolled', window.scrollY > 24);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // ── Mobile drawer ───────────────────────────────────────────────────
    // Geschlossen trägt der Drawer `hidden` (nicht per Tab erreichbar); beim Öffnen erhält der erste Link
    // den Fokus, beim Schließen (Escape, Overlay, Toggle) kehrt er zum Menü-Schalter zurück.
    const isEnglish = (document.documentElement.getAttribute('lang') || '').toLowerCase().indexOf('en') === 0;
    const labelOpen  = isEnglish ? 'Open menu' : 'Menü öffnen';
    const labelClose = isEnglish ? 'Close menu' : 'Menü schließen';
    let drawerTimer = null;

    function openDrawer() {
        if (!drawer || !mToggle) return;
        if (drawerTimer) { window.clearTimeout(drawerTimer); drawerTimer = null; }
        drawer.hidden = false;
        void drawer.offsetWidth; // Reflow, damit die Einblend-Transition greift
        drawer.classList.add('is-open');
        if (overlay) { overlay.classList.add('is-open'); }
        mToggle.classList.add('is-active');
        mToggle.setAttribute('aria-expanded', 'true');
        mToggle.setAttribute('aria-label', labelClose);
        document.body.style.overflow = 'hidden';
        const firstLink = drawer.querySelector('a[href]');
        if (firstLink) { firstLink.focus(); }
    }

    function closeDrawer(restoreFocus) {
        if (!drawer || !mToggle || !drawer.classList.contains('is-open')) return;
        drawer.classList.remove('is-open');
        if (overlay) { overlay.classList.remove('is-open'); }
        mToggle.classList.remove('is-active');
        mToggle.setAttribute('aria-expanded', 'false');
        mToggle.setAttribute('aria-label', labelOpen);
        document.body.style.overflow = '';
        drawerTimer = window.setTimeout(function () {
            drawer.hidden = true;
            drawerTimer = null;
        }, prefersReducedMotion ? 0 : 320);
        if (restoreFocus) { mToggle.focus(); }
    }

    if (mToggle && drawer) {
        mToggle.addEventListener('click', function () {
            if (drawer.classList.contains('is-open')) {
                closeDrawer(true);
            } else {
                openDrawer();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () { closeDrawer(true); });
    }

    // Outside-click safety (in case overlay isn't rendered)
    document.addEventListener('click', function (e) {
        if (!drawer || !drawer.classList.contains('is-open')) return;
        if (drawer.contains(e.target) || (mToggle && mToggle.contains(e.target))) return;
        closeDrawer(false);
    });

    // ── Search panel toggle ─────────────────────────────────────────────
    let searchOpenerEl = null;

    function openSearch() {
        if (!searchPanel || !searchBtn) return;
        searchOpenerEl = document.activeElement;
        searchPanel.removeAttribute('hidden');
        searchBtn.setAttribute('aria-expanded', 'true');
        const input = searchPanel.querySelector('input[type="search"]');
        if (input) { input.focus(); }
    }

    function closeSearch() {
        if (!searchPanel || !searchBtn) return;
        searchPanel.setAttribute('hidden', '');
        searchBtn.setAttribute('aria-expanded', 'false');
        if (searchOpenerEl && typeof searchOpenerEl.focus === 'function') {
            searchOpenerEl.focus();
        } else {
            searchBtn.focus();
        }
    }

    if (searchBtn && searchPanel) {
        searchBtn.addEventListener('click', function () {
            if (searchPanel.hasAttribute('hidden')) {
                openSearch();
            } else {
                closeSearch();
            }
        });
    }
    if (searchClose) {
        searchClose.addEventListener('click', closeSearch);
    }

    // ── Global Escape ───────────────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (drawer && drawer.classList.contains('is-open')) { closeDrawer(true); }
        if (searchPanel && !searchPanel.hasAttribute('hidden')) { closeSearch(); }
    });

    // ── Reveal-on-scroll (motion-allowed only) ──────────────────────────
    if (!prefersReducedMotion && 'IntersectionObserver' in window) {
        // Ausblenden erst mit laufendem Script (style.css: html.news-js .news-reveal); ohne JS bleibt alles sichtbar.
        document.documentElement.classList.add('news-js');
        const obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.news-reveal').forEach(function (el) {
            obs.observe(el);
        });
    } else {
        document.querySelectorAll('.news-reveal').forEach(function (el) {
            el.classList.add('is-visible');
        });
    }

}());
