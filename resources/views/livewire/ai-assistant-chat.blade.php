<div class="ai-chat-shell" x-data="aiAssistantChat" x-on:keydown.window="handleEscape($event)"
    x-on:message-sent.window="resetComposer($refs.aiComposerTextarea); setTimeout(() => scrollToBottom(), 50); if (isPhone) { closeDrawer(); }"
    x-on:freshleaf-realtime-status.window="$wire.handleRealtimeStatus(($event.detail && $event.detail.state) ? $event.detail.state : null, ($event.detail && $event.detail.reason) ? $event.detail.reason : null)"
    x-bind:class="{ 'ai-chat-shell': true, 'is-history-hidden': !$wire.showHistory }"
    x-bind:data-drawer-open="drawerOpen">

    {{-- Mobile Overlay --}}
    <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm md:hidden"
        x-on:click="closeDrawer()"></div>

    <aside class="ai-chat-sidebar">
        <div class="ai-chat-sidebar-head flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Conversations</h3>
            <button type="button" class="ai-new-chat-btn" wire:click="startNewChat" wire:loading.attr="disabled"
                wire:target="startNewChat" x-on:click="if (isPhone) { closeDrawer(); }">
                <x-filament::icon icon="heroicon-o-plus" class="h-4 w-4" />
                <span>New</span>
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
                <button type="button" x-on:click="toggleDrawer()" class="md:hidden">
                    <x-filament::icon icon="heroicon-o-bars-3" class="h-5 w-5" />
                </button>
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-[#dcfce7] flex items-center justify-center text-emerald-600 dark:text-[#16a34a]">
                        <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white leading-tight">FreshLeaf Assistant</h2>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Powered by AI · Your operations, simplified</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-[#dcfce7] rounded-full px-3 py-1">
                <span class="w-2 h-2 rounded-full bg-[#16a34a]"></span>
                <span class="text-[11px] font-bold text-[#16a34a] tracking-wide">Online</span>
            </div>
        </header>

        <div class="ai-chat-thread" id="ai-thread-scroll">
            @foreach($messages as $messageItem)
                @if($messageItem['role'] === 'user')
                    <div class="flex flex-col items-end gap-1 mb-6">
                        <div class="flex items-center justify-end gap-2 mb-1 w-full pr-12">
                            <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase">You</span>
                            <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-[#27272a] border border-gray-300 dark:border-[#3f3f46] flex items-center justify-center text-[10px] font-bold text-gray-600 dark:text-gray-300">
                                {{ substr(auth()->user()?->name ?? 'U', 0, 2) }}
                            </div>
                        </div>
                        <div class="bg-emerald-600 dark:bg-[#16a34a] text-white px-5 py-3 rounded-xl rounded-tr-sm max-w-[75%] mr-[3.25rem] text-[15px] font-medium leading-relaxed shadow-sm">
                            <p>{{ $messageItem['content'] }}</p>
                        </div>
                    </div>
                @elseif($messageItem['role'] === 'assistant')
                    @if(
                        ($messageItem['content'] ?? '') !== '' ||
                        ($messageItem['status'] ?? 'done') === 'failed'
                      )
                        <div class="flex items-start gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-[#dcfce7] flex items-center justify-center text-emerald-600 dark:text-[#16a34a] flex-shrink-0 mt-1">
                                <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                            </div>
                            <div class="flex flex-col gap-1 w-full">
                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">FreshLeaf Assistant</span>
                                <div class="bg-white dark:bg-[#27272a] text-gray-800 dark:text-white px-5 py-4 rounded-xl rounded-tl-sm max-w-[85%] text-[15px] leading-relaxed shadow-sm border border-gray-200 dark:border-transparent">
                                    <div class="freshleaf-markdown {{ ($messageItem['status'] ?? 'done') === 'failed' ? 'text-red-500 dark:text-red-400' : '' }}">
                                        {!! $this->renderAssistantMessage($messageItem['content']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach

            @if($isTyping)
                <div class="flex items-start gap-3 mb-6">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-[#dcfce7] flex items-center justify-center text-emerald-600 dark:text-[#16a34a] flex-shrink-0 mt-1 animate-pulse">
                        <x-filament::icon icon="heroicon-o-sparkles" class="h-4 w-4" />
                    </div>
                    <div class="flex flex-col gap-1 w-full">
                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">FreshLeaf Assistant</span>
                        <div class="bg-white dark:bg-[#27272a] text-gray-800 dark:text-white px-5 py-3 rounded-xl rounded-tl-sm max-w-[75%] text-[15px] flex items-center gap-2 border border-gray-200 dark:border-transparent shadow-sm">
                             <svg class="animate-spin h-4 w-4 text-emerald-600 dark:text-[#16a34a]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                               <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                               <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                             </svg>
                             <span class="text-gray-600 dark:text-gray-300">{{ __('admin.ai.thinking') }}...</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Redesigned Suggestion Chips --}}
        <div class="suggestions flex flex-wrap gap-2 px-6 py-4" wire:ignore>
            <button type="button" class="px-4 py-1.5 rounded-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-[13px] hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" x-on:click="composerMessage = 'View low stock items'; submitComposer($refs.aiComposerTextarea)">
                View low stock items
            </button>
            <button type="button" class="px-4 py-1.5 rounded-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-[13px] hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" x-on:click="composerMessage = 'Recent orders today'; submitComposer($refs.aiComposerTextarea)">
                Recent orders today
            </button>
            <button type="button" class="px-4 py-1.5 rounded-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-[13px] hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" x-on:click="composerMessage = 'Payout summary'; submitComposer($refs.aiComposerTextarea)">
                Payout summary
            </button>
        </div>

        <footer class="px-6 py-4 border-t border-gray-200 dark:border-[#27272a] bg-gray-50 dark:bg-transparent" wire:ignore>
            <form
                x-on:submit.prevent="submitComposer($refs.aiComposerTextarea)"
                class="flex items-end gap-3 bg-white dark:bg-[#18181b] border border-gray-300 dark:border-[#3f3f46] rounded-xl p-2 focus-within:border-gray-400 dark:focus-within:border-[#52525b] transition-colors shadow-sm"
            >
                <textarea
                    x-ref="aiComposerTextarea"
                    x-model="composerMessage"
                    x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); submitComposer($el); }"
                    x-init="resizeTextarea($el)"
                    x-on:input="resizeTextarea($el)"
                    x-bind:style="{ height: composerTextareaHeight, overflowY: composerTextareaOverflowY }"
                    placeholder="{{ $isAiServiceAvailable ? 'Ask anything about your operations...' : __('admin.ai.service_unavailable') }}"
                    class="flex-1 bg-transparent border-none focus:ring-0 text-gray-900 dark:text-white text-[14px] resize-none px-3 py-2 outline-none"
                    rows="1"
                ></textarea>

                <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-[#27272a] text-gray-600 dark:text-white hover:bg-gray-200 dark:hover:bg-[#3f3f46] transition-colors mb-0.5 mr-0.5">
                    <x-filament::icon icon="heroicon-o-arrow-up" class="h-5 w-5" />
                </button>
            </form>
        </footer>
    </section>
</div>
