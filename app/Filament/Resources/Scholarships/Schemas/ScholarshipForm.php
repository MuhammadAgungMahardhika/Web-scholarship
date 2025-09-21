<?php

namespace App\Filament\Resources\Scholarships\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScholarshipForm
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
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                TextInput::make('quota')
                    ->numeric(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Repeater::make('scolarshipCriterias')
                    ->relationship()
                    ->schema([
                        Select::make('criteria_id')
                            ->required()
                            ->relationship('criteria', 'name')
                            ->preload()
                            ->searchable(),
                        TextInput::make('weight')
                            ->required()
                            ->numeric(),
                    ])
                    ->columnSpanFull()->columns(2)->grid(3)

            ]);
    }
}
