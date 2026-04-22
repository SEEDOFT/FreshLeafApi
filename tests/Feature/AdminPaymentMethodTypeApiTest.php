<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PaymentMethodType;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPaymentMethodTypeApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_perform_payment_method_type_crud(): void
    {
        $admin = $this->createAdmin(
            '+85510000201',
            'admin-payment-type@test.local'
        );

        Sanctum::actingAs($admin);

        $listResponse = $this->getJson('/api/v1/admin/payment-method-types')
            ->assertOk()
            ->assertJsonPath('status.success', true);

        $codes = \array_column($listResponse->json('data') ?? [], 'code');
        $this->assertContains('wallet', $codes);
        $this->assertContains('credit_debit', $codes);
        $this->assertContains('aba', $codes);
        $this->assertContains('acleda', $codes);
        $this->assertNotContains('paypal', $codes);
        $this->assertNotContains('stripe', $codes);

        $storeResponse = $this->postJson('/api/v1/admin/payment-method-types', [
            'code' => 'apple_pay',
            'name' => 'Apple Pay',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'apple_pay')
            ->assertJsonPath('data.name', 'Apple Pay');

        $typeId = (int) $storeResponse->json('data.id');

        $this->getJson('/api/v1/admin/payment-method-types/'.$typeId)
            ->assertOk()
            ->assertJsonPath('data.id', $typeId);

        $this->patchJson('/api/v1/admin/payment-method-types/'.$typeId, [
            'name' => 'Apple Pay Updated',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Apple Pay Updated');

        $this->putJson('/api/v1/admin/payment-method-types/'.$typeId, [
            'code' => 'apple_pay_v2',
            'name' => 'Apple Pay V2',
        ])->assertOk()
            ->assertJsonPath('data.code', 'apple_pay_v2')
            ->assertJsonPath('data.name', 'Apple Pay V2');

        $this->deleteJson('/api/v1/admin/payment-method-types/'.$typeId)
            ->assertOk()
            ->assertJsonPath('status.success', true);

        $this->assertDatabaseMissing('payment_method_types', [
            'id' => $typeId,
        ]);
    }

    public function test_vendor_cannot_access_admin_payment_method_type_endpoints(): void
    {
        $vendor = User::query()->create([
            'first_name' => 'Type',
            'last_name' => 'Vendor',
            'phone_number' => '+85510000202',
            'email' => 'vendor-payment-type@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::VENDOR,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        Sanctum::actingAs($vendor);

        $this->getJson('/api/v1/admin/payment-method-types')
            ->assertUnauthorized();
    }

    public function test_code_must_be_unique_when_creating_payment_method_type(): void
    {
        $admin = $this->createAdmin(
            '+85510000203',
            'unique-payment-type@test.local'
        );

        Sanctum::actingAs($admin);

        PaymentMethodType::query()->forceCreate([
            'id' => 99,
            'code' => 'wechat_pay',
            'name' => 'WeChat Pay',
        ]);

        $this->postJson('/api/v1/admin/payment-method-types', [
            'code' => 'wechat_pay',
            'name' => 'WeChat Pay Duplicate',
        ])->assertUnprocessable();
    }

    public function test_user_can_list_payment_method_types_for_selection(): void
    {
        $user = User::query()->create([
            'first_name' => 'Method',
            'last_name' => 'User',
            'phone_number' => '+85510000204',
            'email' => 'user-payment-type@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::USER,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user/payment-method-types')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Payment method types retrieved successfully');
    }

    public function test_core_payment_method_types_cannot_be_updated_replaced_or_deleted(): void
    {
        $admin = $this->createAdmin(
            '+85510000205',
            'core-guard-payment-type@test.local'
        );

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/admin/payment-method-types/'.PaymentMethodType::WALLET, [
            'name' => 'Wallet Updated',
        ])
            ->assertForbidden()
            ->assertJsonPath('status.message', 'Core payment method types cannot be modified');

        $this->putJson('/api/v1/admin/payment-method-types/'.PaymentMethodType::ABA, [
            'code' => 'aba',
            'name' => 'ABA Updated',
        ])
            ->assertForbidden()
            ->assertJsonPath('status.message', 'Core payment method types cannot be modified');

        $this->deleteJson('/api/v1/admin/payment-method-types/'.PaymentMethodType::ACLEDA)
            ->assertForbidden()
            ->assertJsonPath('status.message', 'Core payment method types cannot be deleted');
    }

    public function test_normalization_migration_remaps_legacy_payment_method_type_ids(): void
    {
        $user = User::query()->create([
            'first_name' => 'Legacy',
            'last_name' => 'User',
            'phone_number' => '+85510000206',
            'email' => 'legacy-map-payment-type@test.local',
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::USER,
            'user_status_id' => UserStatus::ACTIVE,
        ]);

        DB::table('payment_method_types')->upsert([
            ['id' => 6, 'code' => 'jcb', 'name' => 'JCB', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'code' => 'paypal', 'name' => 'PayPal', 'created_at' => now(), 'updated_at' => now()],
        ], ['id'], ['code', 'name', 'updated_at']);

        DB::table('payment_methods')->insert([
            [
                'user_id' => $user->id,
                'payment_method_type_id' => 6,
                'payment_method_status_id' => 1,
                'label' => 'Legacy Card',
                'card_holder_name' => 'Legacy User',
                'card_number' => '4111111111111111',
                'expiry_month' => 12,
                'expiry_year' => (int) date('Y') + 2,
                'cvv' => '123',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'payment_method_type_id' => 8,
                'payment_method_status_id' => 1,
                'label' => 'Legacy PayPal',
                'card_holder_name' => 'Legacy User',
                'card_number' => '4222222222222',
                'expiry_month' => 10,
                'expiry_year' => (int) date('Y') + 2,
                'cvv' => '321',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $migration = require database_path('migrations/2026_04_22_132126_normalize_payment_method_types_to_core_catalog.php');
        $migration->up();

        $cardTypeId = (int) DB::table('payment_methods')->where('label', 'Legacy Card')->value('payment_method_type_id');
        $paypalTypeId = (int) DB::table('payment_methods')->where('label', 'Legacy PayPal')->value('payment_method_type_id');

        $this->assertSame(PaymentMethodType::CREDIT_DEBIT, $cardTypeId);
        $this->assertSame(PaymentMethodType::WALLET, $paypalTypeId);
        $this->assertDatabaseMissing('payment_method_types', ['id' => 8]);
        $this->assertDatabaseMissing('payment_method_types', ['id' => 9]);
        $this->assertDatabaseHas('payment_method_types', ['id' => PaymentMethodType::WALLET, 'code' => 'wallet']);
        $this->assertDatabaseHas('payment_method_types', ['id' => PaymentMethodType::CREDIT_DEBIT, 'code' => 'credit_debit']);
        $this->assertDatabaseHas('payment_method_types', ['id' => PaymentMethodType::ABA, 'code' => 'aba']);
        $this->assertDatabaseHas('payment_method_types', ['id' => PaymentMethodType::ACLEDA, 'code' => 'acleda']);
    }

    private function createAdmin(string $phoneNumber, string $email): User
    {
        return User::query()->create([
            'first_name' => 'Type',
            'last_name' => 'Admin',
            'phone_number' => $phoneNumber,
            'email' => $email,
            'password' => bcrypt('password123'),
            'user_type_id' => UserType::ADMIN,
            'user_status_id' => UserStatus::ACTIVE,
        ]);
    }
}
