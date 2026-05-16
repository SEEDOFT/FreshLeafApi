<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Override;

class CreateUser extends CreateRecord
{
    #[Override]
    protected static string $resource = UserResource::class;

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

            $user->userProfile()->create([
                'locale' => 'km',
                'theme' => 'system',
            ]);

            $user->ensureDefaultWallets();

            $user->ensureDefaultPaymentMethod();

            return $user;
        });

        return $user;
    }
}
