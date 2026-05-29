<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Tables;

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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        $notProvided = __('admin.resources.general.not_provided');

        return $table
            ->stackedOnMobile()
            ->recordAction('view')
            ->recordClasses(fn (User $record) => match ($record->user_status_id) {
                UserStatus::PENDING_ID => 'bg-warning-50 dark:bg-warning-500/10 border-l-4 border-warning-500',
                UserStatus::ACTIVE_ID => 'bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400',
                UserStatus::REJECTED_ID, UserStatus::INACTIVE_ID => 'bg-danger-50 dark:bg-danger-500/10 border-l-4 border-danger-500',
                UserStatus::DELETED_ID => 'opacity-50 bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400',
                default => null,
            })
            ->columns([
                TextColumn::make('vendorProfile.business_name')

                    ->label(__('admin.resources.vendor.business_name'))
                    ->searchable()
                    ->placeholder($notProvided)
                    ->sortable(),
                TextColumn::make('name')

                    ->label(__('admin.resources.vendor.owner'))
                    ->getStateUsing(fn (User $record) => $record->fullName)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->placeholder($notProvided),
                TextColumn::make('phone_number')

                    ->label(__('admin.resources.vendor.phone'))
                    ->searchable()
                    ->placeholder($notProvided),
                TextColumn::make('type.translated_name')

                    ->label(__('admin.resources.user.type'))
                    ->badge()
                    ->placeholder($notProvided)
                    ->color('warning'),
                TextColumn::make('status.translated_name')

                    ->label(__('admin.resources.user.status'))
                    ->badge()
                    ->placeholder($notProvided)
                    ->color(fn (User $record): string => match ($record->status->id) {
                        UserStatus::ACTIVE_ID => 'success',
                        UserStatus::PENDING_ID => 'warning',
                        UserStatus::INACTIVE_ID, UserStatus::DELETED_ID, UserStatus::REJECTED_ID => 'danger',
                        default => 'secondary',
                    }),
                IconColumn::make('vendorProfile.is_verified')
                    ->label(__('admin.resources.vendor.verified'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')

                    ->label(__('admin.resources.created_at'))
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_status_id')
                    ->label(__('admin.resources.vendor.status'))
                    ->options(
                        UserStatus::all()
                            ->pluck('translated_name', 'id')
                    ),
                SelectFilter::make('user_type_id')
                    ->label(__('admin.resources.user.account_type'))
                    ->options(
                        UserType::all()
                            ->pluck('translated_name', 'id')
                    ),
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
                    ->visible(
                        fn (User $record) => $record->vendorProfile &&
                         ! $record->vendorProfile->is_verified
                         && $record->user_status_id === UserStatus::PENDING_ID
                         && $record->user_type_id === UserType::VENDOR_ID
                    )
                    ->action(function (User $record, array $data) {
                        $record->vendorProfile->update([
                            'is_verified' => true,
                            'approved_at' => Carbon::now(),
                            'approved_by_admin_id' => Auth::id(),
                            'approve_reason' => $data['note'] ?? null,
                        ]);
                        $record->update([
                            'user_type_id' => UserType::VENDOR_ID,
                            'user_status_id' => UserStatus::ACTIVE_ID,
                        ]);
                    })
                    ->form([
                        Textarea::make('note')
                            ->label(__('admin.resources.vendor.approval_note'))
                            ->required(),
                    ])
                    ->requiresConfirmation(),
                Action::make('rejectVendor')
                    ->label(__('admin.resources.vendor.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (User $record) => $record->vendorProfile &&
                        ! $record->vendorProfile->is_verified
                        && $record->user_status_id === UserStatus::PENDING_ID
                        && $record->user_type_id === UserType::VENDOR_ID
                    )
                    ->action(function (User $record, array $data) {
                        $record->update([
                            'user_type_id' => UserType::VENDOR_ID,
                            'user_status_id' => UserStatus::REJECTED_ID,
                        ]);
                        $record->vendorProfile->update([
                            'is_verified' => false,
                            'rejected_at' => Carbon::now(),
                            'rejected_by_admin_id' => Auth::id(),
                            'reject_reason' => $data['reason'],
                        ]);
                    })
                    ->form([
                        Textarea::make('reason')
                            ->label(__('admin.resources.vendor.rejection_reason'))
                            ->required(),
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
