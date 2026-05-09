<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
#[Table('user_cart_item_statuses', key: 'id', keyType: 'int')]
#[Fillable(['code', 'name'])]
class UserCartItemStatus extends Model
{
    public const int ACTIVE = 1;

    public const int SAVED_FOR_LATER = 2;
}
