<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Override;

class AdminProfile extends BaseEditProfile
{
    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->required(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])->columns(2),

                Section::make('Professional Details')
                    ->relationship('adminProfile')
                    ->schema([
                        TextInput::make('job_title')
                            ->label('Job Title')
                            ->maxLength(255),
                        TextInput::make('department')
                            ->label('Department')
                            ->maxLength(255),
                        TextInput::make('office_phone')
                            ->label('Office Phone')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(3),
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
