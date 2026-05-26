<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use App\Models\UserType;
use App\Services\Ai\AiService;
use App\Services\Auth\UserSessionSecurity;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;

use function __;
use function count;
use function now;
use function session;
use function time;
use function trim;
use function view;

class AiAssistantChat extends Component
{
    // Message Status Constants
    private const string STATUS_DONE = 'done';

    private const string STATUS_PROCESSING = 'processing';

    private const string STATUS_FAILED = 'failed';

    // Timeout Constants
    private const int REALTIME_FALLBACK_SECONDS = 8;

    private const int REQUEST_TIMEOUT_SECONDS = 300;

    private const int HISTORY_MESSAGE_LIMIT = 8;

    private const int HISTORY_CONTENT_LIMIT = 1200;

    // Listeners Methods

    private const string FUNC_HANDLE_STARTED = 'handleStarted';

    private const string FUNC_HANDLE_CHUNK = 'handleChunk';

    private const string FUNC_HANDLE_COMPLETED = 'handleCompleted';

    private const string FUNC_HANDLE_FAILED = 'handleFailed';

    public string $message = '';

    /**
     * @var array<array{
     *  role?: string,
     *  content: string,
     *  message_id?: string|null,
     *  status?: string
     * }>
     */
    public array $messages = [];

    /**
     * @var array<array{
     *  id: int,
     *  session_id: string,
     *  title: string,
     *  updated_at_human: string,
     *  updated_at_iso: string
     * }>
     */
    public array $sessions = [];

    public ?int $activeDbSessionId = null;

    public ?string $activeSessionUlid = null;

    public ?string $pendingAssistantMessageId = null;

    public ?int $pendingSinceUnix = null;

    public bool $isTyping = false;

    public bool $isRealtimeConnected = true;

    public bool $isAiServiceAvailable = true;

    public ?string $realtimeStatusMessage = null;

    public bool $showHistory = true;

    /**
     * Mount the component
     */
    public function mount(): void
    {
        $user = UserSessionSecurity::getAuthorizedUser();

        if (! $user || ! $user->isActive()) {
            return;
        }

        $panelId = Filament::getCurrentPanel()?->getId();

        $isAuthorized = match ($panelId) {
            'admin' => $user->user_type_id === UserType::ADMIN_ID,
            'vendor' => $user->user_type_id === UserType::VENDOR_ID,
            default => false,
        };

        if (! $isAuthorized) {
            $this->dispatch('notify-error', __('Unauthorized to access AI Assistant'));

            return;
        }

        $this->showHistory = (bool) session('ai_assistant_show_history', true);
        $this->checkAiServiceStatus();
        $this->initializeChat((int) $user->id);
    }

    /**
     * Check AI Service Status
     */
    public function checkAiServiceStatus(): void
    {
        $this->isAiServiceAvailable = app(AiService::class)->healthCheck();
    }

    private function getRoleRelation(): string
    {
        $user = UserSessionSecurity::getAuthorizedUser();
        $userTypeId = $user ? $user->user_type_id : UserType::CONSUMER_ID;

        return match ($userTypeId) {
            UserType::ADMIN_ID => 'admin',
            UserType::VENDOR_ID => 'vendor',
            default => 'consumer',
        };
    }

    /**
     * Initialize Chat
     */
    private function initializeChat(int $userId): void
    {
        $this->loadSessions($userId);
        $this->initializeActiveSession($userId);
        $this->loadActiveSessionMessages();
    }

    /**
     * Updated Show History
     */
    /**
     * Updated Show History
     */
    public function updatedShowHistory(bool $value): void
    {
        session()->put('ai_assistant_show_history', $value);
        session()->save();
    }

