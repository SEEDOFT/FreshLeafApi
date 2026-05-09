<div class="ai-chat-shell" wire:poll.2s="syncPendingResponse" x-data="{
        drawerOpen: false,
        showHistory: @entangle('showHistory'),
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
                this.showHistory = ! this.showHistory;
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
    }" x-init="initDrawer(); scrollToBottom()" x-on:keydown.window="handleEscape($event)"
    x-on:message-sent.window="setTimeout(() => scrollToBottom(), 50); if (isPhone) { closeDrawer(); }"
    x-on:freshleaf-realtime-status.window="$wire.handleRealtimeStatus(($event.detail && $event.detail.state) ? $event.detail.state : null, ($event.detail && $event.detail.reason) ? $event.detail.reason : null)"
    x-bind:data-drawer-open="drawerOpen ? 'true' : 'false'"
    x-bind:class="{ 'ai-chat-shell': true, 'is-history-hidden': !showHistory }">
    <button type="button" x-cloak x-show="isPhone && drawerOpen" class="ai-chat-sidebar-drawer-overlay"
        x-on:click="closeDrawer()" aria-label="{{ __('admin.ai.chats') }}"></button>

    <aside id="ai-chat-sidebar" class="ai-chat-sidebar">
        <div class="ai-chat-sidebar-head">
            <button type="button" class="ai-new-chat-btn" wire:click="startNewChat" wire:loading.attr="disabled"
                wire:target="startNewChat" x-on:click="if (isPhone) { closeDrawer(); }">
                <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                <span>{{ __('admin.ai.new_chat') }}</span>
            </button>
        </div>

        <div class="ai-session-list">
            @forelse($sessions as $session)
                <div class="ai-session-item-wrap group">
                    <button type="button"
                        class="ai-session-item {{ $this->isActiveSession($session['id']) ? 'is-active' : '' }}"
                        wire:click="switchSession({{ $session['id'] }})" wire:key="session-{{ $session['id'] }}"
                        x-on:click="if (isPhone) { closeDrawer(); }">
                        <span class="ai-session-title line-clamp-1">{{ $session['title'] }}</span>
                        <span class="ai-session-meta">{{ $session['updated_at_human'] }}</span>
                    </button>

                    <button type="button" class="ai-session-delete-btn" wire:click="deleteSession({{ $session['id'] }})"
                        wire:confirm="{{ __('admin.ai.delete_confirm') }}" aria-label="{{ __('admin.ai.delete_chat') }}">
                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                    </button>
                </div>
            @empty
                <div class="ai-session-empty">
                    <p>{{ __('admin.ai.no_conversations') }}</p>
                </div>
            @endforelse
        </div>
    </aside>

    <section class="ai-chat-main">
        <header class="ai-chat-header">
            <div class="ai-chat-header-title">
                <button type="button" class="ai-drawer-toggle" x-on:click="toggleDrawer()"
                    aria-controls="ai-chat-sidebar" x-bind:aria-expanded="drawerOpen ? 'true' : 'false'"
                    x-bind:title="showHistory ? 'Close sidebar' : 'Open sidebar'">
                    <x-filament::icon icon="heroicon-o-bars-3" class="h-5 w-5" />
                </button>

                <span class="ai-logo-dot"></span>
                <div>
                    <h2>{{ __('admin.ai.freshleaf_assistant') }}</h2>
                    <p class="hidden sm:block">{{ __('admin.ai.description') }}</p>
                </div>
            </div>

            <div class="ai-chat-status">
                <span
                    class="ai-status-pill {{ $isAiServiceAvailable ? ($isRealtimeConnected ? 'is-connected' : 'is-fallback') : 'is-error' }}">
                    {{ $isAiServiceAvailable ? ($isRealtimeConnected ? __('admin.ai.realtime_connected') : __('admin.ai.fallback_sync_mode')) : __('admin.ai.service_unavailable') }}
                </span>
            </div>
        </header>

        @if(!$isAiServiceAvailable)
            <div class="ai-chat-banner ai-chat-banner-error">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4" />
                <span>{{ __('admin.ai.service_unavailable_banner') }}</span>
            </div>
        @elseif($realtimeStatusMessage)
            <div class="ai-chat-banner">
                <x-filament::icon icon="heroicon-o-signal-slash" class="h-4 w-4" />
                <span>{{ $realtimeStatusMessage }}</span>
            </div>
        @endif

        <div class="ai-chat-thread" id="ai-thread-scroll">
            <div class="ai-thread-inner">
                @if(count($messages) === 0)
                    <div class="ai-thread-empty">
                        <h3>{{ __('admin.ai.start_new_conversation') }}</h3>
                        <p>{{ __('admin.ai.empty_state_desc') }}</p>
                    </div>
                @endif

                @foreach($messages as $messageItem)
                    @if($messageItem['role'] === 'user')
                        <div class="ai-message-row is-user">
                            <article class="ai-message-bubble is-user">
                                <p>{{ $messageItem['content'] }}</p>
                            </article>
                        </div>
                    @elseif($messageItem['role'] === 'assistant')
                        @if(($messageItem['content'] ?? '') !== '' || ($messageItem['status'] ?? 'done') === 'failed')
                            <div class="ai-message-row is-assistant">
                                <div class="ai-assistant-avatar">
                                    <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                                </div>

                                <article
                                    class="ai-message-bubble is-assistant {{ ($messageItem['status'] ?? 'done') === 'failed' ? 'is-error' : '' }}">
                                    <div class="ai-message-author">{{ __('admin.ai.freshleaf_assistant') }}</div>
                                    <div class="freshleaf-markdown">
                                        {!! $this->renderAssistantMessage($messageItem['content']) !!}
                                    </div>
                                </article>
                            </div>
                        @endif
                    @endif
                @endforeach

                @if($isTyping)
                    <div class="ai-message-row is-assistant">
                        <div class="ai-assistant-avatar is-loading">
                            <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                        </div>

                        <article class="ai-message-bubble is-assistant">
                            <div class="ai-message-author">{{ __('admin.ai.freshleaf_assistant') }}</div>
                            <div class="ai-thinking-wrapper">
                                <span>{{ __('admin.ai.thinking') }}</span>
                                <span class="ai-pulse-dot"></span>
                            </div>
                        </article>
                    </div>
                @endif
            </div>
        </div>

        <div class="ai-chat-composer" wire:ignore>
            <form x-data="{
                    localMessage: '',
                    resize() {
                        const input = this.$refs.composerInput;
                        input.style.height = '44px';
                        input.style.height = input.scrollHeight + 'px';
                    },
                    submit() {
                        const message = this.localMessage.trim();
                        if (message === '') return;

                        $wire.set('message', message);
                        $wire.sendMessage();

                        this.localMessage = '';
                        this.$nextTick(() => {
                            this.$refs.composerInput.style.height = '44px';
                        });
                    }
                }" x-on:submit.prevent="submit()" class="ai-composer-form">
                <label for="ai-message" class="sr-only">{{ __('admin.ai.type_message') }}</label>
                <textarea id="ai-message" x-ref="composerInput" x-model="localMessage" x-on:input="resize()" rows="1"
                    placeholder="{{ $isAiServiceAvailable ? __('admin.ai.composer_placeholder') : __('admin.ai.service_unavailable') }}"
                    class="ai-composer-input" style="overflow-y: auto; height: 44px; min-height: 44px;"
                    x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); submit(); }"
                    :disabled="{{ $isAiServiceAvailable ? 'false' : 'true' }}"></textarea>

                <div class="ai-composer-actions">
                    @if($isTyping)
                        <button type="button" class="ai-stop-button" wire:click="stopGenerating" title="Stop generating">
                            <x-filament::icon icon="heroicon-o-stop" class="h-4 w-4" />
                        </button>
                    @endif

                    <button type="submit" class="ai-send-button" wire:loading.attr="disabled"
                        x-bind:disabled="{{ $isAiServiceAvailable ? 'false' : 'true' }} || $wire.isTyping || localMessage.trim() === ''">
                        <x-filament::icon icon="heroicon-o-arrow-up" class="h-4 w-4" />
                    </button>
                </div>
            </form>
            <p class="ai-composer-hint">
                {{ __('admin.ai.composer_hint') }}
            </p>
        </div>
    </section>
</div>