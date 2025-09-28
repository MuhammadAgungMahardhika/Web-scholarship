<?php

namespace App\Filament\Resources\Scholarships\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                Hidden::make('id'),
                TextInput::make('name')
                    ->unique()
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
                Toggle::make('is_manual')
                    ->label('Isi bobot manual?')
                    ->default(false)
                    ->live()
                    ->dehydrated(false),
                Repeater::make('scholarshipCriterias')
                    ->relationship(modifyQueryUsing: fn($query) => $query->with('criteria'))
                    ->schema([
                        Select::make('criteria_id')
                            ->required()
                            ->hiddenLabel()
                            ->validationMessages([
                                'unique' => 'Kriteria sudah terdaftar pada beasiswa ini.',
                            ])
                            ->relationship('criteria', 'name')
                            ->preload()
                            ->searchable(),
                        TextInput::make('weight')
                            ->prefix('weight')
                            ->suffix(fn(Get $get) => $get('../../is_manual') ? 'Manual' : 'Ahp')
                            ->hiddenLabel()
                            ->readOnly(fn(Get $get) => !$get('../../is_manual'))
                            ->numeric(),
                    ])
                    ->columnSpanFull()->columns(1)->grid(3)->live(),
                TextEntry::make('total_weight')
                    ->label('Total Weight')
                    ->live()
                    ->dehydrated(false)
                    ->state(function (Get $get) {
                        // Calculate total weight secara real-time
                        $criterias = $get('scholarshipCriterias') ?? [];
                        $total = 0;

                        foreach ($criterias as $criteria) {
                            if (isset($criteria['weight']) && is_numeric($criteria['weight'])) {
                                $total += (float) $criteria['weight'];
                            }
                        }

                        return round($total, 4);
                    })
                    ->formatStateUsing(function ($state) {
                        $value = (float) $state;
                        $color = $value == 1.0 ? 'success' : ($value > 1.0 ? 'danger' : 'warning');
                        $status = $value == 1.0 ? '✓ Perfect' : ($value > 1.0 ? '⚠ Over 100%' : '⚠ Under 100%');

                        return new HtmlString(
                            '<div class="flex items-center gap-2">
                <span class="text-lg font-bold">' . number_format($value, 4) . '</span>
                <span class="text-xs px-2 py-1 rounded ' .
                                ($color === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($color === 'danger' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200')) . '">'
                                . $status . '</span>
            </div>'
                        );
                    })
                    ->columnSpanFull()

            ]);
    }
}
