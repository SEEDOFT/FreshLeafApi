<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table('payout_statuses', key: 'id', keyType: 'int')]
#[Fillable(['name', 'code'])]
class PayoutStatus extends Model
{
    use SoftDeletes;

    public const string PENDING = 'pending';

    public const string PROCESSING = 'processing';

    public const string COMPLETED = 'completed';

    public const string FAILED = 'failed';
}
