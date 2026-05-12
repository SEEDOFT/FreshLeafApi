<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $symbol
 * @property numeric $conversion_to_base
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table('units', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['id', 'name_en', 'name_km', 'symbol', 'conversion_to_base'])]
#[UseFactory(UnitFactory::class)]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'name_en' => 'string',
            'name_km' => 'string',
            'symbol' => 'string',
            'conversion_to_base' => 'float',
        ];
    }
}
