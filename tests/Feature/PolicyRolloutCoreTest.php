<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodStatus;
use App\Models\PaymentMethodType;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PolicyRolloutCoreTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentMethodType::query()->firstOrCreate(
            ['id' => PaymentMethodType::CREDIT_DEBIT],
            ['code' => 'credit_debit', 'name' => 'Credit / Debit Card']
        );

        PaymentMethodStatus::query()->firstOrCreate(
            ['id' => PaymentMethodStatus::ACTIVE],
            ['name' => 'Active']
        );

        PaymentMethodStatus::query()->firstOrCreate(
            ['id' => PaymentMethodStatus::INACTIVE],
            ['name' => 'Inactive']
        );
    }

    public function test_public_user_register_endpoint_remains_accessible(): void
    {
        $response = $this->postJson('/api/v1/user/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '+85510000001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $createdUserId = (int) User::query()
            ->where('phone_number', '+85510000001')
            ->value('id');
        $khrCurrencyId = (int) Currency::query()
            ->where('code', Currency::KHR)
            ->value('id');
        $usdCurrencyId = (int) Currency::query()
            ->where('code', Currency::USD)
            ->value('id');

        $this->assertGreaterThan(0, $createdUserId);
        $this->assertGreaterThan(0, $khrCurrencyId);
        $this->assertGreaterThan(0, $usdCurrencyId);
        $this->assertEquals(2, Wallet::query()->where('user_id', $createdUserId)->count());
        $this->assertDatabaseHas('wallets', [
            'user_id' => $createdUserId,
            'currency_id' => $khrCurrencyId,
            'balance' => 0,
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $createdUserId,
            'currency_id' => $usdCurrencyId,
            'balance' => 0,
        ]);
    }

    public function test_protected_auth_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/user/auth/password/verify', [
            'password' => 'password123',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_verify_update_password_and_logout(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/auth/password/verify', [
            'password' => 'password123',
        ])->assertOk();

        $this->postJson('/api/v1/user/auth/password/update', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->postJson('/api/v1/user/auth/logout')->assertOk();
    }

    public function test_address_owner_can_create_and_view_but_non_owner_gets_not_found(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($owner);

        $createResponse = $this->postJson('/api/v1/user/addresses', [
            'label' => 'Home',
            'recipient_name' => 'John Doe',
            'phone' => '012345678',
            'address_line_1' => 'Street 1',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'postal_code' => '12000',
            'lat' => 11.5564,
            'long' => 104.9282,
        ])->assertCreated();

        $addressId = (int) $createResponse->json('data.id');

        $this->getJson("/api/v1/user/addresses/{$addressId}")->assertOk();

        Sanctum::actingAs($otherUser);
        $this->getJson("/api/v1/user/addresses/{$addressId}")->assertNotFound();
    }

    public function test_payment_method_owner_can_create_and_non_owner_gets_not_found(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($owner);

        $createResponse = $this->postJson('/api/v1/user/payment-methods', [
            'label' => 'Main Card',
            'payment_method_type_id' => PaymentMethodType::CREDIT_DEBIT,
            'card_holder_name' => 'John Doe',
            'card_number' => '4111111111111111',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
            'cvv' => '123',
            'is_default' => true,
            'billing_address' => 'Street 1',
            'billing_city' => 'Phnom Penh',
            'billing_state' => 'Phnom Penh',
            'billing_zip_code' => '12000',
        ])->assertCreated();

        $paymentMethodId = (int) $createResponse->json('data.id');

        $this->patchJson("/api/v1/user/payment-methods/{$paymentMethodId}", [
            'label' => 'Updated Card',
        ])->assertOk();

        Sanctum::actingAs($otherUser);
        $this->patchJson("/api/v1/user/payment-methods/{$paymentMethodId}", [
            'label' => 'Unauthorized Update',
        ])
            ->assertNotFound();
    }

    public function test_user_address_returns_not_found_for_missing_id(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/user/addresses/999999')
            ->assertNotFound()
            ->assertJsonPath('status.message', 'Address not found');
    }

    public function test_payment_method_returns_not_found_for_missing_id(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);

        $this->patchJson('/api/v1/user/payment-methods/999999', [
            'label' => 'Missing Method',
        ])
            ->assertNotFound()
            ->assertJsonPath('status.message', 'Payment method not found');
    }

    public function test_profile_policy_denies_access_to_other_user_as_not_found(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Gate::policy(User::class, UserPolicy::class);

        $response = Gate::forUser($owner)->inspect('view', [$otherUser, UserType::CONSUMER_ID]);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }

    public function test_profile_policy_denies_wrong_expected_type_as_not_found(): void
    {
        $owner = User::factory()->create();
        $owner->userProfile()->create(['preferred_language' => 'en']);

        $response = Gate::forUser($owner)->inspect('view', [$owner, UserType::VENDOR]);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }

    public function test_address_policy_denies_non_owner_as_not_found(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $address = Address::query()->create([
            'user_id' => $otherUser->id,
            'label' => 'Other Address',
            'recipient_name' => 'Other User',
            'phone' => '012345678',
            'address_line_1' => 'Street 1',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'postal_code' => '12000',
            'lat' => 11.5564,
            'long' => 104.9282,
        ]);

        $response = Gate::forUser($owner)->inspect('view', [$address, UserType::CONSUMER_ID]);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }

    public function test_address_policy_denies_wrong_expected_type_as_not_found(): void
    {
        $owner = User::factory()->create();

        $address = Address::query()->create([
            'user_id' => $owner->id,
            'label' => 'Owner Address',
            'recipient_name' => 'Owner User',
            'phone' => '012345678',
            'address_line_1' => 'Street 1',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'postal_code' => '12000',
            'lat' => 11.5564,
            'long' => 104.9282,
        ]);

        $response = Gate::forUser($owner)->inspect('view', [$address, UserType::VENDOR]);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }

    public function test_payment_method_policy_denies_non_owner_as_not_found(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $paymentMethod = PaymentMethod::query()->create([
            'user_id' => $otherUser->id,
            'payment_method_type_id' => PaymentMethodType::CREDIT_DEBIT,
            'payment_method_status_id' => PaymentMethodStatus::ACTIVE,
            'label' => 'Other Card',
            'card_holder_name' => 'Other User',
            'card_number' => '4111111111111111',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
            'cvv' => '123',
            'is_default' => false,
        ]);

        $response = Gate::forUser($owner)->inspect('view', $paymentMethod);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }

    public function test_wallet_policy_denies_non_owner_as_not_found(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $usdCurrencyId = (int) Currency::query()
            ->where('code', Currency::USD)
            ->value('id');

        $wallet = Wallet::query()->create([
            'user_id' => $otherUser->id,
            'currency_id' => $usdCurrencyId,
            'balance' => '10.00',
        ]);

        $response = Gate::forUser($owner)->inspect('view', [$wallet, UserType::CONSUMER_ID]);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }
}
