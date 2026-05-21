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
    protected static bool $canCreateAnother = false;

    #[Override]
    public function mount(): void
    {
        parent::mount();

        if (session()->has('create_vendor_form_state')) {
            $this->form->fill(session()->get('create_vendor_form_state'));
        }
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'data.')) {
            session()->put('create_vendor_form_state', $this->form->getRawState());
        }
    }

    #[Override]
    protected function handleRecordCreation(array $data): User
    {
        return DB::transaction(static function () use ($data): User {
            return User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? null,
                'phone_number' => $data['phone_number'],
                'image' => 'user.png',
                'password' => Hash::make($data['password']),
                'user_type_id' => $data['user_type_id'],
                'user_status_id' => $data['user_status_id'],
            ]);
        });
    }

    protected function afterCreate(): void
    {
        $this->getRecord()->ensureDefaultWallets();
        session()->forget('create_vendor_form_state');
    }
}
