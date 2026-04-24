<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PaymentMethodType;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
