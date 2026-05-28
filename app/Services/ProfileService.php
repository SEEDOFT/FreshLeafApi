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
    /**
     * Default relationships loaded for the updated user profile.
     *
     * @var list<string>
     */
    private const DEFAULT_RELATIONS = ['adminProfile', 'vendorProfile', 'userProfile'];

    /**
    /**
     * Update the user's profile and optionally upload a new image.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $image = null): ?User
    {
        if ($image) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
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
            $freshUser->load(self::DEFAULT_RELATIONS);
        }

        return $freshUser;
    }

    /**
     * Store uploaded user's profile image in public disk.
     *
     * @return string The relative path of the stored file
     */
    private function storeUserImage(UploadedFile $file): string
    {
        $fileName = Str::ulid().'.'.$file->getClientOriginalExtension();
        $file->storeAs(StorageDirectory::USERS, $fileName, 'public');

        return StorageDirectory::USERS.'/'.$fileName;
    }
}
