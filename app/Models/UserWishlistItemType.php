<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWishlistItemType extends Model
{
    protected $fillable = ['code', 'name'];

    public const int DEFAULT = 1;
}
