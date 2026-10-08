document.addEventListener('DOMContentLoaded', function() {
    // Live topbar clock
    const clockTimeEl = document.getElementById('topbarClockTime');
    const clockDateEl = document.getElementById('topbarClockDate');

    if (clockTimeEl && clockDateEl) {
        const updateTopbarClock = () => {
            const now = new Date();
            clockTimeEl.textContent = now.toLocaleTimeString('en-IN', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });
            clockDateEl.textContent = now.toLocaleDateString('en-IN', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        };

        updateTopbarClock();
        setInterval(updateTopbarClock, 1000);
    }

    // Sidebar Toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
    const sidebarClose = document.getElementById('sidebarClose');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.add('show');
            sidebarBackdrop.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when sidebar is open
        });
    }

    const closeSidebar = () => {
        adminSidebar.classList.remove('show');
        sidebarBackdrop.classList.remove('show');
        document.body.style.overflow = '';
    };

    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    // Submenu Toggle
    const menuLinks = document.querySelectorAll('.sidebar-menu-link');
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const parentItem = this.closest('.sidebar-menu-item');
            const submenu = this.nextElementSibling;
            if (submenu && submenu.classList.contains('sidebar-submenu')) {
                e.preventDefault();

                const arrow = this.querySelector('.sidebar-menu-arrow');
                const isOpen = submenu.classList.contains('show');

                if (isOpen) {
                    submenu.classList.remove('show');
                    if (parentItem) parentItem.classList.remove('is-open');
                    if (arrow) arrow.classList.replace('fa-chevron-down', 'fa-chevron-right');
                } else {
                    submenu.classList.add('show');
                    if (parentItem) parentItem.classList.add('is-open');
                    if (arrow) arrow.classList.replace('fa-chevron-right', 'fa-chevron-down');
                }
            }
        });
    });

    // Auto-open active submenus on page load
    document.querySelectorAll('.sidebar-submenu.show').forEach(parentSubmenu => {
        const parentItem = parentSubmenu.closest('.sidebar-menu-item');
        const parentLink = parentSubmenu.previousElementSibling;
        if (parentItem) parentItem.classList.add('is-open');
        if (parentLink) {
            const arrow = parentLink.querySelector('.sidebar-menu-arrow');
            if (arrow) arrow.classList.replace('fa-chevron-right', 'fa-chevron-down');
        }
    });

    const activeSubLink = document.querySelector('.sidebar-submenu-link.active');
    if (activeSubLink) {
        const parentSubmenu = activeSubLink.closest('.sidebar-submenu');
        if (parentSubmenu) {
            parentSubmenu.classList.add('show');
            const parentItem = parentSubmenu.closest('.sidebar-menu-item');
            const parentLink = parentSubmenu.previousElementSibling;
            if (parentItem) parentItem.classList.add('is-open');
            if (parentLink) {
                const arrow = parentLink.querySelector('.sidebar-menu-arrow');
                if (arrow) arrow.classList.replace('fa-chevron-right', 'fa-chevron-down');
            }
        }
    }

    // Handle session alerts auto-dismiss
    const alerts = document.querySelectorAll('.alert[data-dismiss="auto"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
});

/**
 * Premium Toast Notification
 * @param {string} message 
 * @param {string} type - 'success', 'error', 'warning', 'info'
 */
function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type} animate-slide-in`;
    
    const icon = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    }[type];

    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${icon} toast-icon"></i>
            <span class="toast-message">${message}</span>
        </div>
        <div class="toast-progress"></div>
    `;

    toastContainer.appendChild(toast);

    // Remove toast after 4 seconds
    setTimeout(() => {
        toast.classList.replace('animate-slide-in', 'animate-slide-out');
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

/**
 * Profile photo zoom lightbox (click to enlarge, +/- / wheel to zoom, drag to pan)
 */
(function initImageZoomLightbox() {
    let lightbox = null;
    let imgEl = null;
    let stageEl = null;
    let scale = 1;
    let translateX = 0;
    let translateY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let originX = 0;
    let originY = 0;

    function ensureLightbox() {
        if (lightbox) return lightbox;

        lightbox = document.createElement('div');
        lightbox.className = 'image-zoom-lightbox';
        lightbox.setAttribute('role', 'dialog');
        lightbox.setAttribute('aria-modal', 'true');
        lightbox.setAttribute('aria-label', 'Image preview');
        lightbox.innerHTML = `
            <button type="button" class="image-zoom-lightbox__close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            <div class="image-zoom-lightbox__hint">Scroll to zoom · Drag to move</div>
            <div class="image-zoom-lightbox__stage">
                <img class="image-zoom-lightbox__img" alt="">
            </div>
            <div class="image-zoom-lightbox__controls">
                <button type="button" class="image-zoom-lightbox__btn" data-zoom-action="out" aria-label="Zoom out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <button type="button" class="image-zoom-lightbox__btn" data-zoom-action="reset" aria-label="Reset zoom">
                    <i class="fas fa-compress"></i>
                </button>
                <button type="button" class="image-zoom-lightbox__btn" data-zoom-action="in" aria-label="Zoom in">
                    <i class="fas fa-search-plus"></i>
                </button>
            </div>
        `;
        document.body.appendChild(lightbox);

        imgEl = lightbox.querySelector('.image-zoom-lightbox__img');
        stageEl = lightbox.querySelector('.image-zoom-lightbox__stage');

        lightbox.querySelector('.image-zoom-lightbox__close').addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });

        lightbox.querySelectorAll('[data-zoom-action]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.getAttribute('data-zoom-action');
                if (action === 'in') setScale(scale + 0.25);
                if (action === 'out') setScale(scale - 0.25);
                if (action === 'reset') resetTransform();
            });
        });

        stageEl.addEventListener('wheel', (e) => {
            e.preventDefault();
            setScale(scale + (e.deltaY < 0 ? 0.15 : -0.15));
        }, { passive: false });

        stageEl.addEventListener('pointerdown', (e) => {
            if (scale <= 1) return;
            isDragging = true;
            stageEl.classList.add('is-dragging');
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            originX = translateX;
            originY = translateY;
            stageEl.setPointerCapture(e.pointerId);
        });

        stageEl.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            translateX = originX + (e.clientX - dragStartX);
            translateY = originY + (e.clientY - dragStartY);
            applyTransform();
        });

        const endDrag = () => {
            isDragging = false;
            stageEl.classList.remove('is-dragging');
        };
        stageEl.addEventListener('pointerup', endDrag);
        stageEl.addEventListener('pointercancel', endDrag);

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('is-open')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === '+' || e.key === '=') setScale(scale + 0.25);
            if (e.key === '-') setScale(scale - 0.25);
            if (e.key === '0') resetTransform();
        });

        return lightbox;
    }

    function applyTransform() {
        imgEl.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    }

    function setScale(next) {
        scale = Math.min(4, Math.max(1, next));
        if (scale === 1) {
            translateX = 0;
            translateY = 0;
        }
        applyTransform();
    }

    function resetTransform() {
        scale = 1;
        translateX = 0;
        translateY = 0;
        applyTransform();
    }

    function openLightbox(src, alt) {
        ensureLightbox();
        resetTransform();
        imgEl.src = src;
        imgEl.alt = alt || 'Profile photo';
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
        resetTransform();
    }

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-zoom-image]');
        if (!trigger) return;
        e.preventDefault();
        openLightbox(trigger.getAttribute('data-zoom-image'), trigger.getAttribute('data-zoom-alt') || trigger.alt);
    });
})();
