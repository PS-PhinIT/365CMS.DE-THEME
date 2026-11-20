/**
 * Business Theme – Navigation: Mobile Menu
 *
 * @package IT_Business_Theme
 */

(function () {
    'use strict';
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function init() {

        // ===== Header Scroll =====
        const header = document.getElementById('biz-masthead');
        if (header) {
            window.addEventListener('scroll', function () {
                header.classList.toggle('scrolled', window.scrollY > 60);
            }, { passive: true });
        }

        // ===== Mobile Menu =====
        const toggle  = document.getElementById('bizMobileToggle');
        const drawer  = document.getElementById('bizMobileDrawer');
        const overlay = document.getElementById('bizMobileOverlay');

        function isOpen() {
            return !!(drawer && drawer.classList.contains('is-open'));
        }

        function open() {
            if (!toggle || !drawer || !overlay) return;
            drawer.classList.add('is-open');
            overlay.classList.add('is-open');
            toggle.classList.add('is-active');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', toggle.getAttribute('data-label-close') || 'Menü schließen');
            // Geschlossen ist der Drawer inert (nicht per Tab erreichbar); geöffnet erhält der erste Link den Fokus.
            drawer.inert = false;
            drawer.removeAttribute('inert');
            document.body.classList.add('mobile-menu-open');
            const first = drawer.querySelector('a[href], button:not([disabled])');
            if (first) first.focus();
        }

        function close(restoreFocus) {
            if (!toggle || !drawer || !overlay || !isOpen()) return;
            drawer.classList.remove('is-open');
            overlay.classList.remove('is-open');
            toggle.classList.remove('is-active');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', toggle.getAttribute('data-label-open') || 'Menü öffnen');
            drawer.inert = true;
            drawer.setAttribute('inert', '');
            document.body.classList.remove('mobile-menu-open');
            if (restoreFocus !== false) toggle.focus();
        }

        function findAnchorTarget(hash) {
            if (!hash || hash.length <= 1 || !/^#[A-Za-z][A-Za-z0-9_-]*$/.test(hash)) {
                return null;
            }

            return document.getElementById(hash.slice(1));
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                isOpen() ? close() : open();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () { close(); });
        }

        // Escape schließt nur ein offenes Menü und gibt den Fokus an den Menüschalter zurück.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen()) close();
        });

        // ===== Smooth Scroll for Anchor Links =====
        document.querySelectorAll('a[href^="#"], a[href*="/#"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                const href  = link.getAttribute('href');
                const hash  = href.includes('#') ? '#' + href.split('#')[1] : href;
                const target = findAnchorTarget(hash);
                if (target) {
                    e.preventDefault();
                    const delay = isOpen() ? 300 : 0;
                    close(false);
                    setTimeout(function () {
                        target.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
                        // Fokus an das Ziel übergeben, damit Tastatur und Screenreader dort weiterlesen.
                        if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
                        target.focus({ preventScroll: true });
                    }, delay);
                }
            });
        });

    }

    // Sicherer Einstiegspunkt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
