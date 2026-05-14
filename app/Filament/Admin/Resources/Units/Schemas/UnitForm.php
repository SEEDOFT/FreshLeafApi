<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.resources.unit.name'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                TextInput::make('symbol')
                    ->label(__('admin.resources.unit.symbol'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                TextInput::make('conversion_to_base')
                    ->label(__('admin.resources.unit.conversion'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->default(1),
            ]);
    }
}
