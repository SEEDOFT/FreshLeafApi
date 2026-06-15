<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorInventoryRatings;

use App\Filament\Vendor\Resources\VendorInventoryRatings\Pages\ListVendorInventoryRatings;
use App\Filament\Vendor\Resources\VendorInventoryRatings\Pages\ViewVendorInventoryRating;
use App\Filament\Vendor\Resources\VendorInventoryRatings\Schemas\VendorInventoryRatingInfolist;
use App\Filament\Vendor\Resources\VendorInventoryRatings\Tables\VendorInventoryRatingsTable;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorInventoryRating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
        return __('shared.rating.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('shared.rating.plural_label');
    }

    #[Override]
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User &&
            $user->user_type_id === UserType::VENDOR_ID &&
            $user->user_status_id === UserStatus::ACTIVE_ID &&
            (bool) $user->vendorProfile->is_verified;
    }

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        $vendor = Auth::user();

        if (! $vendor) {
            throw new AuthenticationException;
        }

        if ($vendor->user_type_id !== UserType::VENDOR_ID) {
            throw new AuthenticationException;
        }

        return parent::getEloquentQuery()
            ->whereHas('vendorInventory', fn (Builder $q) => $q->where('vendor_id', $vendor->id));
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
