<div class="ai-chat-shell" x-data="aiAssistantChat" x-on:keydown.window="handleEscape($event)"
    x-on:message-sent.window="resetComposer($refs.aiComposerTextarea); setTimeout(() => scrollToBottom(), 50); if (isPhone) { closeDrawer(); }"
    x-on:freshleaf-realtime-status.window="$wire.handleRealtimeStatus(($event.detail && $event.detail.state) ? $event.detail.state : null, ($event.detail && $event.detail.reason) ? $event.detail.reason : null)"
    x-bind:class="{ 'ai-chat-shell': true, 'is-history-hidden': !$wire.showHistory }">

    <aside class="ai-chat-sidebar">
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
                        <span class="ai-session-title">{{ $session['title'] }}</span>
                        <span class="ai-session-meta">{{ $session['updated_at_human'] }}</span>
                    </button>

                    <button type="button" class="ai-session-delete-btn" wire:click="deleteSession({{ $session['id'] }})"
                        wire:confirm="{{ __('admin.ai.delete_confirm') }}" aria-label="{{ __('admin.ai.delete_chat') }}">
                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                    </button>
                </div>
            @empty
                <div class="p-4 text-center text-sm text-gray-500">{{ __('admin.ai.no_conversations') }}</div>
            @endforelse
        </div>
    </aside>

    <section class="ai-chat-main">
        <header class="ai-chat-header">
            <div class="ai-chat-header-title">
                <button type="button" x-on:click="toggleDrawer()">
                    <x-filament::icon icon="heroicon-o-bars-3" class="h-5 w-5" />
                </button>
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-sparkles" class="h-6 w-6 text-emerald-500" />
                    <h2>{{ __('admin.ai.freshleaf_assistant') }}</h2>
                </div>
            </div>

            @php
                $statusClass = $isAiServiceAvailable
                    ? ($isRealtimeConnected
                        ? 'ai-chat-status-pill--connected'
                        : 'ai-chat-status-pill--fallback'
                      )
                    : 'ai-chat-status-pill--error';
            @endphp
            <span class="ai-chat-status-pill {{ $statusClass }}">
                {{ $isAiServiceAvailable
                    ? ($isRealtimeConnected
                        ? __('admin.ai.realtime_connected')
                        : __('admin.ai.fallback_sync_mode')
                      )
                    : __('admin.ai.service_unavailable')
                }}
            </span>
        </header>

        <div class="ai-chat-thread" id="ai-thread-scroll">
            @foreach($messages as $messageItem)
                @if($messageItem['role'] === 'user')
                    <div class="ai-message-row ai-message-row--user">
                        <article class="ai-message-bubble ai-message-bubble--user">
                            <p>{{ $messageItem['content'] }}</p>
                        </article>
                    </div>
                @elseif($messageItem['role'] === 'assistant')
                    @if(
                        ($messageItem['content'] ?? '') !== '' ||
                        ($messageItem['status'] ?? 'done') === 'failed'
                      )
                        <div class="ai-message-row">
                            <article class="ai-message-bubble ai-message-bubble--assistant {{ ($messageItem['status'] ?? 'done') === 'failed' ? 'is-error' : '' }}">
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
                <div class="ai-message-row">
                    <article class="ai-message-bubble ai-message-bubble--assistant">
                        <div class="flex items-center gap-2 text-sm opacity-60">
                             <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4 animate-pulse" />
                             <span>{{ __('admin.ai.thinking') }}</span>
                        </div>
                    </article>
                </div>
            @endif
        </div>

        <footer class="ai-chat-composer" wire:ignore>
            <form
                x-on:submit.prevent="submitComposer($refs.aiComposerTextarea)"
                class="flex items-center gap-3"
            >
                <textarea
                    x-ref="aiComposerTextarea"
                    x-model="composerMessage"
                    x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); submitComposer($el); }"
                    x-init="resizeTextarea($el)"
                    x-on:input="resizeTextarea($el)"
                    x-bind:style="{ height: composerTextareaHeight, overflowY: composerTextareaOverflowY }"
                    placeholder="{{ $isAiServiceAvailable ? __('admin.ai.composer_placeholder') : __('admin.ai.service_unavailable') }}"
                    class="ai-composer-input"
                    rows="1"
                ></textarea>

                <button type="submit" class="ai-send-button">
                    <x-filament::icon icon="heroicon-o-arrow-up" class="h-5 w-5" />
                </button>
            </form>
        </footer>
    </section>
</div>
