import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('app-toast');

    if (toast && toast.classList.contains('is-visible')) {
        window.setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 2800);
    }

    const body = document.body;
    const sidebar = document.querySelector('[data-sidebar]');
    const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');
    const sidebarToggles = document.querySelectorAll('[data-sidebar-toggle]');
    const sidebarCloseButtons = document.querySelectorAll('[data-sidebar-close]');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    const mobileQuery = window.matchMedia('(max-width: 1024px)');

    const syncSidebarState = (isOpen) => {
        body.classList.toggle('sidebar-open', isOpen);

        if (sidebarOverlay) {
            sidebarOverlay.hidden = !isOpen;
        }

        sidebarToggles.forEach((toggle) => {
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    };

    if (sidebar && sidebarOverlay && sidebarToggles.length > 0) {
        sidebarToggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const shouldOpen = !body.classList.contains('sidebar-open');
                syncSidebarState(shouldOpen);
            });
        });

        sidebarCloseButtons.forEach((closeButton) => {
            closeButton.addEventListener('click', () => {
                syncSidebarState(false);
            });
        });

        sidebarOverlay.addEventListener('click', () => {
            syncSidebarState(false);
        });

        sidebarLinks.forEach((link) => {
            link.addEventListener('click', () => {
                if (mobileQuery.matches) {
                    syncSidebarState(false);
                }
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && body.classList.contains('sidebar-open')) {
                syncSidebarState(false);
            }
        });

        mobileQuery.addEventListener('change', (event) => {
            if (!event.matches) {
                syncSidebarState(false);
            }
        });
    }

    const revealTargets = document.querySelectorAll('.section-card, .summary-card, .timeline-item, .empty-state, .auth-block');

    revealTargets.forEach((element, index) => {
        element.dataset.reveal = 'true';

        window.setTimeout(() => {
            element.classList.add('is-visible');
        }, 40 + (index % 8) * 45);
    });

    const welcomeTabs = document.querySelectorAll('[data-role-tab]');
    const welcomePanels = document.querySelectorAll('[data-role-panel]');

    if (welcomeTabs.length > 0 && welcomePanels.length > 0) {
        const activateWelcomePanel = (role) => {
            welcomeTabs.forEach((tab) => {
                const isActive = tab.dataset.roleTab === role;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            welcomePanels.forEach((panel) => {
                const isActive = panel.dataset.rolePanel === role;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });
        };

        welcomeTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activateWelcomePanel(tab.dataset.roleTab);
            });
        });
    }
});
