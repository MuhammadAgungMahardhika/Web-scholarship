<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'sm' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->components([
                Select::make('user_id')
                    ->required()
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable(),
                Select::make('faculty_id')
                    ->required()
                    ->relationship('faculty', 'name')
                    ->preload()
                    ->searchable(),
                Select::make('department_id')
                    ->required()
                    ->relationship('department', 'name')
                    ->preload()
                    ->searchable(),
                TextInput::make('student_number')
                    ->required(),
                TextInput::make('fullname')
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('phone_number')
                    ->tel(),
                DatePicker::make('date_of_birth'),
                TextInput::make('gpa')
                    ->numeric(),
                TextInput::make('parent_income')
                    ->numeric(),
            ]);
    }
}
