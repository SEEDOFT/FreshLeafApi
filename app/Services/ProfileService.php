<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileService
{
    /**
     * Persist profile data.
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
                Storage::disk(config('filesystems.default'))->delete('users/'.$user->image);
            }
            $data['image'] = $this->storeUserImage($image);
        }

        $user->update(array_intersect_key($data, array_flip([
            'first_name', 'last_name', 'email', 'phone_number', 'password', 'image',
        ])));

        // Update specific profile
        match ($user->user_type_id) {
            UserType::ADMIN => $user->adminProfile()->firstOrCreate(['user_id' => $user->id])->update($data),
            UserType::VENDOR => $user->vendorProfile()->firstOrCreate(['user_id' => $user->id])->update($data),
            UserType::USER => $user->userProfile()->firstOrCreate(['user_id' => $user->id])->update($data),
            default => null,
        };

        $freshUser = $user->fresh();
        if ($freshUser) {
            $freshUser->load(['adminProfile', 'vendorProfile', 'userProfile']);
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
