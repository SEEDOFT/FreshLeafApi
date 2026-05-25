<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property string|null $translated_name
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[Table('packaging_types', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km'])]
class PackagingType extends Model
{
    use SoftDeletes;

    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::currentLocale()};
    }
}
