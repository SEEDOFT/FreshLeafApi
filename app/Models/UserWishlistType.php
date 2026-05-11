<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('user_wishlist_types', key: 'id', keyType: 'int')]
#[Fillable(['code', 'name'])]
class UserWishlistType extends Model
{
    public const int DEFAULT = 1;

    public const int SHARED = 2;
}
