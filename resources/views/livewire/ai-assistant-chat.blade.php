<div
    class="ai-chat-shell"
    wire:poll.2s="syncPendingResponse"
    x-data="{
        drawerOpen: false,
        isPhone: false,
        mediaQuery: null,
        syncViewportState(event) {
            this.isPhone = event.matches;

            if (! this.isPhone) {
                this.drawerOpen = false;
            }
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
            if (! this.isPhone) {
                return;
            }

            this.drawerOpen = ! this.drawerOpen;
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
                container.scrollTop = container.scrollHeight;
            }
        },
    }"
    x-init="initDrawer(); scrollToBottom()"
    x-on:keydown.window="handleEscape($event)"
    x-on:message-sent.window="setTimeout(() => scrollToBottom(), 50); if (isPhone) { closeDrawer(); }"
    x-on:freshleaf-realtime-status.window="$wire.handleRealtimeStatus(($event.detail && $event.detail.state) ? $event.detail.state : null, ($event.detail && $event.detail.reason) ? $event.detail.reason : null)"
    x-bind:data-drawer-open="drawerOpen ? 'true' : 'false'"
>
    <button
        type="button"
        x-cloak
        x-show="isPhone && drawerOpen"
        class="ai-chat-sidebar-drawer-overlay"
        x-on:click="closeDrawer()"
        aria-label="Close conversations panel"
    ></button>

    <aside id="ai-chat-sidebar" class="ai-chat-sidebar">
        <div class="ai-chat-sidebar-head">
            <button
                type="button"
                class="ai-new-chat-btn"
                wire:click="startNewChat"
                wire:loading.attr="disabled"
                wire:target="startNewChat"
                x-on:click="if (isPhone) { closeDrawer(); }"
            >
                <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                <span>New chat</span>
            </button>
        </div>

        <div class="ai-session-list">
            @forelse($sessions as $session)
                <button
                    type="button"
                    class="ai-session-item {{ $this->isActiveSession($session['id']) ? 'is-active' : '' }}"
                    wire:click="switchSession({{ $session['id'] }})"
                    wire:key="session-{{ $session['id'] }}"
                    x-on:click="if (isPhone) { closeDrawer(); }"
                >
                    <span class="ai-session-title">{{ $session['title'] }}</span>
                    <span class="ai-session-meta">{{ $session['updated_at_human'] }}</span>
                </button>
            @empty
                <div class="ai-session-empty">
                    <p>No conversations yet.</p>
                </div>
            @endforelse
        </div>
    </aside>

    <section class="ai-chat-main">
        <header class="ai-chat-header">
            <div class="ai-chat-header-title">
                <button
                    type="button"
                    class="ai-drawer-toggle"
                    x-on:click="toggleDrawer()"
                    aria-controls="ai-chat-sidebar"
                    x-bind:aria-expanded="drawerOpen ? 'true' : 'false'"
                >
                    <x-filament::icon icon="heroicon-o-bars-3" class="h-4 w-4" />
                    <span>Chats</span>
                    <span class="ai-drawer-toggle-count">{{ count($sessions) }}</span>
                </button>

                <span class="ai-logo-dot"></span>
                <div>
                    <h2>FreshLeaf Assistant</h2>
                    <p>Chat-style support for operational tasks and store decisions.</p>
                </div>
            </div>

            <div class="ai-chat-status">
                <span class="ai-status-pill {{ $isRealtimeConnected ? 'is-connected' : 'is-fallback' }}">
                    {{ $isRealtimeConnected ? 'Realtime connected' : 'Fallback sync mode' }}
                </span>
            </div>
        </header>

        @if($realtimeStatusMessage)
            <div class="ai-chat-banner">
                <x-filament::icon icon="heroicon-o-signal-slash" class="h-4 w-4" />
                <span>{{ $realtimeStatusMessage }}</span>
            </div>
        @endif

        <div class="ai-chat-thread" id="ai-thread-scroll">
            <div class="ai-thread-inner">
                @if(count($messages) === 0)
                    <div class="ai-thread-empty">
                        <h3>Start a new conversation</h3>
                        <p>Ask about inventory, order flow, vendor onboarding, or operational decisions.</p>
                    </div>
                @endif

                @foreach($messages as $messageItem)
                    @if($messageItem['role'] === 'user')
                        <div class="ai-message-row is-user">
                            <article class="ai-message-bubble is-user">
                                <p>{{ $messageItem['content'] }}</p>
                            </article>
                        </div>
                    @else
                        <div class="ai-message-row is-assistant">
                            <div class="ai-assistant-avatar">
                                <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                            </div>

                            <article class="ai-message-bubble is-assistant {{ ($messageItem['status'] ?? 'done') === 'failed' ? 'is-error' : '' }}">
                                <div class="ai-message-author">FreshLeaf Assistant</div>
                                <div class="freshleaf-markdown">
                                    {!! $this->renderAssistantMessage($messageItem['content']) !!}
                                </div>
                            </article>
                        </div>
                    @endif
                @endforeach

                @if($isTyping)
                    <div class="ai-message-row is-assistant">
                        <div class="ai-assistant-avatar is-loading">
                            <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                        </div>

                        <article class="ai-message-bubble is-assistant">
                            <div class="ai-message-author">FreshLeaf Assistant</div>
                            <div class="ai-typing-dots" aria-label="Assistant is typing">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </article>
                    </div>
                @endif
            </div>
        </div>

        <div class="ai-chat-composer">
            <form wire:submit.prevent="sendMessage" class="ai-composer-form">
                <label for="ai-message" class="sr-only">Type your message</label>
                <textarea
                    id="ai-message"
                    wire:model.live.debounce.150ms="message"
                    rows="1"
                    placeholder="Message FreshLeaf Assistant..."
                    class="ai-composer-input"
                    x-data="{ resize() { $el.style.height = 'auto'; $el.style.height = `${Math.min($el.scrollHeight, 220)}px`; } }"
                    x-on:input="resize()"
                    x-init="resize()"
                    x-on:message-sent.window="resize()"
                    x-on:keydown.enter.prevent="if (! $event.shiftKey) { $wire.sendMessage(); }"
                    :disabled="$wire.isTyping"
                ></textarea>

                <button
                    type="submit"
                    class="ai-send-button"
                    wire:loading.attr="disabled"
                    :disabled="$wire.isTyping || $wire.message.trim() === ''"
                >
                    <x-filament::icon icon="heroicon-o-arrow-up" class="h-4 w-4" />
                    <span>Send</span>
                </button>
            </form>
            <p class="ai-composer-hint">
                Press Enter to send, Shift+Enter for a new line.
            </p>
        </div>
    </section>
</div>
