<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\ProductInventories\Schemas;

use App\Constants\StorageDirectory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput as FormTextInput;
use Illuminate\Support\HtmlString;

class AdjustStockForm
{
    public static function schema(): array
    {
        return [
            FormSelect::make('type')
                ->label(__('shared.product.adjustment_type'))
                ->options([
                    'IN' => 'Restock (In)',
                    'OUT' => 'Sold / Removed (Out)',
                    'LOSS' => 'Damage / Loss',
                    'CORRECTION' => 'Correction',
                ])
                ->required()
                ->reactive(),
            FormTextInput::make('quantity_change')
                ->label(__('shared.product.quantity_change'))
                ->helperText('Use negative numbers for stock reduction.')
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
                ->placeholder('Explain why you are adjusting the stock...')
                ->required(),
        ];
    }
}
