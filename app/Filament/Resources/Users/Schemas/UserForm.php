<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('roles')
                    ->label('Hak akses')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->dehydrated(false),
                TextInput::make('password')
                    ->password()
                    ->required(),
            ]);
    }
}
