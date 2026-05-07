<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCartItemType extends Model
{
    protected $fillable = ['code', 'name'];

    public const int STANDARD = 1;

    public const int SUBSCRIPTION = 2;
}
