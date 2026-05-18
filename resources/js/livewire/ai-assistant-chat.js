export default () => ({
    drawerOpen: false,
    showHistory: false,
    isPhone: false,
    mediaQuery: null,
    pollInterval: null,
    composerMessage: '',
    composerTextareaHeight: '40px',
    composerTextareaOverflowY: 'hidden',

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
    },

    resizeTextarea(textarea) {
        const minHeight = 40;
        const maxHeight = 150;

        if (this.composerMessage === '') {
            this.resetTextarea(textarea);
            return;
        }

        textarea.style.height = 'auto';
        this.composerTextareaHeight = Math.max(minHeight, Math.min(textarea.scrollHeight, maxHeight)) + 'px';
        this.composerTextareaOverflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
        textarea.style.height = this.composerTextareaHeight;
        textarea.style.overflowY = this.composerTextareaOverflowY;
    },

    resetTextarea(textarea) {
        this.composerTextareaHeight = '40px';
        this.composerTextareaOverflowY = 'hidden';

        if (textarea) {
            textarea.style.height = this.composerTextareaHeight;
            textarea.style.overflowY = this.composerTextareaOverflowY;
        }
    },

    resetComposer(textarea) {
        this.composerMessage = '';
        this.resetTextarea(textarea);
    },

    async submitComposer(textarea) {
        const message = this.composerMessage.trim();

        if (message === '') {
            this.resetComposer(textarea);
            return;
        }

        await this.$wire.set('message', message);
        await this.$wire.sendMessage();
        this.resetComposer(textarea);
    },
});
