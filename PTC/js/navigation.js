/**
 * PTC Theme – Navigation & UI Interactions
 *
 * Modules:
 *   - Sticky Header (.scrolled)
 *   - Burger Menu (Mobile Drawer)
 *   - Smooth Scroll for Anchor Links
 *   - Submenus per Tastatur (Escape schließt)
 *   - Profil-Dropdown (Disclosure)
 *
 * Aktive Menüeinträge markiert der Server (class="active" + aria-current="page").
 *
 * @package PTC_Theme
 */

(function () {
    'use strict';

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function init() {

        // ===== Sticky Header =====
        var header = document.getElementById('ptc-masthead');
        if (header) {
            window.addEventListener('scroll', function () {
                if (window.scrollY > 60) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }, { passive: true });
        }

        // ===== Mobile Menu =====
        var toggle  = document.getElementById('ptcMobileToggle');
        var drawer  = document.getElementById('ptcMobileDrawer');
        var overlay = document.getElementById('ptcMobileOverlay');

        var lastFocusBeforeMenu = null;

        function isMenuOpen() {
            return !!(drawer && drawer.classList.contains('is-open'));
        }

        function openMenu() {
            if (!toggle || !drawer || !overlay) return;
            lastFocusBeforeMenu = document.activeElement;
            drawer.classList.add('is-open');
            overlay.classList.add('is-open');
            toggle.classList.add('is-active');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Menü schließen');
            // Geschlossen ist der Drawer inert (nicht per Tab erreichbar); geöffnet erster Link fokussieren.
            drawer.inert = false;
            drawer.removeAttribute('inert');
            document.body.style.overflow = 'hidden';
            var first = drawer.querySelector('a[href], button:not([disabled])');
            if (first) {
                first.focus();
            }
        }

        function closeMenu(restoreFocus) {
            if (!toggle || !drawer || !overlay || !isMenuOpen()) return;
            drawer.classList.remove('is-open');
            overlay.classList.remove('is-open');
            toggle.classList.remove('is-active');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Menü öffnen');
            drawer.inert = true;
            drawer.setAttribute('inert', '');
            document.body.style.overflow = '';
            if (restoreFocus !== false) {
                var restore = lastFocusBeforeMenu || toggle;
                if (restore && typeof restore.focus === 'function') {
                    restore.focus();
                }
            }
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (isMenuOpen()) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () { closeMenu(); });
        }

        // Escape schließt nur ein offenes Menü (bisher sprang der Fokus bei jedem Escape zum Menüschalter).
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isMenuOpen()) {
                closeMenu();
            }
        });

        // ===== Desktop-Submenüs: öffnen per Hover und Tastaturfokus (:focus-within), Escape schließt =====
        document.querySelectorAll('.ptc-nav li.has-children').forEach(function (item) {
            var parentLink = item.querySelector(':scope > a');
            item.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && item.matches(':focus-within')) {
                    item.classList.add('is-collapsed');
                    if (parentLink) {
                        parentLink.focus();
                    }
                    e.stopPropagation();
                }
            });
            item.addEventListener('focusout', function (e) {
                if (!item.contains(e.relatedTarget)) {
                    item.classList.remove('is-collapsed');
                }
            });
            item.addEventListener('mouseleave', function () {
                item.classList.remove('is-collapsed');
            });
            if (parentLink) {
                parentLink.addEventListener('keydown', function (e) {
                    if (e.key === 'ArrowDown') {
                        item.classList.remove('is-collapsed');
                        var firstChild = item.querySelector('.ptc-submenu a');
                        if (firstChild) {
                            e.preventDefault();
                            firstChild.focus();
                        }
                    }
                });
            }
        });

        // ===== Smooth Scroll for Anchor Links =====
        document.querySelectorAll('a[href^="#"], a[href*="/#"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var href  = link.getAttribute('href');
                var hash  = href.indexOf('#') !== -1 ? '#' + href.split('#')[1] : href;
                var target = hash.length > 1 ? document.querySelector(hash) : null;
                if (target) {
                    e.preventDefault();
                    var delay = isMenuOpen() ? 350 : 0;
                    closeMenu(false);
                    setTimeout(function () {
                        var headerH = header ? header.offsetHeight : 72;
                        var top = target.getBoundingClientRect().top + window.pageYOffset - headerH;
                        if (prefersReducedMotion) {
                            window.scrollTo(0, top);
                        } else {
                            window.scrollTo({ top: top, behavior: 'smooth' });
                        }
                        // Fokus an das Ziel übergeben, damit Tastatur und Screenreader dort weiterlesen.
                        if (!target.hasAttribute('tabindex')) {
                            target.setAttribute('tabindex', '-1');
                        }
                        target.focus({ preventScroll: true });
                    }, prefersReducedMotion ? 0 : delay);
                }
            });
        });


        // ===== Flash Messages Auto-Dismiss =====
        document.querySelectorAll('[data-auto-dismiss]').forEach(function (el) {
            var delay = parseInt(el.getAttribute('data-auto-dismiss'), 10) || 5000;
            setTimeout(function () {
                el.style.transition = 'opacity 0.4s ease';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 400);
            }, delay);
        });

        // ===== Profile Dropdown =====
        var profileDropdown = document.getElementById('ptcProfileDropdown');
        if (profileDropdown) {
            var profileToggle = profileDropdown.querySelector('.ptc-profile-toggle');
            var closeProfile = function () {
                profileDropdown.classList.remove('is-open');
                if (profileToggle) {
                    profileToggle.setAttribute('aria-expanded', 'false');
                }
            };
            if (profileToggle) {
                profileToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var isOpen = profileDropdown.classList.toggle('is-open');
                    profileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    if (isOpen) {
                        var firstItem = profileDropdown.querySelector('.ptc-profile-menu a, .ptc-profile-menu button');
                        if (firstItem) {
                            firstItem.focus();
                        }
                    }
                });
            }
            // Schließen, wenn der Fokus das Dropdown verlässt (Tab)
            profileDropdown.addEventListener('focusout', function (e) {
                if (!profileDropdown.contains(e.relatedTarget)) {
                    closeProfile();
                }
            });
            // Schließen per Klick außerhalb
            document.addEventListener('click', function (e) {
                if (!profileDropdown.contains(e.target)) {
                    closeProfile();
                }
            });
            // Schließen per Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && profileDropdown.classList.contains('is-open')) {
                    closeProfile();
                    if (profileToggle) {
                        profileToggle.focus();
                    }
                }
            });
        }
    }

    // Entry point
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
