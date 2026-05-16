export default () => ({
    isUserTyping: false,
    typingTimeout: null,
    currentTicketId: null,
    pollInterval: null,
    showHistory: true,
    drawerOpen: false,
    isPhone: false,
    mediaQuery: null,

    init() {
        this.showHistory = this.$wire.entangle('showHistory');
        this.initDrawer();
        this.scrollToBottom();
        this.initEcho();
        this.startPolling();
    },

    syncViewportState(event) {
        this.isPhone = event.matches;
        if (!this.isPhone) this.drawerOpen = false;
    },

    initDrawer() {
        this.mediaQuery = window.matchMedia('(max-width: 767px)');
        this.syncViewportState(this.mediaQuery);
        if (this.mediaQuery.addEventListener) {
            this.mediaQuery.addEventListener('change', (event) => this.syncViewportState(event));
        } else {
            this.mediaQuery.addListener((event) => this.syncViewportState(event));
        }
    },

    toggleDrawer() {
        if (!this.isPhone) {
            this.$wire.toggleHistory();
            return;
        }
        this.drawerOpen = !this.drawerOpen;
    },

    closeDrawer() {
        this.drawerOpen = false;
    },

    handleEscape(event) {
        if (event.key === 'Escape' && this.drawerOpen) {
            this.closeDrawer();
        }
    },

    scrollToBottom() {
        const container = document.getElementById('support-thread');
        if (container) {
            requestAnimationFrame(() => {
                container.scrollTop = container.scrollHeight;
            });
        }
    },

    initEcho() {
        if (typeof window.Echo === 'undefined') {
            setTimeout(() => this.initEcho(), 2000);
            return;
        }

        const adminChannel = window.Echo.private('support.admin');
        adminChannel.listen('.NewSupportTicket', () => {
            this.$wire.$refresh();
        });
        adminChannel.listen('.SupportMessageSent', () => {
            this.$wire.$refresh();
        });
        adminChannel.listen('.SupportTyping', (e) => {
            if (e.sender_type === 'user') {
                this.isUserTyping = true;
                clearTimeout(this.typingTimeout);
                this.typingTimeout = setTimeout(() => {
                    this.isUserTyping = false;
                }, 3000);
                setTimeout(() => this.scrollToBottom(), 50);
            }
        });

        this.listenToTicket(this.$wire.activeTicketId);
    },

    listenToTicket(ticketId) {
        if (!ticketId) return;
        if (this.currentTicketId) {
            window.Echo.leave('support.ticket.' + this.currentTicketId);
        }
        this.currentTicketId = ticketId;
        const ticketChannel = window.Echo.private('support.ticket.' + ticketId);
        ticketChannel.listen('.SupportMessageSent', (e) => {
            this.$wire.handleIncomingMessage(e);
        });
    },

    startPolling() {
        if (this.pollInterval) return;
        this.pollInterval = setInterval(async () => {
            await this.$wire.$refresh();
        }, 5000);
    },

    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
});
