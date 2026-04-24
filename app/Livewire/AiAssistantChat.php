<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Jobs\ProcessAiChatMessageJob;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;

class AiAssistantChat extends Component
{
    public string $message = '';

    /**
     * @var array<array{role: string, content: string, message_id?: string, status?: string}>
     */
    public array $messages = [];

    /**
     * @var array<array{id: int, session_id: string, title: string, updated_at_human: string, updated_at_iso: string}>
     */
    public array $sessions = [];

    public ?int $activeDbSessionId = null;

    public ?string $activeSessionUlid = null;

    public ?string $pendingAssistantMessageId = null;

    public ?int $pendingSinceUnix = null;

    public bool $isTyping = false;

    public bool $isRealtimeConnected = true;

    public ?string $realtimeStatusMessage = null;

    public function mount(): void
    {
        $userId = auth()->id();

        if (! $userId) {
            return;
        }

        $this->loadSessions($userId);
        $this->initializeActiveSession($userId);
        $this->loadActiveSessionMessages();
    }

    /**
     * @return array<string, string>
     */
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

    /**
     * @param  array{message_id: string}  $event
     */
    public function handleStarted(array $event): void
    {
        $this->isTyping = true;
        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;

        $this->updateOrAppendAssistantMessage(
            messageId: $event['message_id'],
            content: '',
            status: 'processing',
        );
    }

    /**
     * @param  array{message_id: string, text_chunk: string}  $event
     */
    public function handleChunk(array $event): void
    {
        $this->isTyping = true;
        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;
        $messageId = $event['message_id'];
        $chunk = $event['text_chunk'];

        $messageIndex = $this->findMessageIndexById($messageId);

        if ($messageIndex !== null) {
            $this->messages[$messageIndex]['content'] .= $chunk;
            $this->messages[$messageIndex]['status'] = 'processing';
        } else {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $chunk,
                'message_id' => $messageId,
                'status' => 'processing',
            ];
        }

