<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Table('commission_fees', key: 'id', keyType: 'int', incrementing: false)]
#[Fillable(['rate', 'description'])]
class CommissionFee extends Model
{
    public const int ID = 1;

    /**
     * Get the singleton commission fee record.
     */
    public static function current(): self
    {
        return static::findOrFail(self::ID);
    }

    /**
     * Get the histories for the commission fee.
     *
     * @return HasMany<CommissionFeeHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(CommissionFeeHistory::class, 'commission_fee_id', 'id');
    }

    /**
     * Record a snapshot history of the current state.
     */
    public function recordHistory(): void
    {
        $this->histories()->create([
            'commission_fee_id' => $this->id,
            'rate' => $this->rate,
            'description' => $this->description,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }
}
