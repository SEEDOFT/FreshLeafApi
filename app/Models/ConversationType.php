<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Table('conversation_types', key: 'id', keyType: 'int')]
#[Fillable(['name'])]
class ConversationType extends Model
{
    /** @var int */
    public const DIRECT_ID = 1;

    /** @var int */
    public const SUPPORT_ID = 2;

    protected $fillable = ['name'];

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            //
        ];
    }
}
