<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
            ['id' => UserStatus::INACTIVE_ID, 'name_en' => 'Inactive', 'name_km' => 'អសកម្ម'],
            ['id' => UserStatus::DELETED_ID, 'name_en' => 'Deleted', 'name_km' => 'បានលុប'],
            ['id' => UserStatus::PENDING_ID, 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
        ], ['id'], ['name_en', 'name_km']);

        UserType::upsert([
            ['id' => UserType::CONSUMER_ID, 'name_en' => 'User', 'name_km' => 'អ្នកប្រើប្រាស់'],
            ['id' => UserType::VENDOR_ID, 'name_en' => 'Vendor', 'name_km' => 'អ្នកលក់'],
            ['id' => UserType::ADMIN_ID, 'name_en' => 'Admin', 'name_km' => 'អ្នកគ្រប់គ្រង'],
        ], ['id'], ['name_en', 'name_km']);
    }

    private function validAddressData(): array
    {
        return [
            'label' => 'Home',
            'recipient_name' => 'John Doe',
            'phone' => '012345678',
            'address_line_1' => 'Street 1',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'postal_code' => '12000',
            'lat' => 11.5564,
            'long' => 104.9282,
        ];
    }

    public function test_list_requires_authentication(): void
    {
        $this->getJson('/api/v1/addresses')->assertUnauthorized();
    }

    public function test_create_requires_authentication(): void
    {
        $this->postJson('/api/v1/addresses', $this->validAddressData())->assertUnauthorized();
    }

    public function test_user_can_create_address(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/addresses', $this->validAddressData());

        $response->assertCreated()
            ->assertJsonPath('status.success', true)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'label',
                    'recipient_name',
                    'phone',
                    'address_line_1',
                    'city',
                    'province',
                    'postal_code',
                    'lat',
                    'long',
                ],
            ]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'label' => 'Home',
        ]);
    }

    public function test_user_can_list_their_addresses(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Address::factory()->count(3)->create(['user_id' => $user->id]);
        Address::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->getJson('/api/v1/addresses');

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_user_can_view_their_address(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/addresses/{$address->id}");

        $response->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('data.id', $address->id);
    }

    public function test_user_cannot_view_other_users_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $address = Address::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/addresses/{$address->id}")->assertNotFound();
    }

    public function test_user_can_update_address(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'label' => 'Home',
        ]);

        $response = $this->patchJson("/api/v1/addresses/{$address->id}", [
            'label' => 'Work',
            'recipient_name' => 'Jane Doe',
        ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'label' => 'WORK',
            'recipient_name' => 'Jane Doe',
        ]);
    }

    public function test_user_can_replace_address(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/addresses/{$address->id}", [
            'label' => 'Office',
            'recipient_name' => 'Bob Smith',
            'phone' => '098765432',
            'address_line_1' => 'New Street 2',
            'city' => 'Siem Reap',
            'province' => 'Siem Reap',
            'postal_code' => '13000',
            'lat' => 13.3633,
            'long' => 103.8564,
        ]);

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'label' => 'Office',
            'recipient_name' => 'Bob Smith',
            'city' => 'Siem Reap',
        ]);
    }

    public function test_user_can_delete_address(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/addresses/{$address->id}");

        $response->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertSoftDeleted('addresses', ['id' => $address->id]);
    }

    public function test_user_cannot_update_other_users_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $address = Address::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/addresses/{$address->id}", [
            'label' => 'Hacked',
        ])->assertNotFound();
    }

    public function test_user_cannot_delete_other_users_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $address = Address::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/addresses/{$address->id}")->assertNotFound();
    }

    public function test_create_address_validates_required_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/addresses', []);

        $response->assertStatus(422);
    }
}
