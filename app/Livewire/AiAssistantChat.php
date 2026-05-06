<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\User;
use App\Models\UserType;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;

use function __;
use function auth;
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

    public ?string $realtimeStatusMessage = null;

    public bool $showHistory = true;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user || ! $user->isActive()) {
            return;
        }

        $panelId = Filament::getCurrentPanel()?->getId();

        // Cross-verify user type against current panel context
        $isAuthorized = match ($panelId) {
            'admin' => $user->isType(UserType::ADMIN),
            'vendor' => $user->isType(UserType::VENDOR),
            default => false,
        };

        if (! $isAuthorized) {
            return;
        }

        $this->showHistory = (bool) session('ai_assistant_show_history', true);
        $this->initializeChat((int) $user->id);
    }

    private function initializeChat(int $userId): void
    {
        $this->loadSessions($userId);
        $this->initializeActiveSession($userId);
        $this->loadActiveSessionMessages();
    }

    public function updatedShowHistory(bool $value): void
    {
        session(['ai_assistant_show_history' => $value]);
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
        session(['ai_assistant_show_history' => $this->showHistory]);
    }

    public function deleteSession(int $sessionId): void
    {
        $userId = auth()->id();
        if ($userId === null) {
            return;
        }

        $userId = (int) $userId;

        AiChatSession::where('id', $sessionId)
            ->where('user_id', $userId)
            ->delete();

        if ($this->activeDbSessionId === $sessionId) {
            $this->initializeChat($userId);
        } else {
            $this->loadSessions($userId);
        }

        $this->dispatch('message-sent');
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        $userId = auth()->id();
        if (! $userId || ! $this->activeSessionUlid) {
            return [];
        }

        return [
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageStarted" => 'handleStarted',
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageChunk" => 'handleChunk',
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageCompleted" => 'handleCompleted',
            "echo-private:ai-chat.{$userId}.{$this->activeSessionUlid},AiMessageFailed" => 'handleFailed',
        ];
    }

    /** @param array{message_id: string} $event */
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

    /** @param array{message_id: string, text_chunk: string} $event */
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

    /** @param array{message_id: string, full_text: string} $event */
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

    /** @param array{message_id: string, error: string} $event */
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

    public function startNewChat(): void
    {
        $userId = auth()->id();
        if ($userId === null) {
            return;
        }

        $userId = (int) $userId;
        $session = $this->createSession($userId);
        $this->loadSessions($userId);
        $this->setActiveSession($session);
        $this->loadActiveSessionMessages();
        $this->dispatch('message-sent');
    }

    public function switchSession(int $sessionId): void
    {
        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        $session = AiChatSession::where('id', $sessionId)
            ->where('user_id', $userId)
            ->first();

        if (! $session) {
            return;
        }

        $this->setActiveSession($session);
        $this->loadActiveSessionMessages();
        $this->dispatch('message-sent');
    }

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
            Log::info('AI chat polling finalized completed response', [
                'message_id' => $assistantMessage->id,
            ]);
            $this->finalizePendingMessage();

            return;
        }

        if ($assistantMessage->status === self::STATUS_FAILED) {
            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: 'Error: '.($assistantMessage->error ?: 'Unable to generate response. Please try again.'),
                status: self::STATUS_FAILED,
            );
            Log::info('AI chat polling finalized failed response', [
                'message_id' => $assistantMessage->id,
            ]);
            $this->finalizePendingMessage();

            return;
        }

        if ($assistantMessage->content !== '') {
            $messageIndex = $this->findMessageIndexById((string) $assistantMessage->message_id);
            $currentContent = $messageIndex === null ? '' : (string) ($this->messages[$messageIndex]['content'] ?? '');

            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: (string) $assistantMessage->content,
                status: (string) ($assistantMessage->status ?: self::STATUS_PROCESSING),
            );
            if ($currentContent !== (string) $assistantMessage->content) {
                Log::info('AI chat polling recovered partial response', [
                    'message_id' => $assistantMessage->id,
                    'characters' => \strlen((string) $assistantMessage->content),
                ]);
            }
            $this->dispatch('message-sent');
        }

        $this->checkResponseTimeout();
    }

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
            AiChatMessage::query()
                ->where('message_id', $this->pendingAssistantMessageId)
                ->update([
                    'status' => self::STATUS_FAILED,
                    'error' => 'Request timed out while waiting for AI response.',
                ]);
            $this->finalizePendingMessage();
        }
    }

    public function stopGenerating(): void
    {
        if (! $this->pendingAssistantMessageId) {
            return;
        }

        // Set cancellation flag in cache for 1 minute
        Cache::put("ai_stop_{$this->pendingAssistantMessageId}", true, 60);

        // Update database status
        AiChatMessage::where('message_id', $this->pendingAssistantMessageId)
            ->update([
                'status' => self::STATUS_FAILED,
                'error' => 'Generation stopped by user.',
            ]);

        // Update local state
        $messageIndex = $this->findMessageIndexById($this->pendingAssistantMessageId);
        if ($messageIndex !== null) {
            $this->messages[$messageIndex]['status'] = self::STATUS_FAILED;
        }

        $this->finalizePendingMessage();
    }

    public function sendMessage(): void
    {
        if (trim($this->message) === '' || $this->isTyping) {
            return;
        }

        $rawUserId = auth()->id();
        if (! $rawUserId || ! $this->activeDbSessionId || ! $this->activeSessionUlid) {
            return;
        }

        $userId = (int) $rawUserId;
        $userMessage = trim($this->message);
        $this->message = '';
        $this->resetRealtimeState();
        $this->isTyping = true;

        // Create user message
        $userMsg = $this->createUserMessage($userMessage);
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'message_id' => $userMsg->message_id,
            'status' => self::STATUS_DONE,
        ];

        // Create assistant message placeholder
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

        // Update session title and dispatch job
        $this->updateSessionMetadata($userId, $userMessage);
        ProcessAiChatMessageJob::dispatch(
            userId: $userId,
            prompt: $userMessage,
            history: $this->getChatHistory($userId)
        );

        $this->loadSessions($userId);
        $this->dispatch('message-sent');
    }

    private function createUserMessage(string $content): AiChatMessage
    {
        return AiChatMessage::create([
            'ai_chat_session_id' => $this->activeDbSessionId,
            'user_id' => auth()->id(),
            'message_id' => (string) Str::ulid(),
            'role' => 'user',
            'content' => $content,
            'status' => self::STATUS_DONE,
            'sequence' => count($this->messages) + 1,
        ]);
    }

    private function createAssistantPlaceholder(string $messageId): AiChatMessage
    {
        return AiChatMessage::create([
            'ai_chat_session_id' => $this->activeDbSessionId,
            'user_id' => auth()->id(),
            'message_id' => $messageId,
            'role' => 'assistant',
            'content' => '',
            'status' => self::STATUS_PROCESSING,
            'sequence' => count($this->messages) + 1,
        ]);
    }

    private function updateSessionMetadata(int $userId, string $userMessage): void
    {
        $session = AiChatSession::where('id', $this->activeDbSessionId)
            ->where('user_id', $userId)
            ->first();

        if ($session) {
            $session->update([
                'title' => $this->generateSessionTitle($session->title, $userMessage),
                'last_message_at' => now(),
            ]);
        }
    }

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

    public function isActiveSession(int $sessionId): bool
    {
        return $this->activeDbSessionId === $sessionId;
    }

    private function isMessageFailed(string $messageId): bool
    {
        $index = $this->findMessageIndexById($messageId);

        return $index !== null && ($this->messages[$index]['status'] ?? null) === self::STATUS_FAILED;
    }

    public function render(): View
    {
        return view('livewire.ai-assistant-chat');
    }

    private function resetRealtimeState(): void
    {
        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;
    }

    private function resetPendingState(): void
    {
        $this->pendingAssistantMessageId = null;
        $this->pendingSinceUnix = null;
        $this->isTyping = false;
    }

    /** @return array<array{role: string, content: string}> */
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

    private function initializeActiveSession(int $userId): void
    {
        $latestSession = AiChatSession::where('user_id', $userId)
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->first();

        if (! $latestSession) {
            $latestSession = $this->createSession($userId);
            $this->loadSessions($userId);
        }

        $this->setActiveSession($latestSession);
    }

    private function createSession(int $userId): AiChatSession
    {
        return AiChatSession::create([
            'user_id' => $userId,
            'session_id' => (string) Str::ulid(),
            'title' => __('admin.ai.new_chat'),
            'last_message_at' => now(),
        ]);
    }

    private function setActiveSession(AiChatSession $session): void
    {
        $this->activeDbSessionId = (int) $session->id;
        $this->activeSessionUlid = (string) $session->session_id;
        $this->resetPendingState();
        $this->resetRealtimeState();
    }

    private function loadSessions(int $userId): void
    {
        $this->sessions = AiChatSession::where('user_id', $userId)
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

    private function updateOrAppendAssistantMessage(string $messageId, string $content, string $status): void
    {
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

    private function findMessageIndexById(string $messageId): ?int
    {
        foreach ($this->messages as $index => $message) {
            if (($message['message_id'] ?? null) === $messageId) {
                return $index;
            }
        }

        return null;
    }

    private function finalizePendingMessage(): void
    {
        $this->resetPendingState();

        if ($this->realtimeStatusMessage && $this->isRealtimeConnected) {
            $this->realtimeStatusMessage = null;
        }

        $userId = auth()->id();
        if ($userId) {
            $this->loadSessions((int) $userId);
        }

        $this->dispatch('message-sent');
    }

    private function generateSessionTitle(?string $currentTitle, string $prompt): string
    {
        if ($currentTitle && $currentTitle !== __('admin.ai.new_chat')) {
            return $currentTitle;
        }

        return Str::limit($prompt, 42);
    }
}
