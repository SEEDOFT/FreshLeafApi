<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ChatMessageSent;
use App\Events\ChatNotificationSent;
use App\Events\ChatTyping;
use App\Livewire\ChatInbox;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ConversationStatus;
use App\Models\ConversationType;
use App\Models\Message;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorProfile;
use App\Notifications\NewChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
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

        ConversationType::upsert([
            ['id' => ConversationType::DIRECT_ID, 'name' => 'direct'],
            ['id' => ConversationType::SUPPORT_ID, 'name' => 'support'],
        ], ['id'], ['name']);

        ConversationStatus::upsert([
            ['id' => ConversationStatus::OPEN_ID, 'name' => 'open'],
            ['id' => ConversationStatus::CLOSED_ID, 'name' => 'closed'],
        ], ['id'], ['name']);
    }

    public function test_chat_message_broadcasts_to_conversation_channel_with_stable_payload(): void
    {
        $sender = User::factory()->create([
            'first_name' => 'Dara',
            'last_name' => 'Vendor',
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $conversation = $this->createConversation($sender);
        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Can you check this order?',
        ]);

        $event = new ChatMessageSent($message);
        $channels = collect($event->broadcastOn())
            ->map(static fn (object $channel): string => $channel->name)
            ->all();
        $payload = $event->broadcastWith();

        $this->assertSame(['private-chat.conversation.'.$conversation->id], $channels);
        $this->assertSame($conversation->id, $payload['conversation_id']);
        $this->assertSame($message->id, $payload['message_id']);
        $this->assertSame($sender->id, $payload['sender_id']);
        $this->assertSame('Vendor Dara', $payload['sender_name']);
        $this->assertSame('Can you check this order?', $payload['message_preview']);
        $this->assertFalse($payload['has_attachment']);
    }

    public function test_chat_typing_broadcasts_to_conversation_channel_with_sender_payload(): void
    {
        $sender = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $event = new ChatTyping(123, $sender->id);
        $channels = collect($event->broadcastOn())
            ->map(static fn (object $channel): string => $channel->name)
            ->all();
        $payload = $event->broadcastWith();

        $this->assertSame(['private-chat.conversation.123'], $channels);
        $this->assertSame(123, $payload['conversation_id']);
        $this->assertSame(123, $payload['conversationId']);
        $this->assertSame($sender->id, $payload['sender_id']);
        $this->assertSame($sender->id, $payload['senderId']);
        $this->assertSame('User Admin', $payload['sender_name']);
    }

    public function test_new_chat_message_notification_contains_deep_link_fields(): void
    {
        $sender = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $conversation = $this->createConversation($sender);
        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Please review this.',
        ]);

        $notification = new NewChatMessage($message);

        $this->assertSame('chat_message', $notification->data['type']);
        $this->assertSame((string) $conversation->id, $notification->data['conversation_id']);
        $this->assertSame((string) $message->id, $notification->data['message_id']);
        $this->assertSame('/support_chat', $notification->data['route']);
        $this->assertSame(
            'freshleaf://support-chat?conversation_id='.$conversation->id,
            $notification->data['deep_link'],
        );
    }

    public function test_chat_notification_event_broadcasts_to_user_channel_with_notification_payload(): void
    {
        $user = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $event = new ChatNotificationSent(
            user: $user,
            title: 'New message from Vendor Dara',
            body: 'Please check this.',
            data: [
                'type' => 'chat_message',
                'conversation_id' => '123',
                'message_id' => '456',
            ],
        );

        $channels = collect($event->broadcastOn())
            ->map(static fn (object $channel): string => $channel->name)
            ->all();
        $payload = $event->broadcastWith();

        $this->assertSame(['private-App.Models.User.'.$user->id], $channels);
        $this->assertSame('New message from Vendor Dara', $payload['title']);
        $this->assertSame('Please check this.', $payload['body']);
        $this->assertSame('chat_message', $payload['data']['type']);
        $this->assertSame('123', $payload['data']['conversation_id']);
    }

    public function test_sending_message_touches_conversation_and_notifies_other_participants(): void
    {
        Notification::fake();

        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $conversation = $this->createConversation($vendor, $admin);
        $conversation->forceFill(['updated_at' => now()->subHour()])->saveQuietly();

        Sanctum::actingAs($vendor);

        $this->postJson('/api/v1/conversations/'.$conversation->id.'/messages', [
            'message' => 'Hello admin',
        ])->assertOk();

        $this->assertTrue($conversation->fresh()->updated_at->greaterThan(now()->subMinute()));
        Notification::assertSentTo($admin, NewChatMessage::class);
        Notification::assertNotSentTo($vendor, NewChatMessage::class);
    }

    public function test_vendor_can_create_new_support_ticket_after_previous_ticket_is_resolved(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $resolved = $this->createConversationWithState(
            ConversationType::SUPPORT_ID,
            ConversationStatus::CLOSED_ID,
            $vendor,
            $admin,
        );

        Sanctum::actingAs($vendor);

        $response = $this->postJson('/api/v1/conversations', [
            'type' => 'support',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.type', 'support');

        $this->assertDatabaseHas('conversations', [
            'id' => $resolved->id,
            'conversation_status_id' => ConversationStatus::CLOSED_ID,
        ]);
        $this->assertSame(2, Conversation::whereHas('participants', function ($query) use ($vendor) {
            $query->where('user_id', $vendor->id);
        })->where('conversation_type_id', ConversationType::SUPPORT_ID)->count());
    }

    public function test_messages_and_typing_are_rejected_for_resolved_support_ticket(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $conversation = $this->createConversationWithState(
            ConversationType::SUPPORT_ID,
            ConversationStatus::CLOSED_ID,
            $vendor,
            $admin,
        );

        Sanctum::actingAs($vendor);

        $this->postJson('/api/v1/conversations/'.$conversation->id.'/messages', [
            'message' => 'Can I continue?',
        ])->assertStatus(422);

        $this->postJson('/api/v1/conversations/typing', [
            'conversation_id' => $conversation->id,
        ])->assertStatus(422);
    }

    public function test_direct_conversation_cannot_be_resolved_and_remains_sendable(): void
    {
        Notification::fake();

        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $consumer = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $conversation = $this->createConversationWithState(
            ConversationType::DIRECT_ID,
            ConversationStatus::OPEN_ID,
            $vendor,
            $consumer,
        );

        $this->actingAs($vendor);
        $component = new ChatInbox;
        $component->activeConversationId = $conversation->id;
        $component->resolveConversation($conversation->id);

        $this->assertSame(ConversationStatus::OPEN_ID, $conversation->fresh()->conversation_status_id);

        Sanctum::actingAs($vendor);
        $this->postJson('/api/v1/conversations/'.$conversation->id.'/messages', [
            'message' => 'Direct chat still works',
        ])->assertOk();
    }

    public function test_conversation_filters_separate_open_support_resolved_support_and_direct(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $consumer = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $openSupport = $this->createConversationWithState(ConversationType::SUPPORT_ID, ConversationStatus::OPEN_ID, $vendor, $admin);
        $resolvedSupport = $this->createConversationWithState(ConversationType::SUPPORT_ID, ConversationStatus::CLOSED_ID, $vendor, $admin);
        $direct = $this->createConversationWithState(ConversationType::DIRECT_ID, ConversationStatus::OPEN_ID, $vendor, $consumer);

        $this->actingAs($vendor);
        $component = new ChatInbox;

        $component->conversationFilter = 'support_open';
        $this->assertSame([$openSupport->id], $component->getConversations()->pluck('id')->all());

        $component->conversationFilter = 'support_resolved';
        $this->assertSame([$resolvedSupport->id], $component->getConversations()->pluck('id')->all());

        $component->conversationFilter = 'direct';
        $this->assertSame([$direct->id], $component->getConversations()->pluck('id')->all());
    }

    public function test_conversation_list_includes_unread_messages_count_for_other_senders(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $conversation = $this->createConversation($vendor, $admin);

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'content' => 'Unread one',
            'is_read' => false,
        ]);
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'content' => 'Unread two',
            'is_read' => false,
        ]);
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $vendor->id,
            'content' => 'Own unread should not count',
            'is_read' => false,
        ]);
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'content' => 'Read should not count',
            'is_read' => true,
        ]);

        $this->actingAs($vendor);
        $component = new ChatInbox;

        $listedConversation = $component->getConversations()->firstWhere('id', $conversation->id);

        $this->assertSame(2, $listedConversation->unread_messages_count);
    }

    public function test_vendor_user_type_filter_separates_admin_and_consumer_conversations(): void
    {
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $consumer = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $supportConversation = $this->createConversationWithState(
            ConversationType::SUPPORT_ID,
            ConversationStatus::OPEN_ID,
            $vendor,
            $admin,
        );
        $directConversation = $this->createConversationWithState(
            ConversationType::DIRECT_ID,
            ConversationStatus::OPEN_ID,
            $vendor,
            $consumer,
        );

        $this->actingAs($vendor);
        $component = new ChatInbox;

        $component->activeTab = 'admins';
        $this->assertSame([$supportConversation->id], $component->getConversations()->pluck('id')->all());

        $component->activeTab = 'consumers';
        $this->assertSame([$directConversation->id], $component->getConversations()->pluck('id')->all());
    }

    public function test_api_conversation_filters_return_vendor_direct_chats_with_stable_payload(): void
    {
        $consumer = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $vendor = User::factory()->create([
            'first_name' => 'Leaf',
            'last_name' => 'Vendor',
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        VendorProfile::query()->create([
            'user_id' => $vendor->id,
            'business_name' => 'Green Basket',
            'contact_phone' => '012345678',
            'is_verified' => true,
            'store_front_image' => 'vendors/green-basket.png',
        ]);
        $admin = User::factory()->create([
            'user_type_id' => UserType::ADMIN_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);

        $direct = $this->createConversationWithState(
            ConversationType::DIRECT_ID,
            ConversationStatus::OPEN_ID,
            $consumer,
            $vendor,
        );
        $support = $this->createConversationWithState(
            ConversationType::SUPPORT_ID,
            ConversationStatus::OPEN_ID,
            $consumer,
            $admin,
        );
        Message::query()->create([
            'conversation_id' => $direct->id,
            'sender_id' => $vendor->id,
            'content' => 'Fresh herbs are available.',
            'is_read' => false,
        ]);

        Sanctum::actingAs($consumer);

        $response = $this->getJson(
            '/api/v1/conversations?type=direct&participant_type=vendor'
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $direct->id)
            ->assertJsonPath('data.data.0.type', 'direct')
            ->assertJsonPath('data.data.0.status', 'open')
            ->assertJsonPath('data.data.0.other_participant.id', $vendor->id)
            ->assertJsonPath('data.data.0.other_participant.type', 'vendor')
            ->assertJsonPath('data.data.0.other_participant.business_name', 'Green Basket')
            ->assertJsonPath('data.data.0.other_participant.is_verified', true)
            ->assertJsonPath('data.data.0.latest_message.content', 'Fresh herbs are available.')
            ->assertJsonPath('data.data.0.unread_count', 1)
            ->assertJsonPath(
                'data.data.0.deep_link',
                'freshleaf://support-chat?conversation_id='.$direct->id,
            );

        $this->assertNotSame($support->id, $response->json('data.data.0.id'));
    }

    public function test_starting_vendor_direct_chat_reuses_existing_conversation_resource(): void
    {
        $consumer = User::factory()->create([
            'user_type_id' => UserType::CONSUMER_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        VendorProfile::query()->create([
            'user_id' => $vendor->id,
            'business_name' => 'Green Basket',
            'contact_phone' => '012345678',
        ]);
        $existing = $this->createConversationWithState(
            ConversationType::DIRECT_ID,
            ConversationStatus::OPEN_ID,
            $consumer,
            $vendor,
        );

        Sanctum::actingAs($consumer);

        $response = $this->postJson('/api/v1/conversations', [
            'type' => 'direct',
            'user_id' => $vendor->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $existing->id)
            ->assertJsonPath('data.type', 'direct')
            ->assertJsonPath('data.other_participant.id', $vendor->id)
            ->assertJsonPath(
                'data.deep_link',
                'freshleaf://support-chat?conversation_id='.$existing->id,
            );

        $this->assertSame(
            1,
            Conversation::where('conversation_type_id', ConversationType::DIRECT_ID)
                ->whereHas('participants', fn ($query) => $query->where('user_id', $consumer->id))
                ->whereHas('participants', fn ($query) => $query->where('user_id', $vendor->id))
                ->count()
        );
    }

    private function createConversation(User ...$participants): Conversation
    {
        return $this->createConversationWithState(
            ConversationType::SUPPORT_ID,
            ConversationStatus::OPEN_ID,
            ...$participants,
        );
    }

    private function createConversationWithState(
        int $typeId = ConversationType::SUPPORT_ID,
        int $statusId = ConversationStatus::OPEN_ID,
        User ...$participants,
    ): Conversation {
        $conversation = Conversation::query()->create([
            'conversation_type_id' => $typeId,
            'conversation_status_id' => $statusId,
        ]);

        foreach ($participants as $participant) {
            ConversationParticipant::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $participant->id,
            ]);
        }

        return $conversation;
    }
}
