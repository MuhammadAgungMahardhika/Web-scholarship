<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Resources\Users\Schemas\UserForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->unique()
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable(),
                Select::make('faculty_id')
                    ->required()
                    ->relationship('faculty', 'name')
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('department_id', null);
                    })
                    ->searchable(),
                Select::make('department_id')
                    ->required()
                    ->relationship('department', 'name', modifyQueryUsing: function (Get $get, $query) {
                        $facultyId = $get('faculty_id');
                        $query->where('faculty_id', $facultyId);
                    })
                    ->preload()
                    ->searchable(),
                Select::make('province_id')
                    ->label('Provinsi')
                    ->relationship('province', 'name')
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('city_id', null);
                    })
                    ->searchable()
                    ->preload(),
                Select::make('city_id')
                    ->label('Kota')
                    ->relationship('city', 'name', modifyQueryUsing: function (Get $get, $query) {
                        $provinceId = $get('province_id');
                        $query->where('province_id', $provinceId);
                    })
                    ->searchable()
                    ->preload(),
                TextInput::make('student_number')
                    ->required()
                    ->unique(),
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
