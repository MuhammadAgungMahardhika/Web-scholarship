<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApplicationForm
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
                Select::make('scholarship_id')
                    ->relationship('scholarship', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('student_id')
                    ->relationship('student', 'fullname')
                    ->searchable()
                    ->preload(),
                DatePicker::make('submission_date')
                    ->default(now())
                    ->required(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
