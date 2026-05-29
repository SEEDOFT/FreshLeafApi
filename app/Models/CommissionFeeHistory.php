<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

#[Table('commission_fee_histories', key: 'id', keyType: 'int')]
#[Fillable(['commission_fee_id', 'rate', 'description'])]
class CommissionFeeHistory extends Model
{
    use SoftDeletes;

    /**
     * Get the commission fee that this history belongs to.
     *
     * @return BelongsTo<CommissionFee, $this>
     */
    public function commissionFee(): BelongsTo
    {
        return $this->belongsTo(CommissionFee::class);
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
