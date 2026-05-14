<div class="fl-support-container" x-data="{
        isUserTyping: false,
        typingTimeout: null,
        currentTicketId: null,
        pollInterval: null,
        scrollToBottom() {
            const container = document.getElementById('support-thread');
            if (container) container.scrollTop = container.scrollHeight;
        },
        initEcho() {
            if (typeof window.Echo === 'undefined') {
                setTimeout(() => this.initEcho(), 2000);
                return;
            }
            const adminChannel = window.Echo.private('support.admin');
            adminChannel.listen('.NewSupportTicket', (e) => { $wire.$refresh(); });
            adminChannel.listen('.SupportMessageSent', (e) => { $wire.$refresh(); });
            adminChannel.listen('.SupportTyping', (e) => {
                if (e.sender_type === 'user') {
                    this.isUserTyping = true;
                    clearTimeout(this.typingTimeout);
                    this.typingTimeout = setTimeout(() => { this.isUserTyping = false; }, 3000);
                    setTimeout(() => this.scrollToBottom(), 50);
                }
            });
            this.listenToTicket($wire.activeTicketId);
        },
        listenToTicket(ticketId) {
            if (!ticketId) return;
            if (this.currentTicketId) window.Echo.leave('support.ticket.' + this.currentTicketId);
            this.currentTicketId = ticketId;
            const ticketChannel = window.Echo.private('support.ticket.' + ticketId);
            ticketChannel.listen('.SupportMessageSent', (e) => { $wire.handleIncomingMessage(e); });
        },
        startPolling() {
            if (this.pollInterval) return;
            this.pollInterval = setInterval(async () => {
                await $wire.$refresh();
            }, 5000);
        },
        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        }
    }" x-init="scrollToBottom(); initEcho(); startPolling();"
    x-on:message-sent.window="setTimeout(() => scrollToBottom(), 50)"
    x-on:message-received.window="isUserTyping = false; setTimeout(() => scrollToBottom(), 50)"
    x-on:user-typing.window="isUserTyping = true; clearTimeout(this.typingTimeout); this.typingTimeout = setTimeout(() => { isUserTyping = false; }, 3000); setTimeout(() => scrollToBottom(), 50)"
    x-on:ticket-selected.window="isUserTyping = false; setTimeout(() => scrollToBottom(), 50); listenToTicket($wire.activeTicketId)"
>
    <aside class="fl-support-sidebar">
        <div class="fl-support-sidebar-header">
            <h3 class="fl-support-sidebar-title">{{ __('admin.support.active_tickets') }}</h3>
        </div>

        <div class="fl-support-ticket-list">
            @forelse($this->getTickets() as $ticket)
                @php $latestMessage = $ticket->latestMessage; @endphp
                <button wire:click="selectTicket({{ $ticket->id }})"
                    class="fl-support-ticket-item {{ $activeTicketId === $ticket->id ? 'fl-support-ticket-item--active' : '' }}">
                    <div class="flex justify-between items-start mb-1">
                        <span class="fl-support-ticket-user">{{ $ticket->user->fullName }}</span>
                        <span class="fl-support-ticket-date">{{ $ticket->updated_at->diffForHumans(short: true) }}</span>
                    </div>
                    <p class="fl-support-ticket-excerpt">
                        {{ $latestMessage?->message ?: ($latestMessage?->file_path ? 'File attached' : 'No messages yet') }}
                    </p>
                </button>
            @empty
                <div class="fl-support-sidebar-empty">{{ __('admin.support.no_tickets') }}</div>
            @endforelse
        </div>
    </aside>

    <main class="fl-support-main">
        @if($activeTicketId)
            @php $activeTicket = \App\Models\SupportTicket::find($activeTicketId); @endphp
            <header class="fl-support-header">
                <div class="fl-support-header-user">
                    <div class="fl-support-header-avatar">
                        {{ substr($activeTicket->user->first_name, 0, 1) }}{{ substr($activeTicket->user->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="fl-support-header-name">{{ $activeTicket->user->fullName }}</h2>
                        <p class="fl-support-header-status">{{ __('admin.support.last_active') }}: {{ $activeTicket->updated_at->diffForHumans() }}</p>
                    </div>
                </div>

                <x-filament::button color="success" size="sm" icon="heroicon-o-check-circle"
                    wire:click="resolveTicket({{ $activeTicketId }})" wire:confirm="Mark this ticket as resolved?">
                    {{ __('admin.support.resolve') }}
                </x-filament::button>
            </header>

            <div class="fl-support-thread" id="support-thread">
                @foreach($this->getActiveMessages() as $msg)
                    <div class="fl-support-message-row {{ $msg->sender_type === 'admin' ? 'fl-support-message-row--admin' : '' }}">
                        <div class="fl-support-message-content">
                            <div class="fl-support-message-info">
                                <span class="fl-support-message-author">
                                    {{ $msg->sender_type === 'admin' ? 'Admin' : $activeTicket->user->fullName }}
                                </span>
                                <span class="fl-support-message-time">{{ $msg->created_at->format('H:i') }}</span>
                            </div>
                            <div class="fl-chat-bubble {{ $msg->sender_type === 'admin' ? 'fl-chat-bubble--admin' : 'fl-chat-bubble--user' }}">
                                @if($msg->file_path)
                                    @if(preg_match('/\.(jpg|jpeg|png)$/i', $msg->file_path))
                                        <a href="{{ Storage::url($msg->file_path) }}" target="_blank">
                                            <img src="{{ Storage::url($msg->file_path) }}" class="rounded-lg mb-2 max-w-full h-auto max-h-48 object-cover border border-black/10 dark:border-white/10" alt="Attachment">
                                        </a>
                                    @else
                                    <a href="{{ Storage::url($msg->file_path) }}" target="_blank" class="fl-chat-attachment">
                                            <x-filament::icon icon="heroicon-o-document" class="fl-chat-attachment__icon" />
                                            <span class="fl-chat-attachment__text">Download Attachment</span>
                                        </a>
                                    @endif
                                @endif
                                @if($msg->message)
                                    <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div x-show="isUserTyping" style="display: none;" class="fl-support-message-row">
                    <div class="fl-support-message-content">
                        <div class="fl-support-message-info">
                            <span class="fl-support-message-author">{{ $activeTicket->user->fullName }}</span>
                        </div>
                        <div class="fl-chat-bubble fl-chat-bubble--user">
                            <div class="flex items-center gap-1.5 h-5">
                                <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce"></span>
                                <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                                <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                <form wire:submit.prevent="sendMessage" class="fl-support-composer">
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
                    <div class="fl-support-composer-input">
                        <x-filament::input wire:model="message" wire:keyup.debounce.500ms="sendTyping"
                            placeholder="{{ __('admin.support.type_reply') }}" autocomplete="off" autofocus />
                    </div>
                    <x-filament::button type="submit" icon="heroicon-o-paper-airplane" class="fl-support-composer-btn">
                        {{ __('admin.support.send') }}
                    </x-filament::button>
                </form>
            </footer>
        @else
            <div class="fl-support-empty-state">
                <div class="fl-support-empty-icon-wrapper">
                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-12 h-12" />
                </div>
                <h3 class="fl-support-empty-title">{{ __('admin.support.no_ticket_selected_title') }}</h3>
                <p class="fl-support-empty-desc">{{ __('admin.support.no_ticket_selected_desc') }}</p>
            </div>
        @endif
    </main>
</div>