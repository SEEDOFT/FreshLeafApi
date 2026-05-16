import './bootstrap';
import aiAssistantChat from './livewire/ai-assistant-chat';
import supportChat from './livewire/support-chat';

document.addEventListener('alpine:init', () => {
    Alpine.data('aiAssistantChat', aiAssistantChat);
    Alpine.data('supportChat', supportChat);
});

const supportPanelPath = '/admin/support-chat';

const isAdminPanelPath = () => window.location.pathname.startsWith('/admin');
const isSupportPanelPath = () => window.location.pathname === supportPanelPath;

const buildSupportPanelUrl = (ticketId = null) => {
    const url = new URL(supportPanelPath, window.location.origin);

    if (ticketId) {
        url.searchParams.set('activeTicketId', ticketId);
    }

    return url.toString();
};

const getSupportMessageBody = (event) => {
    const senderName = event.ticket_user_name ?? event.sender_name ?? 'Customer';
    const preview = event.message_preview ?? event.message ?? 'Sent an attachment';

    return `${senderName}: ${preview}`;
};

const showSupportToast = ({ title, body, ticketId }) => {
    const existingToast = document.querySelector('[data-support-notification-toast]');

    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('button');
    toast.type = 'button';
    toast.dataset.supportNotificationToast = 'true';
    toast.className = 'freshleaf-support-toast';
    toast.innerHTML = `
        <span class="freshleaf-support-toast__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M7 8h10M7 12h6m8 0c0 4.418-4.03 8-9 8a10.7 10.7 0 0 1-3.55-.597L3 21l1.678-4.474C3.62 15.225 3 13.676 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="freshleaf-support-toast__content">
            <span class="freshleaf-support-toast__title"></span>
            <span class="freshleaf-support-toast__body"></span>
        </span>
    `;

    toast.querySelector('.freshleaf-support-toast__title').textContent = title;
    toast.querySelector('.freshleaf-support-toast__body').textContent = body;
    toast.addEventListener('click', () => {
        window.location.href = buildSupportPanelUrl(ticketId);
    });

    document.body.appendChild(toast);

    window.setTimeout(() => {
        toast.classList.add('is-visible');
    }, 20);

    window.setTimeout(() => {
        toast.classList.remove('is-visible');
        window.setTimeout(() => toast.remove(), 250);
    }, 6200);
};

const showBrowserSupportNotification = ({ title, body, ticketId }) => {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
        return false;
    }

    const notification = new Notification(title, {
        body,
        tag: `freshleaf-support-${ticketId ?? 'new'}`,
        icon: '/favicon.ico',
    });

    notification.onclick = () => {
        window.focus();
        window.location.href = buildSupportPanelUrl(ticketId);
        notification.close();
    };

    return true;
};

const notifyAdminAboutSupport = ({ title, body, ticketId }) => {
    if (!isAdminPanelPath() || isSupportPanelPath()) {
        return;
    }

    const shownInBrowser = showBrowserSupportNotification({ title, body, ticketId });

    if (!shownInBrowser) {
        showSupportToast({ title, body, ticketId });
    }
};

const bootAdminSupportNotifier = () => {
    if (!isAdminPanelPath() || window.App?.adminSupportNotifierBooted) {
        return;
    }

    if (typeof window.Echo === 'undefined') {
        window.setTimeout(bootAdminSupportNotifier, 500);
        return;
    }

    window.App.adminSupportNotifierBooted = true;

    const adminChannel = window.Echo.private('support.admin');

    adminChannel.listen('.SupportMessageSent', (event) => {
        notifyAdminAboutSupport({
            title: 'New support message',
            body: getSupportMessageBody(event),
            ticketId: event.support_ticket_id,
        });
    });

    adminChannel.listen('.NewSupportTicket', (event) => {
        notifyAdminAboutSupport({
            title: 'New support ticket',
            body: `${event.user_name ?? 'Customer'} started a support chat`,
            ticketId: event.id,
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    bootAdminSupportNotifier();

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
