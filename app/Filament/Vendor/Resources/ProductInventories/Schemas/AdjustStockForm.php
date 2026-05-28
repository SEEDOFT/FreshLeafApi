<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Schemas;

use App\Constants\StorageDirectory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput as FormTextInput;
use Filament\Schemas\Components\Component;

class AdjustStockForm
{
    /**
     * @return array<int, Component>
     */
    public static function schema(): array
    {
        return [
            FormSelect::make('type')
                ->label(__('shared.product.adjustment_type'))
                ->options([
                    'IN' => __('shared.product.adjustment_type_in'),
                    'OUT' => __('shared.product.adjustment_type_out'),
                    'LOSS' => __('shared.product.adjustment_type_loss'),
                    'CORRECTION' => __('shared.product.adjustment_type_correction'),
                ])
                ->required()
                ->reactive(),
            FormTextInput::make('quantity_change')
                ->label(__('shared.product.quantity_change'))
                ->helperText(__('shared.product.stock_reduction_hint'))
                ->numeric()
                ->required(),
            FileUpload::make('proof_image_path')
                ->label(__('shared.product.proof_photo'))
                ->image()
                ->directory(StorageDirectory::INVENTORY_ADJUSTMENTS)
                ->visibility('public')
                ->required(fn ($get) => \in_array($get('type'), ['IN', 'LOSS'])),
            Textarea::make('notes')
                ->label(__('shared.product.reason'))
                ->placeholder(__('shared.product.adjustment_reason_placeholder'))
                ->required(),
        ];
    }
}
