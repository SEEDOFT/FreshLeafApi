<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('symbol')
                    ->required(),
                TextInput::make('conversion_to_base')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
