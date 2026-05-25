<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('panel_preferences', key: 'id', keyType: 'int')]
#[Fillable([
    'account_type',
    'account_id',
    'locale',
    'theme',
])]
class PanelPreference extends Model
{
    use SoftDeletes;

    /**
     * @return MorphTo<Model, $this>
     */
    public function account(): MorphTo
    {
        return $this->morphTo('account', 'account_type', 'account_id');
    }
}
