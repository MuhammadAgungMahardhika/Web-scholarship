<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                FileUpload::make('profile_photo_path')
                    ->label('Photo')
                    ->image()
                    ->directory('profile-photos')
                    ->maxSize(1024)
                    ->columnSpanFull(),
                Repeater::make('student')
                    ->relationship()
                    ->label('Data Mahasiswa')
                    ->addActionLabel('Lengkapi data mahasiswa')
                    // ->required()
                    ->deletable(false)
                    ->schema([
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
                            ->required()
                            ->numeric(),
                        TextInput::make('parent_income')
                            ->required()
                            ->prefix('Rp.')
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->maxItems(1),
            ]);
    }
}
