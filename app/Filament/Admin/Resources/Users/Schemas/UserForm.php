<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Filament\Forms\Components\PhoneNumberInput;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

use function filled;
use function str_starts_with;
use function strlen;
use function substr;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.user.account_info'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('admin.resources.user.first_name'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('admin.resources.user.last_name'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('admin.resources.user.email'))
                            ->email()
                            ->unique(ignoreRecord: true),
                        PhoneNumberInput::make('phone_number')
                            ->label(__('admin.resources.user.phone'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->afterStateHydrated(function (PhoneNumberInput $component, ?string $state): void {
                                if (filled($state) && str_starts_with($state, $component->getDialCode())) {
                                    $component->state(substr($state, strlen($component->getDialCode())));
                                }
                            })
                            ->rule(
                                fn (callable $get, ?Model $record): Closure => function (
                                    string $attribute,
                                    mixed $value,
                                    Closure $fail
                                ) use ($get, $record): void {
                                    $phone = '+855'.ltrim((string) preg_replace('/\s+/', '', (string) $value), '0');
                                    $userTypeId = $get('user_type_id') ?? UserType::CONSUMER_ID;

                                    $exists = User::query()
                                        ->where('phone_number', $phone)
                                        ->where('user_type_id', $userTypeId)
                                        ->when(
                                            $record !== null,
                                            fn (Builder $q) => $q->whereNot('id', $record?->getKey())
                                        )
                                        ->exists();

                                    if ($exists) {
                                        $fail(__('validation.unique', ['attribute' => 'phone number']));
                                    }
                                }),
                        TextInput::make('password')
                            ->label(__('admin.resources.user.password'))
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->confirmed()
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->label(__('admin.resources.user.password_confirmation'))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                        Select::make('user_type_id')
                            ->label(__('admin.resources.user.account_type'))
                            ->relationship('type')
                            ->getOptionLabelFromRecordUsing(fn (UserType $record) => $record->translated_name)
                            ->default(UserType::CONSUMER_ID)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),
                        Select::make('user_status_id')
                            ->label(__('admin.resources.user.account_status'))
                            ->relationship('status')
                            ->getOptionLabelFromRecordUsing(fn (UserStatus $record) => $record->translated_name)
                            ->default(UserStatus::ACTIVE_ID)
                            ->disabled(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(),
                    ]),
            ]);
    }
}
