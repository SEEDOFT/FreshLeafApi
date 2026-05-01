<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\SupportMessageSent;
use App\Events\SupportTyping;
use App\Filament\Pages\SupportChat;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Notifications\NewSupportMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class SupportChatRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE, 'code' => 'ACTIVE', 'name' => 'Active'],
            ['id' => UserStatus::INACTIVE, 'code' => 'INACTIVE', 'name' => 'Inactive'],
            ['id' => UserStatus::DELETED, 'code' => 'DELETED', 'name' => 'Deleted'],
        ], ['id'], ['code', 'name']);

        UserType::upsert([
            ['id' => UserType::USER, 'code' => 'USER', 'name' => 'User'],
            ['id' => UserType::VENDOR, 'code' => 'VENDOR', 'name' => 'Vendor'],
            ['id' => UserType::ADMIN, 'code' => 'ADMIN', 'name' => 'Admin'],
        ], ['id'], ['code', 'name']);
    }

    public function test_user_support_message_broadcasts_to_ticket_and_admin_channels(): void
    {
        $message = SupportMessage::query()->make([
            'support_ticket_id' => 123,
            'sender_type' => 'user',
            'sender_id' => 456,
            'message' => 'I need help',
            'file_path' => 'support/files/help.png',
        ]);

        $channels = collect((new SupportMessageSent($message))->broadcastOn())
            ->map(static fn (object $channel): string => $channel->name)
            ->all();

        $this->assertContains('private-support.ticket.123', $channels);
        $this->assertContains('private-support.admin', $channels);
    }

    public function test_admin_support_message_keeps_ticket_channel_for_flutter(): void
    {
        $message = SupportMessage::query()->make([
            'support_ticket_id' => 123,
            'sender_type' => 'admin',
            'sender_id' => 456,
            'message' => 'We can help',
        ]);

        $channels = collect((new SupportMessageSent($message))->broadcastOn())
            ->map(static fn (object $channel): string => $channel->name)
            ->all();

        $this->assertSame(['private-support.ticket.123'], $channels);
    }

    public function test_user_typing_broadcasts_to_ticket_and_admin_channels(): void
    {
        $channels = collect((new SupportTyping(123, 'user'))->broadcastOn())
            ->map(static fn (object $channel): string => $channel->name)
            ->all();

        $this->assertContains('private-support.ticket.123', $channels);
        $this->assertContains('private-support.admin', $channels);
    }

    public function test_support_chat_has_stable_admin_realtime_listeners(): void
    {
        $listeners = (new SupportChat)->getListeners();

        $this->assertSame(
            '$refresh',
            $listeners['echo-private:support.admin,NewSupportTicket']
        );
        $this->assertSame(
            'handleIncomingMessage',
            $listeners['echo-private:support.admin,SupportMessageSent']
        );
        $this->assertSame(
            'handleTypingEvent',
            $listeners['echo-private:support.admin,SupportTyping']
        );
    }

    public function test_user_message_send_touches_ticket_timestamp(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::USER,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $ticket = SupportTicket::query()->create([
            'user_id' => $user->id,
            'status' => 'open',
        ]);
        $ticket->forceFill(['updated_at' => now()->subHour()])->saveQuietly();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/support/messages', [
            'ticket_id' => $ticket->id,
            'message' => 'Hello support',
        ])->assertCreated();

        $this->assertTrue($ticket->fresh()->updated_at->greaterThan(now()->subMinute()));
    }

    public function test_admin_reply_sends_immediate_notification_to_ticket_owner(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $user = User::factory()->create([
            'user_type_id' => UserType::USER,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
        $ticket = SupportTicket::query()->create([
            'user_id' => $user->id,
            'status' => 'open',
        ]);

        $this->actingAs($admin);

        Livewire::test(SupportChat::class, ['activeTicketId' => $ticket->id])
            ->set('message', 'Thanks for waiting')
            ->call('sendMessage');

        Notification::assertSentTo($user, NewSupportMessageNotification::class);
    }
}
