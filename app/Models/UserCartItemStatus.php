<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCartItemStatus extends Model
{
    protected $fillable = ['code', 'name'];

    public const int ACTIVE = 1;

    public const int SAVED_FOR_LATER = 2;
}
