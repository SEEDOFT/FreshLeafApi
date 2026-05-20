<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_km')
                    ->label(new HtmlString('<strong>'.__('admin.resources.unit.name_km').'</strong>'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state)),
                TextInput::make('name_en')
                    ->label(new HtmlString('<strong>'.__('admin.resources.unit.name_en').'</strong>'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state)),
                TextInput::make('symbol')
                    ->label(new HtmlString('<strong>'.__('admin.resources.unit.symbol').'</strong>'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state)),
                TextInput::make('conversion_to_base')
                    ->label(new HtmlString('<strong>'.__('admin.resources.unit.conversion').'</strong>'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn (mixed $state): bool => filled($state))
                    ->numeric()
                    ->minValue(0)
                    ->default(1),
            ]);
    }
}
