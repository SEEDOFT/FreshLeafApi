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
            UserSeeder::class,

            // Finance
            CurrencySeeder::class,
            ExchangeRateSeeder::class,
            PaymentStatusSeeder::class,
            PaymentTypeSeeder::class,
            PaymentMethodStatusSeeder::class,
            PaymentMethodTypeSeeder::class,
            PayoutStatusSeeder::class,
            PayoutMethodSeeder::class,
            WalletTransactionTypeSeeder::class,
            WalletTransactionStatusSeeder::class,

            // Product & Inventory
            UnitSeeder::class,
            ProductStatusSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            VendorInventoryStatusSeeder::class,
            VendorInventorySeeder::class,
            PackagingTypeSeeder::class,

            // Order & Cart
            OrderTypeSeeder::class,
            OrderStatusSeeder::class,
            CartStatusSeeder::class,

            // Notifications & System
            NotificationStatusSeeder::class,
            NotificationTypeSeeder::class,

            // Chat
            ConversationTypeSeeder::class,
            ConversationStatusSeeder::class,
        ]);
    }
}
