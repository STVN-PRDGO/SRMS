<script data-navigate-once>
(function() {
    function setupLiquidNav() {
        const navContainer = document.querySelector(':is(.fi-panel-teacher, .fi-panel-student) .fi-topbar-nav-groups');
        if (!navContainer) return;

        let glider = navContainer.querySelector('.srms-liquid-glider');
        if (!glider) {
            glider = document.createElement('div');
            glider.className = 'srms-liquid-glider';
            glider.setAttribute('aria-hidden', 'true');
            navContainer.style.position = 'relative';
            navContainer.prepend(glider);
        }

        navContainer.classList.add('srms-has-glider');

        const items = Array.from(navContainer.querySelectorAll('.fi-topbar-item'));
        if (items.length === 0) return;

        function getActiveItem() {
            return navContainer.querySelector('.fi-topbar-item.fi-active') || items[0];
        }

        let isHovered = false;

        function moveTo(el, withAnimation = true) {
            if (!el || !glider) return;
            const containerRect = navContainer.getBoundingClientRect();
            const elRect = el.getBoundingClientRect();

            if (elRect.width === 0 || elRect.height === 0) return;

            const left = elRect.left - containerRect.left;
            const width = elRect.width;

            if (!withAnimation) {
                glider.style.transition = 'none';
            } else {
                glider.style.transition = '';
            }

            glider.style.transform = `translateX(${left}px)`;
            glider.style.width = `${width}px`;
            glider.style.opacity = '1';

            if (!withAnimation) {
                requestAnimationFrame(() => {
                    glider.style.transition = '';
                });
            }
        }

        // Position at active item on load/navigate
        const active = getActiveItem();
        moveTo(active, false);

        // Bind hover and click events
        items.forEach(item => {
            if (item.dataset.liquidBound) return;
            item.dataset.liquidBound = 'true';

            item.addEventListener('mouseenter', () => {
                isHovered = true;
                moveTo(item, true);
            });

            item.addEventListener('mousedown', () => {
                if (glider) {
                    glider.style.transform += ' scaleY(0.92)';
                }
            });

            item.addEventListener('mouseup', () => {
                moveTo(item, true);
            });
        });

        if (!navContainer.dataset.liquidBound) {
            navContainer.dataset.liquidBound = 'true';
            navContainer.addEventListener('mouseleave', () => {
                isHovered = false;
                moveTo(getActiveItem(), true);
            });
        }
    }

    // Initialize on initial page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupLiquidNav);
    } else {
        setupLiquidNav();
    }

    // Initialize and smoothly animate when navigating via Filament SPA
    document.addEventListener('livewire:navigated', () => {
        setTimeout(setupLiquidNav, 30);
    });

    // Handle responsive resize
    window.addEventListener('resize', () => {
        setupLiquidNav();
    });
})();
</script>
