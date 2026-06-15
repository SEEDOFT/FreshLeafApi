<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventoryRatings;

use App\Filament\Admin\Resources\VendorInventoryRatings\Pages\ListVendorInventoryRatings;
use App\Filament\Admin\Resources\VendorInventoryRatings\Pages\ViewVendorInventoryRating;
use App\Filament\Admin\Resources\VendorInventoryRatings\Schemas\VendorInventoryRatingInfolist;
use App\Filament\Admin\Resources\VendorInventoryRatings\Tables\VendorInventoryRatingsTable;
use App\Models\VendorInventoryRating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class VendorInventoryRatingResource extends Resource
{
    #[Override]
    protected static ?string $model = VendorInventoryRating::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    #[Override]
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.shop');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.resources.rating.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.rating.plural_label');
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return VendorInventoryRatingInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return VendorInventoryRatingsTable::configure($table);
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
            'index' => ListVendorInventoryRatings::route('/'),
            'view' => ViewVendorInventoryRating::route('/{record}'),
        ];
    }
}
