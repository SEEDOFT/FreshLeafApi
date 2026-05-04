<x-filament-panels::page>
    <div 
        wire:poll.5s="getTickets"
        class="flex h-[calc(100vh-14rem)] overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-white/5"
        x-data="{
            isUserTyping: false,
            typingTimeout: null,
            currentTicketId: null,
            scrollToBottom() {
                const container = document.getElementById('support-thread');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            },
            initEcho() {
                console.log('[SupportChat] Initializing Echo...');
                
                if (typeof window.Echo === 'undefined') {
                    console.error('[SupportChat] Echo NOT available! Waiting...');
                    setTimeout(() => this.initEcho(), 2000);
                    return;
                }
                
                console.log('[SupportChat] Echo is available');
                
                // Check connection status
                const connection = window.Echo?.connector?.pusher?.connection;
                console.log('[SupportChat] Echo connection state:', connection?.state);
                
                // Listen for connection events
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    console.log('[SupportChat] ✅ Reverb connected!');
                });
                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    console.warn('[SupportChat] ❌ Reverb disconnected');
                });
                window.Echo.connector.pusher.connection.bind('error', (err) => {
                    console.error('[SupportChat] ❌ Reverb error:', err);
                });
                
                // Subscribe to admin channel
                console.log('[SupportChat] Subscribing to support.admin channel...');
                
                const adminChannel = window.Echo.private('support.admin');
                
                adminChannel.listen('.NewSupportTicket', (e) => {
                    console.log('[SupportChat] ✅ NewSupportTicket received:', e);
                    $wire.$refresh();
                });
                
                adminChannel.listen('.SupportMessageSent', (e) => {
                    console.log('[SupportChat] ✅ SupportMessageSent received:', e);
                    $wire.$refresh();
                });
                
                adminChannel.listen('.SupportTyping', (e) => {
                    console.log('[SupportChat] ✅ SupportTyping received:', e);
                    if (e.sender_type === 'user') {
                        this.isUserTyping = true;
                        clearTimeout(this.typingTimeout);
                        this.typingTimeout = setTimeout(() => { this.isUserTyping = false; }, 3000);
                        setTimeout(() => this.scrollToBottom(), 50);
                    }
                });
                
                adminChannel.subscribed(() => {
                    console.log('[SupportChat] ✅ Successfully subscribed to support.admin');
                });
                
                adminChannel.error((err) => {
                    console.error('[SupportChat] ❌ Channel subscription error:', err);
                });
                
                // Also listen to specific ticket channel if active
                this.listenToTicket($wire.activeTicketId);
            },
            listenToTicket(ticketId) {
                if (!ticketId) return;
                
                if (this.currentTicketId) {
                    window.Echo.leave('support.ticket.' + this.currentTicketId);
                }
                
                this.currentTicketId = ticketId;
                console.log('[SupportChat] Subscribing to ticket channel:', ticketId);
                
                const ticketChannel = window.Echo.private('support.ticket.' + ticketId);
                
                ticketChannel.listen('.SupportMessageSent', (e) => {
                    console.log('[SupportChat] ✅ Ticket message received:', e);
                    $wire.handleIncomingMessage(e);
                });
                
                ticketChannel.listen('.SupportTyping', (e) => {
                    console.log('[SupportChat] ✅ Ticket typing received:', e);
                });
                
                ticketChannel.subscribed(() => {
                    console.log('[SupportChat] ✅ Subscribed to support.ticket.' + ticketId);
                });
            }
        }"
        x-init="scrollToBottom(); initEcho()"
        x-on:message-sent.window="setTimeout(() => scrollToBottom(), 50)"
        x-on:message-received.window="isUserTyping = false; setTimeout(() => scrollToBottom(), 50)"
        x-on:user-typing.window="isUserTyping = true; clearTimeout(this.typingTimeout); this.typingTimeout = setTimeout(() => { isUserTyping = false; }, 3000); setTimeout(() => scrollToBottom(), 50)"
        x-on:ticket-selected.window="isUserTyping = false; setTimeout(() => scrollToBottom(), 50); listenToTicket($wire.activeTicketId)"
    >
        <!-- Sidebar: Ticket List -->
        <aside class="w-80 flex-shrink-0 border-r border-gray-200 dark:border-white/5 flex flex-col">
            <div class="p-4 border-bottom border-gray-200 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('admin.support.active_tickets') }}
                </h3>
            </div>
            
            <div class="flex-1 overflow-y-auto">
                @forelse($this->getTickets() as $ticket)
                    @php $latestMessage = $ticket->latestMessage; @endphp
                    <button
                        wire:click="selectTicket({{ $ticket->id }})"
                        class="w-full text-left p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors border-b border-gray-100 dark:border-white/5 {{ $activeTicketId === $ticket->id ? 'bg-primary-50 dark:bg-primary-500/10' : '' }}"
                    >
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ $ticket->user->fullName }}
                            </span>
                            <span class="text-[10px] text-gray-500">
                                {{ $ticket->updated_at->diffForHumans(short: true) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $latestMessage?->message ?: ($latestMessage?->file_path ? 'File attached' : 'No messages yet') }}
                        </p>
                    </button>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p class="text-sm">{{ __('admin.support.no_tickets') }}</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- Main: Chat Thread -->
        <main class="flex-1 flex flex-col min-w-0 bg-white dark:bg-gray-900">
            @if($activeTicketId)
                @php $activeTicket = \App\Models\SupportTicket::find($activeTicketId); @endphp
                <header class="p-4 border-b border-gray-200 dark:border-white/5 flex justify-between items-center bg-gray-50/50 dark:bg-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold text-xs">
                            {{ substr($activeTicket->user->first_name, 0, 1) }}{{ substr($activeTicket->user->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-white">{{ $activeTicket->user->fullName }}</h2>
                            <p class="text-xs text-gray-500">{{ __('admin.support.last_active') }}: {{ $activeTicket->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    
                    <x-filament::button
                        color="success"
                        size="sm"
                        icon="heroicon-o-check-circle"
                        wire:click="resolveTicket({{ $activeTicketId }})"
                        wire:confirm="Mark this ticket as resolved?"
                    >
                        {{ __('admin.support.resolve') }}
                    </x-filament::button>
                </header>

                <div class="flex-1 overflow-y-auto p-6 space-y-6" id="support-thread">
                    @foreach($this->getActiveMessages() as $msg)
                        <div class="flex {{ $msg->sender_type === 'admin' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%]">
                                <div class="flex items-center gap-2 mb-1 {{ $msg->sender_type === 'admin' ? 'flex-row-reverse' : '' }}">
                                    <span class="text-[10px] font-bold uppercase tracking-widest opacity-50">
                                        {{ $msg->sender_type === 'admin' ? 'Admin' : $activeTicket->user->fullName }}
                                    </span>
                                    <span class="text-[10px] opacity-30">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                                <div class="p-4 rounded-2xl {{ $msg->sender_type === 'admin' ? 'bg-primary-600 text-white rounded-tr-none shadow-md shadow-primary-500/20' : 'bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white rounded-tl-none border border-gray-200 dark:border-white/5' }}">
                                    @if($msg->file_path)
                                        @if(preg_match('/\.(jpg|jpeg|png)$/i', $msg->file_path))
                                            <a href="{{ Storage::url($msg->file_path) }}" target="_blank">
                                                <img src="{{ Storage::url($msg->file_path) }}" class="rounded-lg mb-2 max-w-full h-auto max-h-48 object-cover border border-black/10 dark:border-white/10" alt="Attachment">
                                            </a>
                                        @else
                                            <a href="{{ Storage::url($msg->file_path) }}" target="_blank" class="flex items-center gap-2 p-2 mb-2 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
                                                <x-filament::icon icon="heroicon-o-document" class="w-5 h-5 opacity-70" />
                                                <span class="text-sm font-medium underline truncate">Download Attachment</span>
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

                    <!-- Typing Indicator -->
                    <div x-show="isUserTyping" style="display: none;" class="flex justify-start">
                        <div class="max-w-[70%]">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-widest opacity-50">
                                    {{ $activeTicket->user->fullName }}
                                </span>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-100 dark:bg-white/10 rounded-tl-none border border-gray-200 dark:border-white/5">
                                <div class="flex items-center gap-1.5 h-5">
                                    <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce"></span>
                                    <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                                    <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="p-4 border-t border-gray-200 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                    @if ($file)
                        <div class="mb-3 p-3 bg-gray-100 dark:bg-white/10 rounded-lg flex justify-between items-center text-sm border border-gray-200 dark:border-white/5">
                            <div class="flex items-center gap-2 truncate">
                                <x-filament::icon icon="heroicon-o-paper-clip" class="w-4 h-4 text-gray-500" />
                                <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                            </div>
                            <button type="button" wire:click="$set('file', null)" class="text-danger-500 hover:text-danger-600 ml-2 shrink-0">
                                <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    @endif
                    <form wire:submit.prevent="sendMessage" class="flex gap-4 items-end">
                        <div class="pb-1 shrink-0 relative">
                            <label class="cursor-pointer text-gray-400 hover:text-primary-500 transition-colors p-2 block">
                                <x-filament::icon icon="heroicon-o-paper-clip" class="w-6 h-6" />
                                <input type="file" wire:model="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            </label>
                            <!-- Livewire upload progress -->
                            <div wire:loading wire:target="file" class="absolute top-0 right-0 -mt-2 -mr-2">
                                <span class="flex h-3 w-3 relative">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-primary-500"></span>
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <x-filament::input
                                wire:model="message"
                                wire:keyup.debounce.500ms="sendTyping"
                                placeholder="{{ __('admin.support.type_reply') }}"
                                autocomplete="off"
                                autofocus
                            />
                        </div>
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane" class="mb-0.5">
                            {{ __('admin.support.send') }}
                        </x-filament::button>
                    </form>
                </footer>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 p-12">
                    <div class="p-6 bg-gray-100 dark:bg-white/5 rounded-full mb-4">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-12 h-12" />
                    </div>
                    <h3 class="text-lg font-bold mb-1">{{ __('admin.support.no_ticket_selected_title') }}</h3>
                    <p class="text-sm text-center max-w-xs">{{ __('admin.support.no_ticket_selected_desc') }}</p>
                </div>
            @endif
        </main>
    </div>
</x-filament-panels::page>
