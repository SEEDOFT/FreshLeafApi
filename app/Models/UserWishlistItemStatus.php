<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('user_wishlist_item_statuses', key: 'id', keyType: 'int')]
#[Fillable(['code', 'name'])]
class UserWishlistItemStatus extends Model
{
    public const int ACTIVE = 1;

    public const int INACTIVE = 2;
}
