<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('payout_methods', key: 'id')]
#[Fillable(['name', 'code'])]
class PayoutMethod extends Model
{
    public const BANK_TRANSFER = 'bank_transfer';
    public const WALLET = 'wallet';
    public const CASH = 'cash';
}
