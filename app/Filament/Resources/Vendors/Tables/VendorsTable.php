<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors\Tables;

use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordAction('view')
            ->columns([
                TextColumn::make('vendorProfile.business_name')
                    ->label(__('admin.resources.vendor.business_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('admin.resources.vendor.owner'))
                    ->getStateUsing(static fn (User $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('phone_number')
                    ->label(__('admin.resources.vendor.phone'))
                    ->searchable(),
                TextColumn::make('status.name')
                    ->label(__('admin.resources.vendor.status'))
                    ->badge()
                    ->color(static fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Pending' => 'warning',
                        'Inactive', 'Deleted' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('vendorProfile.is_verified')
                    ->label(__('admin.resources.vendor.verified'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_status_id')
                    ->relationship('status', 'name')
                    ->label(__('admin.resources.vendor.status')),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('admin.resources.vendor.view_submission'))
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                EditAction::make(),
                Action::make('approveVendor')
                    ->label(__('admin.resources.vendor.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(static fn (User $record) => $record->vendorProfile && ! $record->vendorProfile->is_verified)
                    ->action(static function (User $record, array $data) {
                        $record->vendorProfile?->update([
                            'is_verified' => true,
                            'approved_at' => now(),
                            'approved_by_admin_id' => Auth::id(),
                            'approve_reason' => $data['note'] ?? null,
                        ]);
                        $record->update([
                            'user_type_id' => UserType::VENDOR,
                            'user_status_id' => UserStatus::ACTIVE,
                        ]);
                    })
                    ->form([
                        Textarea::make('note')
                            ->label(__('admin.resources.vendor.approval_note')),
                    ])
                    ->requiresConfirmation(),

                Action::make('rejectVendor')
                    ->label(__('admin.resources.vendor.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(static fn (User $record) => $record->vendorProfile && ! $record->vendorProfile->is_verified)
                    ->action(static function (User $record, array $data) {
                        $record->vendorProfile?->update([
                            'is_verified' => false,
                            'rejected_at' => now(),
                            'rejected_by_admin_id' => Auth::id(),
                            'reject_reason' => $data['reason'],
                        ]);
                    })
                    ->form([
                        Textarea::make('reason')
                            ->label(__('admin.resources.vendor.rejection_reason'))
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ])
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
