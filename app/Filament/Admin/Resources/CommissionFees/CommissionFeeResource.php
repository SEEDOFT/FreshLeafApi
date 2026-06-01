<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CommissionFees;

use App\Filament\Admin\Resources\CommissionFees\Pages\ManageCommissionFee;
use App\Models\CommissionFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

class CommissionFeeResource extends Resource
{
    #[Override]
    protected static ?string $model = CommissionFee::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.commission_fee');
    }

    #[Override]
    public static function getModelLabel(): string
    {
        return __('admin.commission_fee.model_label');
    }

    #[Override]
    protected static ?string $slug = 'commission-fee';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ManageCommissionFee::route('/'),
        ];
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }
}
