import './bootstrap';
import aiAssistantChat from './livewire/ai-assistant-chat';
import supportChat from './livewire/support-chat';

document.addEventListener('alpine:init', () => {
    Alpine.data('aiAssistantChat', aiAssistantChat);
    Alpine.data('supportChat', supportChat);
});

const getSupportPanelPath = () => (
    window.location.pathname.startsWith('/vendor')
        ? '/vendor/support-chat'
        : '/admin/support-chat'
);

const isPanelPath = () => window.location.pathname.startsWith('/admin') || window.location.pathname.startsWith('/vendor');
const isSupportPanelPath = () => window.location.pathname === getSupportPanelPath();
const activeSupportConversationId = () => {
    if (!isSupportPanelPath()) {
        return null;
    }

    return window.App?.activeSupportConversationId ?? new URLSearchParams(window.location.search).get('activeConversationId');
};

const buildSupportPanelUrl = (conversationId = null) => {
    const url = new URL(getSupportPanelPath(), window.location.origin);

    if (conversationId) {
        url.searchParams.set('activeConversationId', conversationId);
    }

    return url.toString();
};

const getChatNotificationData = (event) => {
    const data = event?.data?.data ?? event?.data ?? event ?? {};

    if (data.type !== 'chat_message') {
        return null;
    }

    return data;
};

const getSupportMessageBody = (data) => {
    const senderName = data.sender_name ?? 'User';
    const preview = data.message_preview ?? data.message ?? data.body ?? 'Sent an attachment';

    return `${senderName}: ${preview}`;
};

const showSupportToast = ({ title, body, conversationId }) => {
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
        window.location.href = buildSupportPanelUrl(conversationId);
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

const showBrowserSupportNotification = ({ title, body, conversationId }) => {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
        return false;
    }

    const notification = new Notification(title, {
        body,
        tag: `freshleaf-chat-${conversationId ?? 'new'}`,
        icon: '/favicon.ico',
    });

    notification.onclick = () => {
        window.focus();
        window.location.href = buildSupportPanelUrl(conversationId);
        notification.close();
    };

    return true;
};

const notifyAboutChat = ({ title, body, conversationId }) => {
    if (!isPanelPath()) {
        return;
    }

    if (isSupportPanelPath() && String(conversationId) === String(activeSupportConversationId())) {
        return;
    }

    const shownInBrowser = showBrowserSupportNotification({ title, body, conversationId });

    if (!shownInBrowser) {
        showSupportToast({ title, body, conversationId });
    }
};

const updateHamburgerBadge = () => {
    const hamburgerBtn = document.querySelector('.fi-layout-sidebar-toggle-btn');
    if (!hamburgerBtn) return;

    const supportLink = document.querySelector('a[href$="/support-chat"]');
    let count = 0;
    
    if (supportLink) {
        const badge = supportLink.querySelector('.fi-badge');
        if (badge) {
            count = parseInt(badge.textContent.trim(), 10) || 0;
        }
    }

    let existingBadge = hamburgerBtn.querySelector('.custom-hamburger-badge');
    
    if (count > 0) {
        if (!existingBadge) {
            existingBadge = document.createElement('span');
            existingBadge.className = 'custom-hamburger-badge fi-badge fi-color-custom fi-color-danger fi-size-xs';
            existingBadge.style.position = 'absolute';
            existingBadge.style.top = '-2px';
            existingBadge.style.right = '-2px';
            existingBadge.style.borderRadius = '9999px';
            existingBadge.style.padding = '2px 4px';
            existingBadge.style.fontSize = '0.65rem';
            existingBadge.style.lineHeight = '1';
            existingBadge.style.backgroundColor = 'var(--danger-600)';
            existingBadge.style.color = '#fff';
            existingBadge.style.display = 'flex';
            existingBadge.style.alignItems = 'center';
            existingBadge.style.justifyContent = 'center';
            existingBadge.style.minWidth = '1.25rem';
            existingBadge.style.height = '1.25rem';
            
            hamburgerBtn.style.position = 'relative';
            hamburgerBtn.style.overflow = 'visible';
            hamburgerBtn.appendChild(existingBadge);
        }
        existingBadge.textContent = count;
    } else if (existingBadge) {
        existingBadge.remove();
    }
};

const bootChatNotifier = () => {
    if (!isPanelPath() || window.App?.chatNotifierBooted) {
        return;
    }

    if (typeof window.Echo === 'undefined') {
        window.setTimeout(bootChatNotifier, 500);
        return;
    }

    if (!window.App?.authUserId) {
        window.setTimeout(bootChatNotifier, 500);
        return;
    }

    window.App.chatNotifierBooted = true;

    window.Echo.private(`App.Models.User.${window.App.authUserId}`)
        .listen('.ChatNotificationSent', (event) => {
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch('refresh-sidebar');
            }

            const data = getChatNotificationData(event);

            if (!data) {
                return;
            }

            notifyAboutChat({
                title: event.title ?? data.title ?? 'New chat message',
                body: event.body ? getSupportMessageBody({ ...data, body: event.body }) : getSupportMessageBody(data),
                conversationId: data.conversation_id,
            });
        })
        .notification((event) => {
            const data = getChatNotificationData(event);

            if (!data) {
                return;
            }

            notifyAboutChat({
                title: event.title ?? data.title ?? 'New chat message',
                body: getSupportMessageBody(data),
                conversationId: data.conversation_id,
            });
        });
};

document.addEventListener('DOMContentLoaded', () => {
    bootChatNotifier();

    window.setTimeout(updateHamburgerBadge, 100);

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

document.addEventListener('livewire:initialized', () => {
    window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            setTimeout(updateHamburgerBadge, 50);
        });
    });
});
