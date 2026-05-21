<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\SupportMessageSent;
use App\Events\SupportTyping;
use App\Livewire\SupportChat;
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
            ['id' => UserStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => UserStatus::INACTIVE_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
            ['id' => UserStatus::DELETED_ID, 'name_en' => 'Deleted', 'name_km' => 'បានលុប'],
        ], ['id'], ['name_en', 'name_km']);

        UserType::upsert([
            ['id' => UserType::CONSUMER_ID, 'name_en' => 'Consumer', 'name_km' => 'អ្នកប្រើប្រាស់'],
            ['id' => UserType::VENDOR_ID, 'name_en' => 'Vendor', 'name_km' => 'អ្នកលក់'],
            ['id' => UserType::ADMIN_ID, 'name_en' => 'Admin', 'name_km' => 'អ្នកគ្រប់គ្រង'],
        ], ['id'], ['name_en', 'name_km']);
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
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $ticket = SupportTicket::query()->create([
            'user_id' => $user->id,
            'status' => 'open',
        ]);
        $ticket->forceFill(['updated_at' => now()->subHour()])->saveQuietly();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/support/messages', [
            'ticket_id' => $ticket->id,
            'message' => 'Hello support',
        ])->assertOk();

        $this->assertTrue($ticket->fresh()->updated_at->greaterThan(now()->subMinute()));
    }

    public function test_admin_reply_sends_immediate_notification_to_ticket_owner(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $user = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
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
