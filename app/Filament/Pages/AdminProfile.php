<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Override;

class AdminProfile extends BaseEditProfile
{
    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Avatar')
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory('avatars')
                                    ->alignCenter()
                                    ->columnSpan(1),

                                Grid::make(2)
                                    ->schema([
                                        $this->getNameFormComponent(),
                                        $this->getEmailFormComponent(),
                                        TextInput::make('phone_number')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->required(),
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->compact(),

                Tabs::make('Admin Profile Tabs')
                    ->tabs([
                        Tab::make('Professional Details')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Section::make('Your Role')
                                    ->description('Manage your role and position within the company.')
                                    ->relationship('adminProfile')
                                    ->schema([
                                        TextInput::make('job_title')
                                            ->label('Job Title')
                                            ->placeholder('e.g. Operations Manager')
                                            ->maxLength(255),
                                        TextInput::make('department')
                                            ->label('Department')
                                            ->placeholder('e.g. Logistics')
                                            ->maxLength(255),
                                        TextInput::make('office_phone')
                                            ->label('Office Phone')
                                            ->tel()
                                            ->maxLength(255),
                                    ])->columns(3),
                            ]),

                        Tab::make('Security')
                            ->icon('heroicon-m-lock-closed')
                            ->schema([
                                Section::make('Change Password')
                                    ->description('Ensure your account is using a long, random password to stay secure.')
                                    ->schema([
                                        $this->getPasswordFormComponent(),
                                        $this->getPasswordConfirmationFormComponent(),
                                    ])->columns(2),
                            ]),

                        Tab::make('Application Settings')
                            ->icon('heroicon-m-cog-8-tooth')
                            ->schema([
                                Section::make('Control Panel Preferences')
                                    ->description('Customize your admin dashboard experience.')
                                    ->schema([
                                        Toggle::make('email_notifications')
                                            ->label('Receive Email Notifications')
                                            ->default(true)
                                            ->dehydrated(false),
                                        Toggle::make('sms_alerts')
                                            ->label('Receive SMS Alerts for New Vendors')
                                            ->default(true)
                                            ->dehydrated(false),
                                        TextInput::make('timezone')
                                            ->label('Preferred Timezone')
                                            ->default('Asia/Phnom_Penh')
                                            ->dehydrated(false),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    protected function getNameFormComponent(): Component
    {
        return Grid::make(2)
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
