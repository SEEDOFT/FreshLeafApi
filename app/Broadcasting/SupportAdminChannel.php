<?php

declare(strict_types=1);

namespace App\Broadcasting;

use App\Models\User;
use App\Models\UserType;

class SupportAdminChannel
{
    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): bool
    {
        return $user->isType(UserType::ADMIN_ID);
    }
}
