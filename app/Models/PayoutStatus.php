<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_km
 * @property-read string $name
 * @property-read string|null $translated_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table('payout_statuses', key: 'id', keyType: 'int')]
#[Fillable(['id', 'name_en', 'name_km'])]
class PayoutStatus extends Model
{
    use SoftDeletes;

    public const int PENDING_ID = 1;

    public const int PAID_ID = 2;

    public const int FAILED_ID = 3;

    public function getNameAttribute(): string
    {
        return $this->name_en;
    }

    public function getTranslatedNameAttribute(): ?string
    {
        return $this->{'name_'.App::getLocale()};
    }

    public function getColor(): string
    {
        return match ($this->id) {
            self::PENDING_ID => 'warning',
            self::PAID_ID => 'success',
            self::FAILED_ID => 'danger',
            default => 'gray',
        };
    }
}
