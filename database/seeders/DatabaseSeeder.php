<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserStatusSeeder::class,
            UserTypeSeeder::class,
            DefaultSuperAdminSeeder::class,
            PaymentMethodTypeSeeder::class,
            PaymentMethodStatusSeeder::class,
            ProductCategoryStatusSeeder::class,
            VendorInventoryStatusSeeder::class,
            UserCartStatusSeeder::class,
            UserCartTypeSeeder::class,
            UserCartItemStatusSeeder::class,
            UserCartItemTypeSeeder::class,
            UserWishlistStatusSeeder::class,
            UserWishlistTypeSeeder::class,
            UserWishlistItemStatusSeeder::class,
            UserWishlistItemTypeSeeder::class,
            // OperationUserSeeder::class,
            // SupplierSeeder::class,
            ProductStatusSeeder::class,
            ProductTypeSeeder::class,
            UnitSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            ProductSampleSeeder::class,
            ProductVariantSeeder::class,
            ProductDiscountSeeder::class,
            ExchangeRateSeeder::class,
            CommissionSettingSeeder::class,
            WalletTransactionSeeder::class,
        ]);

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);
    }
}
