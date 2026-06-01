<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethodStatus;
use App\Models\PaymentMethodType;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        
        $users = [
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'phone_number' => '+85581968185',
                'password' => $password,
                'user_type_id' => UserType::ADMIN_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Vendor',
                'phone_number' => '+85581968185',
                'password' => $password,
                'user_type_id' => UserType::VENDOR_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Consumer',
                'phone_number' => '+85581968185',
                'password' => $password,
                'user_type_id' => UserType::CONSUMER_ID,
                'user_status_id' => UserStatus::ACTIVE_ID,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                [
                    'phone_number' => $userData['phone_number'],
                    'user_type_id' => $userData['user_type_id']
                ],
                $userData
            );

            if ($user->wasRecentlyCreated) {
                if ($user->user_type_id === UserType::ADMIN_ID) {
                    $user->adminProfile()->create([
                        'locale' => 'km',
                        'theme' => 'system',
                        'super_admin' => true,
                    ]);
                    $user->ensureDefaultWallets();
                } elseif ($user->user_type_id === UserType::VENDOR_ID) {
                    $user->vendorProfile()->create([
                        'business_name' => 'Test Vendor',
                        'contact_phone' => '+85581968185',
                        'village' => 'Test Village',
                        'commune' => 'Test Commune',
                        'district' => 'Test District',
                        'province' => 'Phnom Penh',
                        'id_card_front' => 'default.png',
                        'id_card_back' => 'default.png',
                        'store_front_image' => 'default.png',
                    ]);
        
                    $user->vendorFinancialDetails()->create([
                        'payment_method_type_id' => PaymentMethodType::ABA_ID ?? 1,
                        'payment_method_status_id' => PaymentMethodStatus::ACTIVE_ID,
                        'bank_name' => 'ABA Bank',
                        'account_name' => 'Test Vendor',
                        'account_number' => '123456789',
                        'qr_code' => 'default.png',
                    ]);
        
                    $user->ensureDefaultWallets();
                } elseif ($user->user_type_id === UserType::CONSUMER_ID) {
                    $user->userProfile()->create([
                        'locale' => 'km',
                        'theme' => 'system',
                    ]);
                    $user->ensureDefaultWallets();
                    $user->ensureDefaultPaymentMethod();
                }
            }
        }
    }
}
