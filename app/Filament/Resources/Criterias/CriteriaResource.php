<?php

namespace App\Filament\Resources\Criterias;

use App\Filament\Resources\Criterias\Pages\ManageCriterias;
use App\Models\Criteria;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CriteriaResource extends Resource
{
    protected static ?string $model = Criteria::class;
    protected static string | \UnitEnum | null $navigationGroup = "Master";
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Section::make([
                    Repeater::make('scoringScales')
                        ->relationship()
                        ->schema([
                            TextInput::make('value')
                                ->required(),
                            TextInput::make('score')
                                ->required()
                                ->numeric(),
                        ])
                        ->columnSpanFull()->columns(2)->grid(3)
                ])->columnSpan(2),
                Section::make([
                    Repeater::make('criteriaRequiredDocuments')
                        ->relationship()
                        ->schema([
                            TextInput::make('name') // Example: GPA, income, achievement
                                ->required(),
                        ])->columnSpanFull()->columns(1)->grid(3)
                ])->columnSpan(2)
            ]);
    }

    // public static function infolist(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             TextEntry::make('name'),
    //             TextEntry::make('created_by'),
    //             TextEntry::make('updated_by'),
    //             TextEntry::make('created_at')
    //                 ->dateTime(),
    //             TextEntry::make('updated_at')
    //                 ->dateTime(),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('scoringScales.value')
                    ->listWithLineBreaks()
                    ->searchable(),
                TextColumn::make('criteriaRequiredDocuments.name')
                    ->listWithLineBreaks()
                    ->searchable(),
                TextColumn::make('created_by')
                    ->searchable(),
                TextColumn::make('updated_by')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCriterias::route('/'),
        ];
    }
}
