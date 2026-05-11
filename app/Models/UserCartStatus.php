<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('user_cart_statuses', key: 'id', keyType: 'int')]
#[Fillable(['code', 'name'])]
class UserCartStatus extends Model
{
    public const int ACTIVE = 1;

    public const int ABANDONED = 2;

    public const int CONVERTED = 3;
}
