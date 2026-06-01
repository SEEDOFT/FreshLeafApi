<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Table('conversation_statuses', key: 'id', keyType: 'int')]
#[Fillable(['name'])]
class ConversationStatus extends Model
{
    /** @var int */
    public const OPEN_ID = 1;

    /** @var int */
    public const CLOSED_ID = 2;

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