        $this->dispatch('message-sent');
    }

    /**
     * @param  array{message_id: string, full_text: string}  $event
     */
    public function handleCompleted(array $event): void
    {
        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;
        $this->updateOrAppendAssistantMessage(
            messageId: $event['message_id'],
            content: $event['full_text'],
            status: 'done',
        );

        $this->finalizePendingMessage();
    }

    /**
     * @param  array{message_id: string, error: string}  $event
     */
    public function handleFailed(array $event): void
    {
        $this->isRealtimeConnected = false;
        $this->realtimeStatusMessage = 'Realtime stream disconnected. Switched to fallback sync mode.';
        $this->updateOrAppendAssistantMessage(
            messageId: $event['message_id'],
            content: 'Error: '.$event['error'],
            status: 'failed',
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

        if ($this->isTyping) {
            $this->realtimeStatusMessage = 'Realtime connection is unstable. Waiting with fallback sync mode.';
        } else {
            $this->realtimeStatusMessage = null;
        }

        if ($reason && $this->isTyping) {
            $this->realtimeStatusMessage = 'Realtime connection is unstable ('.$reason.'). Waiting with fallback sync mode.';
        }
    }

    public function startNewChat(): void
    {
        $userId = auth()->id();

        if (! $userId) {
            return;
        }

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

        $session = AiChatSession::query()
            ->where('id', $sessionId)
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
        if (
            ! $this->pendingAssistantMessageId ||
            ! $this->activeDbSessionId
        ) {
            return;
        }

        $assistantMessage = AiChatMessage::query()
            ->where('ai_chat_session_id', $this->activeDbSessionId)
            ->where('message_id', $this->pendingAssistantMessageId)
            ->first();

        if (! $assistantMessage) {
            return;
        }

        if ($assistantMessage->status === 'done') {
            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: (string) $assistantMessage->content,
                status: 'done',
            );

            $this->finalizePendingMessage();

            return;
        }

        if ($assistantMessage->status === 'failed') {
            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: 'Error: '.($assistantMessage->error ?: 'Unable to generate response. Please try again.'),
                status: 'failed',
            );

            $this->finalizePendingMessage();

            return;
        }

        if ($assistantMessage->content !== '') {
            $this->updateOrAppendAssistantMessage(
                messageId: (string) $assistantMessage->message_id,
                content: (string) $assistantMessage->content,
                status: (string) ($assistantMessage->status ?: 'processing'),
            );
            $this->dispatch('message-sent');
        }

        if (! $this->pendingSinceUnix) {
            return;
        }

        $elapsedSeconds = time() - $this->pendingSinceUnix;

        if ($elapsedSeconds >= 8 && ! $this->isRealtimeConnected) {
            $this->realtimeStatusMessage = 'Realtime stream disconnected. Waiting for queued response sync.';
        }

        if ($elapsedSeconds >= 90) {
            $this->updateOrAppendAssistantMessage(
                messageId: $this->pendingAssistantMessageId,
                content: 'Request timed out while waiting for AI response. Please try sending again.',
                status: 'failed',
            );

            $this->finalizePendingMessage();
        }
    }

    public function sendMessage(): void
    {
        if (trim($this->message) === '' || $this->isTyping) {
            return;
        }

        $userId = auth()->id();

        if (! $userId || ! $this->activeDbSessionId || ! $this->activeSessionUlid) {
            return;
        }

        $userMessage = trim($this->message);
        $this->message = '';
        $this->isTyping = true;
        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;

        $userMsg = AiChatMessage::create([
            'ai_chat_session_id' => $this->activeDbSessionId,
            'message_id' => (string) Str::ulid(),
            'role' => 'user',
            'content' => $userMessage,
            'status' => 'done',
            'sequence' => count($this->messages) + 1,
        ]);

        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'message_id' => $userMsg->message_id,
            'status' => 'done',
        ];

        $assistantMsgId = (string) Str::ulid();
        AiChatMessage::create([
            'ai_chat_session_id' => $this->activeDbSessionId,
            'message_id' => $assistantMsgId,
            'role' => 'assistant',
            'content' => '',
            'status' => 'processing',
            'sequence' => count($this->messages) + 1,
        ]);

        $this->messages[] = [
            'role' => 'assistant',
            'content' => '',
            'message_id' => $assistantMsgId,
            'status' => 'processing',
        ];

        $this->pendingAssistantMessageId = $assistantMsgId;
        $this->pendingSinceUnix = time();

        $session = AiChatSession::query()
            ->where('id', $this->activeDbSessionId)
            ->where('user_id', $userId)
            ->first();

        if ($session) {
            $session->update([
                'title' => $this->generateSessionTitle(
                    currentTitle: $session->title,
                    prompt: $userMessage,
                ),
                'last_message_at' => now(),
            ]);
        }

        ProcessAiChatMessageJob::dispatch(
            userId: $userId,
            sessionId: $this->activeSessionUlid,
            messageId: $assistantMsgId,
            prompt: $userMessage
        );

        $this->loadSessions($userId);
        $this->dispatch('message-sent');
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

    public function render(): View
    {
        return view('livewire.ai-assistant-chat');
    }

    private function initializeActiveSession(int $userId): void
    {
        $latestSession = AiChatSession::query()
            ->where('user_id', $userId)
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
            'title' => 'New chat',
            'last_message_at' => now(),
        ]);
    }

    private function setActiveSession(AiChatSession $session): void
    {
        $this->activeDbSessionId = (int) $session->id;
        $this->activeSessionUlid = (string) $session->session_id;
        $this->pendingAssistantMessageId = null;
        $this->pendingSinceUnix = null;
        $this->isTyping = false;
        $this->isRealtimeConnected = true;
        $this->realtimeStatusMessage = null;
    }

    private function loadSessions(int $userId): void
    {
        $this->sessions = AiChatSession::query()
            ->where('user_id', $userId)
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->get(['id', 'session_id', 'title', 'last_message_at', 'updated_at'])
            ->map(static function (AiChatSession $session): array {
                $activityAt = $session->last_message_at ?? $session->updated_at;

                return [
                    'id' => (int) $session->id,
                    'session_id' => (string) $session->session_id,
                    'title' => (string) ($session->title ?: 'New chat'),
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

        $this->messages = AiChatMessage::query()
            ->where('ai_chat_session_id', $this->activeDbSessionId)
            ->orderBy('created_at')
            ->get()
            ->map(static fn (AiChatMessage $message): array => [
                'role' => (string) $message->role,
                'content' => (string) $message->content,
                'message_id' => (string) $message->message_id,
                'status' => (string) ($message->status ?: 'done'),
            ])
            ->values()
            ->toArray();

        if ($this->messages === []) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'Hi! I am FreshLeaf Assistant. Ask me anything about your operations.',
                'status' => 'done',
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
        $this->pendingAssistantMessageId = null;
        $this->pendingSinceUnix = null;
        $this->isTyping = false;

        if ($this->realtimeStatusMessage && $this->isRealtimeConnected) {
            $this->realtimeStatusMessage = null;
        }

        $userId = auth()->id();

        if ($userId) {
            $this->loadSessions($userId);
        }

        $this->dispatch('message-sent');
    }

    private function generateSessionTitle(?string $currentTitle, string $prompt): string
    {
        if ($currentTitle && $currentTitle !== 'New chat') {
            return $currentTitle;
        }

        return Str::limit($prompt, 42);
    }
}
