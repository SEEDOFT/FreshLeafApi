<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCartStatus extends Model
{
    protected $fillable = ['code', 'name'];

    public const int ACTIVE = 1;

    public const int ABANDONED = 2;

    public const int CONVERTED = 3;
}
