<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileService
{
    public const array RELATIONSHIP = ['adminProfile', 'vendorProfile', 'userProfile'];

    /**
     * Update the user's profile.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $image = null): ?User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($image) {
            if ($user->image) {
                Storage::disk(config('filesystems.default'))
                    ->delete('users/'.$user->image);
            }
            $data['image'] = $this->storeUserImage($image);
        }

        $user->update(array_intersect_key($data, array_flip([
            'first_name', 'last_name', 'email', 'phone_number', 'password', 'image',
        ])));

        $user->userProfile()->firstOrCreate(['user_id' => $user->id])->update($data);

        $freshUser = $user->fresh();

        if ($freshUser) {
            $freshUser->load(self::RELATIONSHIP);
        }

        return $freshUser;
    }

    private function storeUserImage(UploadedFile $file): string
    {
        $fileName = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('users', $fileName, 'public');

        return $fileName;
    }
}
