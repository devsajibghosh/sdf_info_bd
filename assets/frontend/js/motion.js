/* ==========================================================================
   Homepage Motion Engine — Sanatani Development Foundation
   --------------------------------------------------------------------------
   Vanilla JS, no new dependency. Adds ".is-visible" to ".reveal"/".reveal-scale"
   elements once, the first time they enter the viewport. Falls back to making
   everything visible immediately when IntersectionObserver isn't available or
   the visitor has requested reduced motion — content must never stay hidden.
   ========================================================================== */
(function () {
    "use strict";

    var targets = document.querySelectorAll('.reveal, .reveal-scale');
    if (!targets.length) return;

    var prefersReducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function showAll() {
        targets.forEach(function (el) {
            el.classList.add('is-visible');
        });
    }

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        showAll();
        return;
    }

    var observer = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -8% 0px'
    });

    targets.forEach(function (el) {
        observer.observe(el);
    });
})();
