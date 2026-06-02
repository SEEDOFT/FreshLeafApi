<div class="fl-support-container" x-data="supportChat" x-on:keydown.window="handleEscape($event)"
    x-on:message-sent.window="resetComposer($refs.supportComposerTextarea); setTimeout(() => scrollToBottom(), 50); if (isPhone) { closeDrawer(); }"
    x-on:message-received.window="isUserTyping = false; setTimeout(() => scrollToBottom(), 50)"
    x-on:user-typing.window="isUserTyping = true; clearTimeout(this.typingTimeout); this.typingTimeout = setTimeout(() => { isUserTyping = false; }, 3000); setTimeout(() => scrollToBottom(), 50)"
    x-on:conversation-selected.window="isUserTyping = false; setTimeout(() => scrollToBottom(), 50); history.replaceState(null, '', `${window.location.pathname}?activeConversationId=${$wire.activeConversationId}`); listenToConversation($wire.activeConversationId)"
    x-bind:class="{ 'is-history-hidden': !$wire.showHistory }"
    x-bind:data-drawer-open="drawerOpen"
>
    {{-- Mobile Overlay --}}
    <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm md:hidden"
        x-on:click="closeDrawer()"></div>

    <aside class="fl-support-sidebar">
        <div class="fl-support-sidebar-header">
            <div class="flex items-center justify-between gap-3">
                <h3 class="fl-support-sidebar-title">{{ __('admin.chat.active_conversations', [], 'en') }}</h3>
                @if($this->canCreateSupportTicket())
                    <button type="button" wire:click="createSupportTicket"
                        class="px-3 py-1.5 bg-transparent border border-gray-300 dark:border-[#3f3f46] hover:bg-gray-100 dark:hover:bg-[#27272a] text-gray-700 dark:text-white rounded-lg text-[13px] font-medium transition-all duration-200">
                        <span class="flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-o-plus" class="w-4 h-4" />
                            {{ __('admin.chat.new_ticket', [], 'en') }}
                        </span>
                    </button>
                @endif
            </div>
        </div>

        <div class="flex border-b border-gray-200 dark:border-[#27272a] px-4 py-2 gap-1 bg-gray-50 dark:bg-[#18181b] overflow-x-auto">
            <button wire:click="$set('conversationFilter', 'all')"
                class="fl-filter-btn {{ $conversationFilter === 'all' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                {{ __('admin.chat.filter_all', [], 'en') }}
            </button>
            <button wire:click="$set('conversationFilter', 'support_open')"
                class="fl-filter-btn {{ $conversationFilter === 'support_open' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                {{ __('admin.chat.filter_support_open', [], 'en') }}
            </button>
            <button wire:click="$set('conversationFilter', 'support_resolved')"
                class="fl-filter-btn {{ $conversationFilter === 'support_resolved' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                {{ __('admin.chat.filter_support_resolved', [], 'en') }}
            </button>
            <button wire:click="$set('conversationFilter', 'direct')"
                class="fl-filter-btn {{ $conversationFilter === 'direct' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                {{ __('admin.chat.filter_direct', [], 'en') }}
            </button>
        </div>

        @if(in_array(auth()->user()?->user_type_id, [\App\Models\UserType::ADMIN_ID, \App\Models\UserType::VENDOR_ID], true))
            <div class="flex border-b border-gray-200 dark:border-[#27272a] px-4 py-2 gap-1 bg-gray-50 dark:bg-[#18181b]">
                <button wire:click="$set('activeTab', 'all')"
                    class="fl-filter-btn {{ $activeTab === 'all' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                    All
                </button>
                @if(auth()->user()?->user_type_id === \App\Models\UserType::VENDOR_ID)
                    <button wire:click="$set('activeTab', 'admins')"
                        class="fl-filter-btn {{ $activeTab === 'admins' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                        Admins
                    </button>
                @endif
                <button wire:click="$set('activeTab', 'consumers')"
                    class="fl-filter-btn {{ $activeTab === 'consumers' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                    Consumers
                </button>
                @if(auth()->user()?->user_type_id === \App\Models\UserType::ADMIN_ID)
                    <button wire:click="$set('activeTab', 'vendors')"
                        class="fl-filter-btn {{ $activeTab === 'vendors' ? 'fl-filter-btn--active' : 'fl-filter-btn--inactive' }}">
                        Vendors
                    </button>
                @endif
            </div>
        @endif

        <div class="fl-support-ticket-list">
            @forelse($this->getConversations() as $conversation)
                @php
                    $latestMessage = $conversation->messages->first();
                    $otherParticipant = $conversation->participants->where('user_id', '!=', auth()->id())->first();
                    $title = $otherParticipant ? $otherParticipant->user->fullName : __('admin.chat.unknown_user', [], 'en');
                    $userType = $otherParticipant?->user?->user_type_id;
                    $isSupport = (int) $conversation->conversation_type_id === \App\Models\ConversationType::SUPPORT_ID;
                    $isResolved = (int) $conversation->conversation_status_id === \App\Models\ConversationStatus::CLOSED_ID;
                    $unreadCount = (int) ($conversation->unread_messages_count ?? 0);
                    if ($isSupport) {
                        $title = __('admin.chat.support', [], 'en') . ' - ' . $title;
                    }
                @endphp
                <button wire:click="selectConversation({{ $conversation->id }})"
                    class="fl-support-ticket-item {{ $activeConversationId === $conversation->id ? 'fl-support-ticket-item--active' : '' }}"
                    x-on:click="if (isPhone) { closeDrawer(); }">
                    <div class="flex justify-between items-start mb-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="fl-support-ticket-user">{{ $title }}</span>
                            @if($isSupport)
                                <span class="fl-badge {{ $isResolved ? 'fl-badge--resolved' : 'fl-badge--open' }}">
                                    {{ $isResolved ? __('admin.chat.resolved', [], 'en') : __('admin.chat.open', [], 'en') }}
                                </span>
                            @else
                                <span class="fl-badge fl-badge--direct">
                                    {{ __('admin.chat.direct', [], 'en') }}
                                </span>
                            @endif
                            @if(auth()->user()?->user_type_id === \App\Models\UserType::ADMIN_ID && $userType)
                                @if($userType === \App\Models\UserType::VENDOR_ID)
                                    <span class="fl-badge fl-badge--vendor">Vendor</span>
                                @elseif($userType === \App\Models\UserType::CONSUMER_ID)
                                    <span class="fl-badge fl-badge--consumer">Consumer</span>
                                @endif
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($unreadCount > 0)
                                <span class="fl-unread-badge">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                            <span class="fl-support-ticket-date">{{ $conversation->updated_at->diffForHumans(short: true) }}</span>
                        </div>
                    </div>
                    <p class="fl-support-ticket-excerpt">
                        {{ $latestMessage?->content ?: ($latestMessage?->file_path ? __('admin.chat.file_attached', [], 'en') : __('admin.chat.no_messages_yet', [], 'en')) }}
                    </p>
                </button>
            @empty
                <div class="fl-support-sidebar-empty">{{ __('admin.chat.no_conversations', [], 'en') }}</div>
            @endforelse
        </div>
    </aside>

    <main class="fl-support-main">
        @if($activeConversationId)
            @php
                $activeConversation = \App\Models\Conversation::with(['participants.user'])->find($activeConversationId);
                $otherParticipant = $activeConversation->participants->where('user_id', '!=', auth()->id())->first();
                $title = $otherParticipant ? $otherParticipant->user->fullName : __('admin.chat.unknown_user', [], 'en');
                $isSupport = (int) $activeConversation->conversation_type_id === \App\Models\ConversationType::SUPPORT_ID;
                $isResolved = (int) $activeConversation->conversation_status_id === \App\Models\ConversationStatus::CLOSED_ID;
                if ($isSupport) {
                    $title = __('admin.chat.support', [], 'en') . ' - ' . $title;
                }
            @endphp
            <header class="border-b border-gray-200 dark:border-[#27272a] px-6 py-4 flex items-center justify-between bg-white dark:bg-[#18181b]">
                <div class="flex items-center gap-3">
                    <button type="button" class="md:hidden p-2 -ml-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/10 transition-colors" x-on:click="toggleDrawer()">
                        <x-filament::icon icon="heroicon-o-bars-3" class="h-6 w-6" />
                    </button>
                    <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-[#dcfce7] flex items-center justify-center text-emerald-600 dark:text-[#16a34a] font-bold text-sm">
                        @if($otherParticipant)
                            {{ mb_substr($otherParticipant->user->first_name, 0, 1) }}{{ mb_substr($otherParticipant->user->last_name, 0, 1) }}
                        @else
                            ?
                        @endif
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white leading-tight">{{ $title }}</h2>
                            @if($isSupport)
                                <span class="fl-badge {{ $isResolved ? 'fl-badge--resolved' : 'fl-badge--open' }}">
                                    {{ $isResolved ? __('admin.chat.resolved', [], 'en') : __('admin.chat.open', [], 'en') }}
                                </span>
                            @else
                                <span class="fl-badge fl-badge--direct">
                                    {{ __('admin.chat.direct', [], 'en') }}
                                </span>
                            @endif
                            @if(auth()->user()?->user_type_id === \App\Models\UserType::ADMIN_ID && $otherParticipant?->user?->user_type_id)
                                @if($otherParticipant->user->user_type_id === \App\Models\UserType::VENDOR_ID)
                                    <span class="fl-badge fl-badge--vendor">Vendor</span>
                                @elseif($otherParticipant->user->user_type_id === \App\Models\UserType::CONSUMER_ID)
                                    <span class="fl-badge fl-badge--consumer">Consumer</span>
                                @endif
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ __('admin.chat.last_active', [], 'en') }}: {{ $activeConversation->updated_at->diffForHumans() }}</p>
                    </div>
                </div>

                @if($this->canResolveActiveConversation())
                    <x-filament::button color="success" size="sm" icon="heroicon-o-check-circle"
                        wire:click="resolveConversation({{ $activeConversationId }})" wire:confirm="{{ __('admin.chat.confirm_resolve', [], 'en') }}">
                        {{ __('admin.chat.resolve', [], 'en') }}
                    </x-filament::button>
                @endif
            </header>

            <div class="fl-support-thread" id="support-thread">
                @foreach($this->getActiveMessages() as $msg)
                    @if($msg->sender_id === auth()->id())
                        {{-- Admin (You) message, aligned right --}}
                        <div class="flex flex-col items-end gap-1 mb-6">
                            <div class="flex items-center justify-end gap-2 mb-1 w-full pr-12">
                                <span class="text-[10px] text-gray-500">{{ $msg->created_at->format('H:i') }}</span>
                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase">{{ __('admin.chat.you', [], 'en') }}</span>
                                <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-[#27272a] border border-gray-300 dark:border-[#3f3f46] flex items-center justify-center text-[10px] font-bold text-gray-600 dark:text-gray-300">
                                    {{ substr(auth()->user()?->name ?? 'U', 0, 2) }}
                                </div>
                            </div>
                            <div class="w-fit bg-emerald-600 dark:bg-[#16a34a] text-white px-5 py-3 rounded-xl rounded-tr-sm max-w-[75%] mr-[3.25rem] text-[15px] font-medium leading-relaxed break-words shadow-sm">
                                @if($msg->file_path)
                                    @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $msg->file_path))
                                        <div x-data="{ isZoomed: false }">
                                            <img src="{{ Storage::url($msg->file_path) }}" 
                                                 x-on:click="isZoomed = true"
                                                 class="fl-chat-image-attachment" 
                                                 alt="Attachment">
                                            
                                            <template x-teleport="body">
                                                <div x-show="isZoomed" 
                                                     style="display: none;"
                                                     x-transition:enter="transition ease-out duration-300"
                                                     x-transition:enter-start="opacity-0"
                                                     x-transition:enter-end="opacity-100"
                                                     x-transition:leave="transition ease-in duration-200"
                                                     x-transition:leave-start="opacity-100"
                                                     x-transition:leave-end="opacity-0"
                                                     class="fl-chat-lightbox-overlay"
                                                     x-on:click="isZoomed = false"
                                                     x-on:keydown.escape.window="isZoomed = false">
                                                    
                                                    <img src="{{ Storage::url($msg->file_path) }}" 
                                                         class="fl-chat-lightbox-image" 
                                                         x-on:click.stop="isZoomed = false"
                                                         alt="Attachment Zoomed">
                                                    
                                                    <button type="button" 
                                                            class="fl-chat-lightbox-close" 
                                                            x-on:click="isZoomed = false">
                                                        <x-filament::icon icon="heroicon-o-x-mark" class="w-6 h-6" />
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    @else
                                        <a href="{{ Storage::url($msg->file_path) }}" target="_blank" class="flex items-center gap-2 p-2 mb-2 rounded bg-black/10 hover:bg-black/20 transition-colors text-white">
                                            <x-filament::icon icon="heroicon-o-document" class="w-5 h-5 opacity-70" />
                                            <span>{{ __('admin.chat.download_attachment', [], 'en') }}</span>
                                        </a>
                                    @endif
                                @endif
                                @if($msg->content)
                                    <p>{{ $msg->content }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- User (Other) message, aligned left --}}
                        <div class="flex items-start gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-[#dcfce7] flex items-center justify-center text-emerald-600 dark:text-[#16a34a] font-bold text-xs flex-shrink-0 mt-1">
                                {{ mb_substr($msg->sender->first_name ?? 'U', 0, 1) }}{{ mb_substr($msg->sender->last_name ?? '', 0, 1) }}
                            </div>
                            <div class="flex flex-col gap-1 w-full items-start">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $msg->sender->fullName }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                                <div class="w-fit bg-white dark:bg-[#27272a] text-gray-800 dark:text-white px-5 py-4 rounded-xl rounded-tl-sm max-w-[85%] text-[15px] leading-relaxed shadow-sm border border-gray-200 dark:border-[#3f3f46] break-words">
                                    @if($msg->file_path)
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $msg->file_path))
                                            <div x-data="{ isZoomed: false }">
                                                <img src="{{ Storage::url($msg->file_path) }}" 
                                                     x-on:click="isZoomed = true"
                                                     class="fl-chat-image-attachment" 
                                                     alt="Attachment">
                                                
                                                <template x-teleport="body">
                                                    <div x-show="isZoomed" 
                                                         style="display: none;"
                                                         x-transition:enter="transition ease-out duration-300"
                                                         x-transition:enter-start="opacity-0"
                                                         x-transition:enter-end="opacity-100"
                                                         x-transition:leave="transition ease-in duration-200"
                                                         x-transition:leave-start="opacity-100"
                                                         x-transition:leave-end="opacity-0"
                                                         class="fl-chat-lightbox-overlay"
                                                         x-on:click="isZoomed = false"
                                                         x-on:keydown.escape.window="isZoomed = false">
                                                        
                                                        <img src="{{ Storage::url($msg->file_path) }}" 
                                                             class="fl-chat-lightbox-image" 
                                                             x-on:click.stop="isZoomed = false"
                                                             alt="Attachment Zoomed">
                                                        
                                                        <button type="button" 
                                                                class="fl-chat-lightbox-close" 
                                                                x-on:click="isZoomed = false">
                                                            <x-filament::icon icon="heroicon-o-x-mark" class="w-6 h-6" />
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        @else
                                            <a href="{{ Storage::url($msg->file_path) }}" target="_blank" class="flex items-center gap-2 p-2 mb-2 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition-colors text-gray-800 dark:text-white">
                                                <x-filament::icon icon="heroicon-o-document" class="w-5 h-5 opacity-70" />
                                                <span>{{ __('admin.chat.download_attachment', [], 'en') }}</span>
                                            </a>
                                        @endif
                                    @endif
                                    @if($msg->content)
                                        <p>{{ $msg->content }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                <div x-show="isUserTyping" style="display: none;" class="flex items-start gap-3 mb-6">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-[#dcfce7] flex items-center justify-center text-emerald-600 dark:text-[#16a34a] font-bold text-xs flex-shrink-0 mt-1 animate-pulse">
                        {{ mb_substr($otherParticipant?->user?->first_name ?? 'U', 0, 1) }}{{ mb_substr($otherParticipant?->user?->last_name ?? '', 0, 1) }}
                    </div>
                    <div class="flex flex-col gap-1 w-full items-start">
                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $title }}</span>
                        <div class="w-fit bg-white dark:bg-[#27272a] text-gray-800 dark:text-white px-5 py-3 rounded-xl rounded-tl-sm max-w-[75%] text-[15px] border border-gray-200 dark:border-[#3f3f46] shadow-sm">
                            <div class="flex items-center gap-1.5 h-5">
                                <span class="fl-typing-dot"></span>
                                <span class="fl-typing-dot" style="animation-delay: 0.2s"></span>
                                <span class="fl-typing-dot" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($this->canSendInActiveConversation())
            <footer class="fl-support-footer">
                @if ($file)
                    <div class="fl-chat-attachment-preview">
                        <div class="fl-chat-attachment-name">
                            <x-filament::icon icon="heroicon-o-paper-clip" class="w-4 h-4 text-gray-500" />
                            <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                        </div>
                        <button type="button" wire:click="$set('file', null)" class="fl-chat-remove-btn">
                            <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                        </button>
                    </div>
                @endif
                <form
                    x-on:submit.prevent="submitComposer($refs.supportComposerTextarea, @js((bool) $file))"
                    class="fl-support-composer"
                >
                    <div class="fl-support-composer-file-label">
                        <label class="cursor-pointer text-gray-400 hover:text-primary-500 transition-colors p-2 block">
                            <x-filament::icon icon="heroicon-o-paper-clip" class="w-6 h-6" />
                            <input type="file" wire:model="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </label>
                        <div wire:loading wire:target="file" class="absolute top-0 right-0 -mt-2 -mr-2">
                            <span class="flex h-3 w-3 relative animate-ping">
                              <span class="inline-flex rounded-full h-3 w-3 bg-primary-500"></span>
                            </span>
                        </div>
                    </div>
                    <div class="fl-support-composer-input-wrap">
                        <textarea
                            x-ref="supportComposerTextarea"
                            x-model="composerMessage"
                            x-init="resizeTextarea($el)"
                            x-on:input="resizeTextarea($el); queueTyping()"
                            x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); submitComposer($el, @js((bool) $file)); }"
                            x-bind:style="{ height: composerTextareaHeight, overflowY: composerTextareaOverflowY }"
                            placeholder="{{ __('admin.chat.type_reply', [], 'en') }}"
                            class="flex-1 bg-transparent border-none focus:ring-0 text-gray-900 dark:text-white text-[14px] resize-none px-3 py-2 outline-none" rows="1"
                            autofocus></textarea>
                    </div>
                    <button type="submit" class="w-10 h-10 self-end mb-0.5 mr-0.5 flex items-center justify-center bg-gray-100 dark:bg-[#27272a] hover:bg-gray-200 dark:hover:bg-[#3f3f46] text-gray-600 dark:text-white rounded-lg transition-colors flex-shrink-0 disabled:opacity-50 disabled:grayscale" wire:loading.attr="disabled" wire:target="sendMessage, file">
                        <x-filament::icon icon="heroicon-o-paper-airplane" class="h-5 w-5" />
                    </button>
                </form>
            </footer>
            @else
                <footer class="fl-support-footer">
                    <div class="max-w-4xl mx-auto rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('admin.chat.resolved_read_only', [], 'en') }}
                    </div>
                </footer>
            @endif
        @else
            <div class="fl-support-empty-state">
                <header class="fl-support-header absolute top-0 left-0 right-0 border-b-0 bg-transparent flex justify-start pt-4 pl-4">
                    <button type="button" class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/10 transition-colors" x-on:click="toggleDrawer()">
                        <x-filament::icon icon="heroicon-o-bars-3" class="h-6 w-6" />
                    </button>
                    <button type="button" class="hidden md:block p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/10 transition-colors" x-on:click="toggleDrawer()">
                        <x-filament::icon icon="heroicon-o-bars-3-bottom-left" class="h-6 w-6" />
                    </button>
                </header>
                <div class="fl-support-empty-icon-wrapper">
                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-12 h-12" />
                </div>
                <h3 class="fl-support-empty-title">{{ __('admin.chat.no_conversation_selected_title', [], 'en') }}</h3>
                <p class="fl-support-empty-desc">{{ __('admin.chat.no_conversation_selected_desc', [], 'en') }}</p>
                @if($this->canCreateSupportTicket())
                    <button type="button" wire:click="createSupportTicket"
                        class="fl-cta-btn fl-cta-btn--lg">
                        {{ __('admin.chat.new_ticket', [], 'en') }}
                    </button>
                @endif
            </div>
        @endif
    </main>
</div>
