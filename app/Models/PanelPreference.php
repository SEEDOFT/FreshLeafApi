<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'account_type',
    'account_id',
    'locale',
    'theme',
])]
class PanelPreference extends Model
{
    /**
     * @return MorphTo<Model, $this>
     */
    public function account(): MorphTo
    {
        return $this->morphTo('account', 'account_type', 'account_id');
    }
}
