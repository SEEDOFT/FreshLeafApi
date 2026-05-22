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
                TextInput::make('name_km')
                    ->label(__('admin.resources.unit.name_km'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state)),
                TextInput::make('name_en')
                    ->label(__('admin.resources.unit.name_en'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state)),
                TextInput::make('symbol')
                    ->label(__('admin.resources.unit.symbol'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state)),
                TextInput::make('conversion_to_base')
                    ->label(__('admin.resources.unit.conversion'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->minValue(0)
                    ->default(1),
            ]);
    }
}