    /**
     * Toggle History
     */
    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
        session()->put('ai_assistant_show_history', $this->showHistory);
        session()->save();
    }

    /**
     * Delete Session
     */
    public function deleteSession(int $sessionId): void
    {
        $user = UserSessionSecurity::getAuthorizedUser();

        if (! $user) {
            return;
        }

        $userId = $user->id;

        AiChatSession::where('id', $sessionId)
            ->whereHas($this->getRoleRelation(), fn ($query) => $query->where('users.id', $userId))
            ->delete();

        if ($this->activeDbSessionId === $sessionId) {
            $this->initializeChat($userId);
        } else {
            $this->loadSessions($userId);
        }

        $this->dispatch('message-sent');
    }

    /**
     * Get Listeners
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $userId = $user->id;

        if (! $this->activeSessionUlid) {
            return [];
        }

        return [
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageStarted" => self::FUNC_HANDLE_STARTED,
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageChunk" => self::FUNC_HANDLE_CHUNK,
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageCompleted" => self::FUNC_HANDLE_COMPLETED,
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageFailed" => self::FUNC_HANDLE_FAILED,
        ];
    }

    /**
     * Handle Started
     *
     * @param  array{message_id: string}  $event
     */
    public function handleStarted(array $event): void
    {
        if ($this->isMessageFailed($event['message_id'])) {
            return;
        }

        $this->resetRealtimeState();
        $this->isTyping = true;
        $this->updateOrAppendAssistantMessage(
            messageId: $event['message_id'],
            content: '',
            status: self::STATUS_PROCESSING,
        );
    }

    /**
     * Handle Chunk
     *
     * @param  array{message_id: string, text_chunk: string}  $event
     */
    public function handleChunk(array $event): void
    {
        if ($this->isMessageFailed($event['message_id'])) {
            return;
        }

        $this->isTyping = true;
        $this->isRealtimeConnected = true;
        $messageId = $event['message_id'];
        $chunk = $event['text_chunk'];

        $messageIndex = $this->findMessageIndexById($messageId);
        if ($messageIndex !== null) {
            $this->messages[$messageIndex]['content'] .= $chunk;
            $this->messages[$messageIndex]['status'] = self::STATUS_PROCESSING;
        } else {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $chunk,
                'message_id' => $messageId,
                'status' => self::STATUS_PROCESSING,
            ];
        }

        $this->dispatch('message-sent');
    }

    /**
     * Handle Completed
     *
     * @param  array{message_id: string, full_text: string}  $event
     */
    public function handleCompleted(array $event): void
    {
        if ($this->isMessageFailed($event['message_id'])) {
            return;
        }

        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;
        $this->updateOrAppendAssistantMessage(
            messageId: $event['message_id'],
            content: $event['full_text'],
            status: self::STATUS_DONE,
        );
        $this->finalizePendingMessage();
    }

    /**
     * Handle Failed
     *
     * @param  array{message_id: string, error: string}  $event
     */
    public function handleFailed(array $event): void
    {
        $this->isRealtimeConnected = false;
        $this->realtimeStatusMessage = 'Realtime stream disconnected. Switched to fallback sync mode.';
        $this->updateOrAppendAssistantMessage(
            messageId: $event['message_id'],
            content: 'Error: '.$event['error'],
            status: self::STATUS_FAILED,
        );
        $this->finalizePendingMessage();
    }

    /**
     * Handle Realtime Status
     */
    public function handleRealtimeStatus(?string $state = null, ?string $reason = null): void
    {
        if ($state === 'connected') {
            $this->isRealtimeConnected = true;
            $this->realtimeStatusMessage = null;

            return;
        }

        $this->isRealtimeConnected = false;
        if (! $this->isTyping) {
            $this->realtimeStatusMessage = null;

            return;
        }

        $this->realtimeStatusMessage = $reason
            ? "Realtime connection is unstable ({$reason}). Waiting with fallback sync mode."
            : 'Realtime connection is unstable. Waiting with fallback sync mode.';
    }

    /**
     * Start New Chat
     */
    public function startNewChat(): void
    {
        $user = UserSessionSecurity::getAuthorizedUser();

        if (! $user) {
            return;
        }

        $userId = (int) $user->id;
        $session = $this->createSession($userId);
        $this->loadSessions($userId);
        $this->setActiveSession($session);
        $this->loadActiveSessionMessages();
        $this->dispatch('message-sent');
    }

    /**
     * Switch Session
     */
    public function switchSession(int $sessionId): void
    {
        $user = UserSessionSecurity::getAuthorizedUser();

        if (! $user) {
            return;
        }

        $userId = $user->id;

        $session = AiChatSession::where('id', $sessionId)
            ->whereHas($this->getRoleRelation(), fn ($query) => $query->where('users.id', $userId))
            ->first();

        if (! $session) {
            return;
        }

        $this->setActiveSession($session);
        $this->loadActiveSessionMessages();
        $this->dispatch('message-sent');
    }

    /**
     * Sync Pending Response
     */
    public function syncPendingResponse(): void
    {
        if (! $this->pendingAssistantMessageId || ! $this->activeDbSessionId) {
            return;
        }

        $assistantMessage = AiChatMessage::where('ai_chat_session_id', $this->activeDbSessionId)
            ->where('message_id', $this->pendingAssistantMessageId)
            ->first();

        if (! $assistantMessage) {
            return;
        }

        if ($assistantMessage->status === self::STATUS_DONE) {
            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: (string) $assistantMessage->content,
                status: self::STATUS_DONE,
            );

            $this->finalizePendingMessage();

            return;
        }

        if ($assistantMessage->status === self::STATUS_FAILED) {
            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: 'Error: '.($assistantMessage->error ?: 'Unable to generate response. Please try again.'),
                status: self::STATUS_FAILED,
            );
            $this->finalizePendingMessage();

            return;
        }

        if ($assistantMessage->content !== '') {
            $messageIndex = $this->findMessageIndexById((string) $assistantMessage->message_id);

            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: (string) $assistantMessage->content,
                status: (string) ($assistantMessage->status ?: self::STATUS_PROCESSING),
            );

            $this->dispatch('message-sent');
        }

        $this->checkResponseTimeout();
    }

    /**
     * Check Response Timeout
     */
    private function checkResponseTimeout(): void
    {
        if (! $this->pendingSinceUnix) {
            return;
        }

        $elapsedSeconds = time() - $this->pendingSinceUnix;

        if ($elapsedSeconds >= self::REALTIME_FALLBACK_SECONDS && ! $this->isRealtimeConnected) {
            $this->realtimeStatusMessage = 'Realtime stream disconnected. Waiting for queued response sync.';
        }

        if ($elapsedSeconds >= self::REQUEST_TIMEOUT_SECONDS) {
            $this->updateOrAppendAssistantMessage(
                messageId: (string) $this->pendingAssistantMessageId,
                content: 'Request timed out while waiting for AI response. Please try sending again.',
                status: self::STATUS_FAILED,
            );
            AiChatMessage::where('message_id', $this->pendingAssistantMessageId)
                ->update([
                    'status' => self::STATUS_FAILED,
                    'error' => 'Request timed out while waiting for AI response.',
                ]);
            $this->finalizePendingMessage();
        }
    }

    /**
     * Stop Generating
     */
    public function stopGenerating(): void
    {
        if (! $this->pendingAssistantMessageId) {
            return;
        }

        Cache::put("ai_stop_{$this->pendingAssistantMessageId}", true, 60);

        AiChatMessage::where('message_id', $this->pendingAssistantMessageId)
            ->update([
                'status' => self::STATUS_FAILED,
                'error' => 'Generation stopped by user.',
            ]);

        $messageIndex = $this->findMessageIndexById($this->pendingAssistantMessageId);
        if ($messageIndex !== null) {
            $this->messages[$messageIndex]['status'] = self::STATUS_FAILED;
        }

        $this->finalizePendingMessage();
    }

    /**
     * Send Message
     */
    public function sendMessage(): void
    {
        if (trim($this->message) === '' || $this->isTyping) {
            return;
        }

        $user = UserSessionSecurity::getAuthorizedUser();
        if (! $user || ! $this->activeDbSessionId || ! $this->activeSessionUlid) {
            return;
        }

        $userId = (int) $user->id;
        $userMessage = trim($this->message);
        $this->message = '';
        $this->resetRealtimeState();
        $this->isTyping = true;

        $userMsg = $this->createUserMessage($userMessage);
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'message_id' => $userMsg->message_id,
            'status' => self::STATUS_DONE,
        ];

        $assistantMsgId = (string) Str::ulid();
        $this->createAssistantPlaceholder($assistantMsgId);
        $this->messages[] = [
            'role' => 'assistant',
            'content' => '',
            'message_id' => $assistantMsgId,
            'status' => self::STATUS_PROCESSING,
        ];

        $this->pendingAssistantMessageId = $assistantMsgId;
        $this->pendingSinceUnix = time();

        $this->updateSessionMetadata($userId, $userMessage);
        ProcessAiChatMessageJob::dispatch(
            userId: $userId,
            sessionId: $this->activeSessionUlid,
            messageId: $assistantMsgId,
            prompt: $userMessage,
            history: $this->getChatHistory($userId)
        );

        $this->loadSessions($userId);
        $this->dispatch('message-sent');
    }

    /**
     * Create User Message
     */
    private function createUserMessage(string $content): AiChatMessage
    {
        return AiChatMessage::create([
            'ai_chat_session_id' => $this->activeDbSessionId,
            'user_id' => Auth::id(),
            'message_id' => (string) Str::ulid(),
            'role' => 'user',
            'content' => $content,
            'status' => self::STATUS_DONE,
            'sequence' => count($this->messages) + 1,
        ]);
    }

    /**
     * Create Assistant Placeholder
     */
    private function createAssistantPlaceholder(string $messageId): AiChatMessage
    {
        return AiChatMessage::create([
            'ai_chat_session_id' => $this->activeDbSessionId,
            'user_id' => Auth::id(),
            'message_id' => $messageId,
            'role' => 'assistant',
            'content' => '',
            'status' => self::STATUS_PROCESSING,
            'sequence' => count($this->messages) + 1,
        ]);
    }

    /**
     * Update Session Metadata
     */
    private function updateSessionMetadata(int $userId, string $userMessage): void
    {
        $session = AiChatSession::where('id', $this->activeDbSessionId)
            ->whereHas($this->getRoleRelation(), fn ($query) => $query->where('users.id', $userId))
            ->first();

        if ($session) {
            $session->update([
                'title' => $this->generateSessionTitle($session->title, $userMessage),
                'last_message_at' => now(),
            ]);
        }
    }

    /**
     * Render Assistant Message
     */
    public function renderAssistantMessage(string $content): HtmlString
    {
        if (trim($content) === '') {
            return new HtmlString('<p class="ai-message-placeholder">...</p>');
        }

        return new HtmlString(Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }

    /**
     * Check if session is active
     */
    public function isActiveSession(int $sessionId): bool
    {
        return $this->activeDbSessionId === $sessionId;
    }

    /**
     * Check if message is failed
     */
    private function isMessageFailed(string $messageId): bool
    {
        $index = $this->findMessageIndexById($messageId);

        return $index !== null &&
            ($this->messages[$index]['status'] ?? null) === self::STATUS_FAILED;
    }

    /**
     * Render the component
     */
    public function render(): View
    {
        return view('livewire.ai-assistant-chat');
    }

    /**
     * Reset Realtime State
     */
    private function resetRealtimeState(): void
    {
        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;
    }

    /**
     * Reset Pending State
     */
    private function resetPendingState(): void
    {
        $this->pendingAssistantMessageId = null;
        $this->pendingSinceUnix = null;
        $this->isTyping = false;
    }

    /**
     * Get Chat History
     *
     * @return array<array{role: string, content: string}>
     */
    private function getChatHistory(int $userId): array
    {
        if (! $this->activeDbSessionId) {
            return [];
        }

        return AiChatMessage::where('ai_chat_session_id', $this->activeDbSessionId)
            ->where('status', self::STATUS_DONE)
            ->orderBy('sequence')
            ->get()
            ->filter(static fn (AiChatMessage $message): bool => trim((string) $message->content) !== '')
            ->take(-self::HISTORY_MESSAGE_LIMIT)
            ->map(static fn (AiChatMessage $message): array => [
                'role' => (string) $message->role,
                'content' => Str::limit((string) $message->content, self::HISTORY_CONTENT_LIMIT, ''),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Initialize Active Session
     */
    private function initializeActiveSession(int $userId): void
    {
        $latestSession = AiChatSession::whereHas($this->getRoleRelation(), fn ($query) => $query->where('users.id', $userId))
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->first();

        if (! $latestSession) {
            $latestSession = $this->createSession($userId);
            $this->loadSessions($userId);
        }

        $this->setActiveSession($latestSession);
    }

    /**
     * Create Session
     */
    private function createSession(int $userId): AiChatSession
    {
        return AiChatSession::create([
            'user_id' => $userId,
            'session_id' => (string) Str::ulid(),
            'title' => __('admin.ai.new_chat'),
            'last_message_at' => now(),
        ]);
    }

    /**
     * Set Active Session
     */
    private function setActiveSession(AiChatSession $session): void
    {
        $this->activeDbSessionId = (int) $session->id;
        $this->activeSessionUlid = (string) $session->session_id;
        $this->resetPendingState();
        $this->resetRealtimeState();
    }

    /**
     * Load Sessions
     */
    private function loadSessions(int $userId): void
    {
        $this->sessions = AiChatSession::whereHas($this->getRoleRelation(), fn ($query) => $query->where('users.id', $userId))
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->get(['id', 'session_id', 'title', 'last_message_at', 'updated_at'])
            ->map(static function (AiChatSession $session): array {
                $activityAt = $session->last_message_at ?? $session->updated_at;

                return [
                    'id' => (int) $session->id,
                    'session_id' => (string) $session->session_id,
                    'title' => (string) ($session->title ?: __('admin.ai.new_chat')),
                    'updated_at_human' => $activityAt?->diffForHumans() ?? 'Just now',
                    'updated_at_iso' => $activityAt?->toIso8601String() ?? now()->toIso8601String(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Load Active Session Messages
     */
    private function loadActiveSessionMessages(): void
    {
        if (! $this->activeDbSessionId) {
            $this->messages = [];

            return;
        }

        $this->messages = AiChatMessage::where('ai_chat_session_id', $this->activeDbSessionId)
            ->orderBy('created_at')
            ->get()
            ->map(static fn (AiChatMessage $message): array => [
                'role' => (string) $message->role,
                'content' => (string) $message->content,
                'message_id' => (string) $message->message_id,
                'status' => (string) ($message->status ?: self::STATUS_DONE),
            ])
            ->values()
            ->toArray();

        if ($this->messages === []) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => __('admin.ai.welcome_message'),
                'status' => self::STATUS_DONE,
            ];
        }
    }

    /**
     * Update or Append Assistant Message
     */
    private function updateOrAppendAssistantMessage(
        string $messageId,
        string $content,
        string $status
    ): void {
        $messageIndex = $this->findMessageIndexById($messageId);

        if ($messageIndex !== null) {
            $this->messages[$messageIndex]['content'] = $content;
            $this->messages[$messageIndex]['status'] = $status;

            return;
        }

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content,
            'message_id' => $messageId,
            'status' => $status,
        ];
    }

    /**
     * Find Message Index by ID
     */
    private function findMessageIndexById(string $messageId): ?int
    {
        foreach ($this->messages as $index => $message) {
            if (($message['message_id'] ?? null) === $messageId) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Finalize Pending Message
     */
    private function finalizePendingMessage(): void
    {
        $this->resetPendingState();

        if ($this->realtimeStatusMessage && $this->isRealtimeConnected) {
            $this->realtimeStatusMessage = null;
        }

        $user = UserSessionSecurity::getAuthorizedUser();
        if ($user) {
            $this->loadSessions($user->id);
        }

        $this->dispatch('message-sent');
    }

    /**
     * Generate Session Title
     */
    private function generateSessionTitle(?string $currentTitle, string $prompt): string
    {
        if ($currentTitle && $currentTitle !== __('admin.ai.new_chat')) {
            return $currentTitle;
        }

        return Str::limit($prompt, 42);
    }
}
