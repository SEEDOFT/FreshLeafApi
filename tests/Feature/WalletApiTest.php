<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create(['id' => Currency::KHR_ID, 'code' => Currency::KHR]);
        Currency::factory()->create(['id' => Currency::USD_ID, 'code' => Currency::USD]);
    }

    public function test_user_wallet_list_requires_authentication(): void
    {
        $this->getJson('/api/v1/wallets')
            ->assertUnauthorized();
    }

    public function test_user_can_list_own_wallets(): void
    {
        $user = User::factory()->create(['user_type_id' => UserType::CONSUMER_ID]);
        $user->ensureDefaultWallets();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wallets')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallets retrieved successfully')
            ->assertJsonPath('data.0.currency.code', Currency::USD);
    }

    public function test_user_cannot_view_other_user_wallet(): void
    {
        $owner = User::factory()->create(['user_type_id' => UserType::CONSUMER_ID]);
        $owner->ensureDefaultWallets();
        $other = User::factory()->create(['user_type_id' => UserType::CONSUMER_ID]);

        $walletId = (int) $owner->wallets()->value('id');

        Sanctum::actingAs($other);

        $this->getJson('/api/v1/wallets/'.$walletId)
            ->assertNotFound();
    }

    public function test_user_can_view_wallet_history_with_currency_fields(): void
    {
        $user = User::factory()->create(['user_type_id' => UserType::CONSUMER_ID]);
        $usdId = (int) Currency::query()->where('code', Currency::USD)->value('id');

        $wallet = Wallet::query()->create([
            'user_id' => $user->id,
            'currency_id' => $usdId,
            'balance' => '10.0000',
        ]);

        Sanctum::actingAs($user);

        \DB::table('wallet_histories')->insert([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'currency_id' => $usdId,
            'balance' => '25.5000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/v1/wallets/'.$wallet->id.'/histories')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallet history retrieved successfully')
            ->assertJsonPath('data.0.currency.code', Currency::USD)
            ->assertJsonPath('data.0.currency_id', $usdId)
            ->assertJsonPath('data.0.wallet_id', $wallet->id)
            ->assertJsonPath('data.0.user_id', $user->id)
            ->assertJsonPath('data.0.balance', '25.5000');
    }

    public function test_vendor_can_view_wallet_history(): void
    {
        $vendor = User::factory()->create(['user_type_id' => UserType::VENDOR_ID]);
        $usdId = (int) Currency::query()->where('code', Currency::USD)->value('id');
        $wallet = Wallet::query()->create([
            'user_id' => $vendor->id,
            'currency_id' => $usdId,
            'balance' => '5.0000',
        ]);

        Sanctum::actingAs($vendor);

        \DB::table('wallet_histories')->insert([
            'wallet_id' => $wallet->id,
            'user_id' => $vendor->id,
            'currency_id' => $usdId,
            'balance' => '7.0000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/v1/wallets/'.$wallet->id.'/histories')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallet history retrieved successfully')
            ->assertJsonPath('data.0.currency.code', Currency::USD)
            ->assertJsonPath('data.0.currency_id', $usdId)
            ->assertJsonPath('data.0.balance', '7.0000');
    }

    public function test_admin_can_list_own_wallets(): void
    {
        $admin = User::factory()->create(['user_type_id' => UserType::ADMIN_ID]);
        $admin->ensureDefaultWallets();

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/wallets')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallets retrieved successfully');
    }

    public function test_admin_can_view_own_wallet(): void
    {
        $admin = User::factory()->create(['user_type_id' => UserType::ADMIN_ID]);
        $admin->ensureDefaultWallets();
        $walletId = (int) $admin->wallets()->value('id');

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/wallets/'.$walletId)
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallet retrieved successfully');
    }

    public function test_admin_can_view_own_wallet_history(): void
    {
        $admin = User::factory()->create(['user_type_id' => UserType::ADMIN_ID]);
        $usdId = (int) Currency::query()->where('code', Currency::USD)->value('id');
        $wallet = Wallet::query()->create([
            'user_id' => $admin->id,
            'currency_id' => $usdId,
            'balance' => '1.0000',
        ]);

        Sanctum::actingAs($admin);

        \DB::table('wallet_histories')->insert([
            'wallet_id' => $wallet->id,
            'user_id' => $admin->id,
            'currency_id' => $usdId,
            'balance' => '3.5000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/v1/wallets/'.$wallet->id.'/histories')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallet history retrieved successfully')
            ->assertJsonPath('data.0.currency.code', Currency::USD)
            ->assertJsonPath('data.0.currency_id', $usdId)
            ->assertJsonPath('data.0.balance', '3.5000');
    }

    public function test_admin_cannot_view_other_user_wallet(): void
    {
        $owner = User::factory()->create(['user_type_id' => UserType::CONSUMER_ID]);
        $owner->ensureDefaultWallets();
        $admin = User::factory()->create(['user_type_id' => UserType::ADMIN_ID]);

        $walletId = (int) $owner->wallets()->value('id');

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/wallets/'.$walletId)
            ->assertNotFound();
    }
}
