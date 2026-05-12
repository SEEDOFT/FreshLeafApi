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
            // User & Auth
            UserStatusSeeder::class,
            UserTypeSeeder::class,

            // Finance
            CurrencySeeder::class,
            PaymentStatusSeeder::class,
            PaymentTypeSeeder::class,
            PaymentMethodStatusSeeder::class,
            PaymentMethodTypeSeeder::class,
            PayoutStatusSeeder::class,
            WalletTransactionStatusSeeder::class,

            // Product & Inventory
            UnitSeeder::class,
            ProductStatusSeeder::class,
            ProductCategoryStatusSeeder::class,
            VendorInventoryStatusSeeder::class,

            // Order & Cart
            OrderTypeSeeder::class,
            OrderStatusSeeder::class,
            CartStatusSeeder::class,

            // Notifications & System
            NotificationStatusSeeder::class,
            NotificationTypeSeeder::class,
        ]);
    }
}
