export default () => ({
    drawerOpen: false,
    showHistory: false,
    isPhone: false,
    mediaQuery: null,
    pollInterval: null,

    init() {
        this.showHistory = this.$wire.entangle('showHistory');
        this.initDrawer();
        this.scrollToBottom();

        this.$watch('$wire.pendingAssistantMessageId', value => {
            value ? this.startPolling() : this.stopPolling();
        });
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
        const container = document.getElementById('ai-thread-scroll');
        if (container) {
            // Use requestAnimationFrame to ensure DOM is updated
            requestAnimationFrame(() => {
                container.scrollTop = container.scrollHeight;
            });
        }
    },

    startPolling() {
        if (this.pollInterval) return;
        this.pollInterval = setInterval(async () => {
            await this.$wire.syncPendingResponse();
        }, 2000);
    },

    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
});
