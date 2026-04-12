<?php

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
    public function account(): MorphTo
    {
        return $this->morphTo();
    }
}
