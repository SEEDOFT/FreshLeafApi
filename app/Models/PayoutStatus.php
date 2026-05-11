<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('payout_statuses', key: 'id', keyType: 'int')]
#[Fillable(['name', 'code'])]
class PayoutStatus extends Model
{
    public const string PENDING = 'pending';

    public const string PROCESSING = 'processing';

    public const string COMPLETED = 'completed';

    public const string FAILED = 'failed';
}
