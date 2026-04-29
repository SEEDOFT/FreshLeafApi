<x-filament-panels::page>
    <div 
        class="flex h-[calc(100vh-14rem)] overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-white/5"
        x-data="{
            scrollToBottom() {
                const container = document.getElementById('support-thread');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }
        }"
        x-init="scrollToBottom()"
        x-on:message-sent.window="setTimeout(() => scrollToBottom(), 50)"
        x-on:message-received.window="setTimeout(() => scrollToBottom(), 50)"
        x-on:ticket-selected.window="setTimeout(() => scrollToBottom(), 50)"
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
                            {{ $ticket->messages->last()?->message ?? 'No messages yet' }}
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
                                    <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <footer class="p-4 border-t border-gray-200 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                    <form wire:submit.prevent="sendMessage" class="flex gap-4">
                        <div class="flex-1">
                            <x-filament::input
                                wire:model="message"
                                placeholder="{{ __('admin.support.type_reply') }}"
                                autocomplete="off"
                                autofocus
                            />
                        </div>
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                            {{ __('admin.support.send') }}
                        </x-filament::button>
                    </form>
                </footer>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400 p-12">
                    <div class="p-6 bg-gray-100 dark:bg-white/5 rounded-full mb-4">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-12 h-12" />
                    </div>
                    <h3 class="text-lg font-bold mb-1">No ticket selected</h3>
                    <p class="text-sm text-center max-w-xs">Select a customer conversation from the sidebar to begin helping them.</p>
                </div>
            @endif
        </main>
    </div>
</x-filament-panels::page>
