<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Vendor\Resources\Wallets\Pages\ListWallets;
use App\Models\Currency;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\PayoutStatus;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\Wallet;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorWalletWithdrawalTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_vendor_can_request_withdrawal_without_payout_method_form_key(): void
    {
        $this->seedRequiredWalletData();

        $vendor = User::factory()->create([
            'user_type_id' => UserType::VENDOR_ID,
            'user_status_id' => UserStatus::ACTIVE_ID,
        ]);
        $vendor->vendorProfile()->create([
            'business_name' => 'Fresh Vendor',
            'contact_phone' => '+85510000999',
            'province' => 'Phnom Penh',
            'address' => 'Street 1',
            'is_verified' => true,
        ]);

        $wallet = Wallet::query()->create([
            'user_id' => $vendor->id,
            'currency_id' => Currency::USD_ID,
            'balance' => '50.00',
        ]);

        $this->actingAs($vendor);

        Livewire::test(ListWallets::class)
            ->callAction('requestWithdrawal', [
                'wallet_id' => $wallet->id,
                'amount' => '12.50',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('payouts', [
            'vendor_id' => $vendor->id,
            'currency_id' => Currency::USD_ID,
            'payout_method_id' => PayoutMethod::BANK_TRANSFER_ID,
            'amount' => '12.5',
            'status_id' => Payout::STATUS_PENDING,
        ]);

        $payout = Payout::query()->where('vendor_id', $vendor->id)->firstOrFail();

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'currency_id' => Currency::USD_ID,
            'wallet_transaction_type_id' => WalletTransactionType::WITHDRAWAL_ID,
            'wallet_transaction_status_id' => WalletTransactionStatus::PENDING_ID,
            'amount' => '12.50',
            'reference_id' => $payout->id,
            'reference_type' => Payout::class,
        ]);
    }

    private function seedRequiredWalletData(): void
    {
        UserStatus::upsert([
            ['id' => UserStatus::ACTIVE_ID, 'name_en' => 'Active', 'name_km' => 'សកម្ម'],
        ], ['id'], ['name_en', 'name_km']);

        UserType::upsert([
            [
                'id' => UserType::ADMIN_ID,
                'name_en' => 'Admin',
                'name_km' => 'អ្នកគ្រប់គ្រង',
            ],
            [
                'id' => UserType::VENDOR_ID,
                'name_en' => 'Vendor',
                'name_km' => 'អ្នកលក់',
            ],
        ], ['id'], ['name_en', 'name_km']);

        Currency::upsert([
            [
                'id' => Currency::USD_ID,
                'code' => Currency::USD,
                'name_en' => 'US Dollar',
                'name_km' => 'ដុល្លារអាមេរិក',
                'symbol' => '$',
            ],
        ], ['id'], ['code', 'name_en', 'name_km', 'symbol']);

        PayoutStatus::upsert([
            ['id' => Payout::STATUS_PENDING, 'name_en' => 'Pending', 'name_km' => 'រង់ចាំ'],
        ], ['id'], ['name_en', 'name_km']);

        PayoutMethod::upsert([
            [
                'id' => PayoutMethod::BANK_TRANSFER_ID,
                'name_en' => 'Bank Transfer',
                'name_km' => 'ការផ្ទេរតាមធនាគារ',
            ],
        ], ['id'], ['name_en', 'name_km']);

        WalletTransactionType::upsert([
            [
                'id' => WalletTransactionType::WITHDRAWAL_ID,
                'name_en' => 'Withdrawal',
                'name_km' => 'ដកប្រាក់',
            ],
        ], ['id'], ['name_en', 'name_km']);

        WalletTransactionStatus::upsert([
            [
                'id' => WalletTransactionStatus::PENDING_ID,
                'name_en' => 'Pending',
                'name_km' => 'រង់ចាំ',
            ],
        ], ['id'], ['name_en', 'name_km']);
    }
}
