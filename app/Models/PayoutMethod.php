<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('payout_methods', key: 'id', keyType: 'int')]
#[Fillable(['name', 'code'])]
class PayoutMethod extends Model
{
    public const string BANK_TRANSFER = 'bank_transfer';

    public const string WALLET = 'wallet';

    public const string CASH = 'cash';
}
