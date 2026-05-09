<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class AdjustmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'adjustments';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema; // Read-only via Infolist/View
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.resources.product.adjustment_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'IN' => 'success',
                        'OUT' => 'info',
                        'LOSS' => 'danger',
                        'CORRECTION' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('quantity_change')
                    ->label(__('admin.resources.product.quantity_change'))
                    ->numeric(decimalPlaces: 2)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                ImageColumn::make('proof_image_path')
                    ->label(__('admin.resources.product.proof'))
                    ->circular(),
                TextColumn::make('user.name')
                    ->label(__('admin.resources.user.label'))
                    ->getStateUsing(fn ($record) => "{$record->user?->first_name} {$record->user?->last_name}"),
                TextColumn::make('reason')
                    ->label(__('admin.resources.product.reason'))
                    ->limit(30),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->infolist(fn (Schema $infolist): Schema => $infolist
                        ->schema([
                            Section::make(__('admin.resources.product.adjustment_detail'))
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label(__('admin.resources.created_at'))
                                        ->dateTime(),
                                    TextEntry::make('user.name')
                                        ->label(__('admin.resources.user.label'))
                                        ->getStateUsing(fn ($record) => "{$record->user?->first_name} {$record->user?->last_name}"),
                                    TextEntry::make('type')
                                        ->label(__('admin.resources.product.adjustment_type'))
                                        ->badge(),
                                    TextEntry::make('quantity_change')
                                        ->label(__('admin.resources.product.quantity_change'))
                                        ->numeric(),
                                    TextEntry::make('reason')
                                        ->label(__('admin.resources.product.reason'))
                                        ->columnSpanFull(),
                                    ImageEntry::make('proof_image_path')
                                        ->label(__('admin.resources.product.proof_photo'))
                                        ->columnSpanFull(),
                                ]),
                        ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
