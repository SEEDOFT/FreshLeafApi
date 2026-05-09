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
#[Table('user_cart_item_types', key: 'id', keyType: 'int')]
#[Fillable(['code', 'name'])]
class UserCartItemType extends Model
{
    public const int STANDARD = 1;

    public const int SUBSCRIPTION = 2;
}
