<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\StorageDirectory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
        if ($image) {
            if ($user->image) {
                Storage::disk('public')
                    ->delete(StorageDirectory::USERS.'/'.$user->image);
            }
            $data['image'] = $this->storeUserImage($image);
        }

        $user->update(array_intersect_key(
            $data, array_flip([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'password',
                'image',
            ]))
        );

        $user->userProfile()->update([
            'locale' => $data['locale'] ?? $user->userProfile->locale,
            'theme' => $data['theme'] ?? $user->userProfile->theme,
        ]);

        $freshUser = $user->fresh();

        if ($freshUser) {
            $freshUser->load(self::RELATIONSHIP);
        }

        return $freshUser;
    }

    /**
     * Store uploaded user's profile image in public disk.
     */
    private function storeUserImage(UploadedFile $file): string
    {
        $fileName = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->storeAs(StorageDirectory::USERS, $fileName, 'public');

        return $fileName;
    }
}
