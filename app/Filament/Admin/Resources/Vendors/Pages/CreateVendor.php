<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Pages;

use App\Filament\Admin\Resources\Vendors\VendorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Override;

class CreateVendor extends CreateRecord
{
    #[Override]
    protected static string $resource = VendorResource::class;

    #[Override]
    protected function handleRecordCreation(array $data): User
    {
        /** @var User $user */
        $user = DB::transaction(static function () use ($data): User {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? null,
                'email_verified_at' => null,
                'phone_number' => $data['phone_number'],
                'phone_number_verified_at' => null,
                'password' => Hash::make($data['password']),
                'user_type_id' => $data['user_type_id'],
                'user_status_id' => $data['user_status_id'],
            ]);

            $user->vendorProfile()->create([
                'locale' => 'km',
                'theme' => 'system',
                'business_name' => $data['business_name'],
                'city' => $data['city'],
                'province' => $data['province'],
                'address' => $data['address'],
                'contact_phone' => $data['contact_phone'],
                'id_card_front' => $data['id_card_front'],
                'id_card_back' => $data['id_card_back'],
                'store_front_image' => $data['store_front_image'],
                'organic_certificate_url' => $data['organic_certificate_url'],
                'bank_name' => $data['bank_name'],
                'bank_account_number' => $data['bank_account_number'],
                'bank_account_name' => $data['bank_account_name'],
                'bank_qr_code' => $data['bank_qr_code'],
            ]);

            $user->ensureDefaultWallets();

            return $user;
        });

        return $user;
    }
}
