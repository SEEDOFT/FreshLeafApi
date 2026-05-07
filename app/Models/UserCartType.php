<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCartType extends Model
{
    protected $fillable = ['code', 'name'];

    public const int DEFAULT = 1;

    public const int SCHEDULED = 2;
}
