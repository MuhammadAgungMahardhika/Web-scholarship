<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Checkbox;
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
                TextInput::make('username')
                    ->minLength(3)
                    ->maxLength(15)
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('roles')
                    ->label('Hak akses')
                    ->multiple()
                    ->maxItems(1)
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->dehydrated(false),
                TextInput::make('password')
                    ->password()
                    ->minLength(8)
                    ->required(),
                Checkbox::make('is_active')
                    ->label('Aktif ?')
                    ->default(true),
            ]);
    }
}
