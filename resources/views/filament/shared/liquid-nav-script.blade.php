<script data-navigate-once>
(function () {
    function setupLiquidNav() {
        const navContainer = document.querySelector(':is(.fi-panel-teacher, .fi-panel-student) .fi-topbar-nav-groups');
        if (!navContainer) {
            return;
        }

        let glider = navContainer.querySelector('.srms-liquid-glider');
        if (!glider) {
            glider = document.createElement('div');
            glider.className = 'srms-liquid-glider';
            glider.setAttribute('aria-hidden', 'true');
            navContainer.prepend(glider);
        }

        navContainer.classList.add('srms-has-glider');

        const items = Array.from(navContainer.querySelectorAll('.fi-topbar-item'));
        if (items.length === 0) {
            return;
        }

        function pathOf(url) {
            try {
                const path = new URL(url, window.location.origin).pathname.replace(/\/$/, '');

                return path === '' ? '/' : path;
            } catch (error) {
                return '/';
            }
        }

        function markActive() {
            const current = pathOf(window.location.href);
            let active = null;
            let activeLength = -1;

            items.forEach((item) => {
                const link = item.querySelector('a[href]');
                item.classList.remove('fi-active');
                if (!link) {
                    return;
                }

                const href = pathOf(link.href);
                const matches = current === href || (href !== '/' && current.startsWith(href + '/'));
                link.removeAttribute('aria-current');

                if (matches && href.length > activeLength) {
                    active = item;
                    activeLength = href.length;
                }
            });

            if (!active) {
                active = items[0];
            }

            active.classList.add('fi-active');
            active.querySelector('a[href]')?.setAttribute('aria-current', 'page');

            return active;
        }

        function moveTo(el) {
            if (!el || !glider) {
                return;
            }

            const containerRect = navContainer.getBoundingClientRect();
            const elRect = el.getBoundingClientRect();

            if (elRect.width === 0 || elRect.height === 0) {
                return;
            }

            glider.style.transform = `translateX(${elRect.left - containerRect.left}px)`;
            glider.style.width = `${elRect.width}px`;
            glider.style.opacity = '1';
        }

        moveTo(markActive());

        if (navContainer.dataset.liquidBound === 'true') {
            return;
        }

        navContainer.dataset.liquidBound = 'true';
        navContainer.addEventListener('click', (event) => {
            const item = event.target.closest('.fi-topbar-item');
            if (!item || !navContainer.contains(item)) {
                return;
            }

            items.forEach((entry) => entry.classList.remove('fi-active'));
            item.classList.add('fi-active');
            moveTo(item);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupLiquidNav);
    } else {
        setupLiquidNav();
    }

    document.addEventListener('livewire:navigated', () => {
        requestAnimationFrame(setupLiquidNav);
    });

    window.addEventListener('resize', () => {
        setupLiquidNav();
    });
})();
</script>
