export default () => ({
    isUserTyping: false,
    typingTimeout: null,
    currentConversationId: null,
    pollInterval: null,
    drawerOpen: false,
    isPhone: false,
    mediaQuery: null,
    composerMessage: '',
    composerTextareaHeight: '40px',
    composerTextareaOverflowY: 'hidden',
    typingSendTimeout: null,

    init() {
        this.initDrawer();
        this.scrollToBottom();
        window.App = window.App ?? {};
        window.App.activeSupportConversationId = this.$wire.activeConversationId;
        this.initEcho();
        this.startPolling();
    },

    queueTyping() {
        clearTimeout(this.typingSendTimeout);
        this.typingSendTimeout = setTimeout(() => {
            this.$wire.sendTyping();
        }, 500);
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

    async submitComposer(textarea, hasFile) {
        const message = this.composerMessage.trim();

        if (message === '' && !hasFile) {
            this.resetComposer(textarea);
            return;
        }

        await this.$wire.set('message', message);
        await this.$wire.sendMessage();
        this.resetComposer(textarea);
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

        this.listenToConversation(this.$wire.activeConversationId);
    },

    listenToConversation(conversationId) {
        if (!conversationId || typeof window.Echo === 'undefined') return;
        window.App = window.App ?? {};
        window.App.activeSupportConversationId = conversationId;
        if (this.currentConversationId) {
            window.Echo.leave('chat.conversation.' + this.currentConversationId);
        }
        this.currentConversationId = conversationId;
        const conversationChannel = window.Echo.private('chat.conversation.' + conversationId);
        conversationChannel.listen('.ChatMessageSent', (e) => {
            this.$wire.handleIncomingMessage(e);
        });
        conversationChannel.listen('.ChatTyping', (e) => {
            this.$wire.handleTypingEvent(e);
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
        clearTimeout(this.typingSendTimeout);
    }
});
