<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_list_own_wallets(): void
    {
        $user = User::factory()->create(['user_type_id' => UserType::USER]);
        $user->ensureDefaultWallets();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user/wallets')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallets retrieved successfully');
    }

    public function test_user_cannot_view_other_user_wallet(): void
    {
        $owner = User::factory()->create(['user_type_id' => UserType::USER]);
        $owner->ensureDefaultWallets();
        $other = User::factory()->create(['user_type_id' => UserType::USER]);

        $walletId = (int) $owner->wallets()->value('id');

        Sanctum::actingAs($other);

        $this->getJson('/api/v1/user/wallets/'.$walletId)
            ->assertNotFound();
    }

    public function test_wallet_history_is_logged_on_wallet_create_and_balance_update(): void
    {
        $user = User::factory()->create(['user_type_id' => UserType::USER]);
        $usdId = (int) Currency::query()->where('code', Currency::USD)->value('id');

        $wallet = Wallet::query()->create([
            'user_id' => $user->id,
            'currency_id' => $usdId,
            'balance' => '10.0000',
        ]);

        Sanctum::actingAs($user);

        $wallet->update(['balance' => '25.5000']);

        $this->assertDatabaseHas('wallet_histories', [
            'wallet_id' => $wallet->id,
            'action' => WalletHistory::ACTION_CREATED,
            'amount_before' => 0,
            'amount_after' => 10.0000,
        ]);

        $this->assertDatabaseHas('wallet_histories', [
            'wallet_id' => $wallet->id,
            'action' => WalletHistory::ACTION_BALANCE_UPDATED,
            'amount_before' => 10.0000,
            'amount_after' => 25.5000,
            'performed_by_user_id' => $user->id,
        ]);
    }

    public function test_vendor_can_view_wallet_history(): void
    {
        $vendor = User::factory()->create(['user_type_id' => UserType::VENDOR]);
        $usdId = (int) Currency::query()->where('code', Currency::USD)->value('id');
        $wallet = Wallet::query()->create([
            'user_id' => $vendor->id,
            'currency_id' => $usdId,
            'balance' => '5.0000',
        ]);

        Sanctum::actingAs($vendor);

        $wallet->update(['balance' => '7.0000']);

        $this->getJson('/api/v1/vendor/wallets/'.$wallet->id.'/histories')
            ->assertOk()
            ->assertJsonPath('status.success', true)
            ->assertJsonPath('status.message', 'Wallet history retrieved successfully');
    }
}
