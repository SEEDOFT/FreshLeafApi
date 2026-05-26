<?php

declare(strict_types=1);

namespace App\Broadcasting;

use App\Models\User;

class UserChannel
{
    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, string $id): bool
    {
        return $user->id === (int) $id;
    }
}
