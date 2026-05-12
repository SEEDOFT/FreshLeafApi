<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('payout_methods', key: 'id', keyType: 'int')]
#[Fillable(['id', 'name_en', 'name_km'])]
class PayoutMethod extends Model
{
    public const int BANK_TRANSFER_ID = 1;

    public const string BANK_TRANSFER = 'bank_transfer';

    public const int WALLET_ID = 2;

    public const string WALLET = 'wallet';

    public const int CASH_ID = 3;

    public const string CASH = 'cash';
}
