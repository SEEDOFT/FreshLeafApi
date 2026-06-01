<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Units;

use App\Filament\Admin\Resources\Units\Pages\CreateUnit;
use App\Filament\Admin\Resources\Units\Pages\EditUnit;
use App\Filament\Admin\Resources\Units\Pages\ListUnits;
use App\Filament\Admin\Resources\Units\Schemas\UnitForm;
use App\Filament\Admin\Resources\Units\Tables\UnitsTable;
use App\Models\Unit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class UnitResource extends Resource
{
    #[Override]
    protected static ?string $model = Unit::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.unit.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.unit.plural_label');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return UnitForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return UnitsTable::configure($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListUnits::route('/'),
            'create' => CreateUnit::route('/create'),
            'edit' => EditUnit::route('/{record}/edit'),
        ];
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }
}
