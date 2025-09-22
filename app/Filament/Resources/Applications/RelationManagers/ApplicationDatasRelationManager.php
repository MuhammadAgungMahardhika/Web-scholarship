<?php

namespace App\Filament\Resources\Applications\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApplicationDatasRelationManager extends RelationManager
{
    protected static bool $isLazy = false;
    protected static string $relationship = 'applicationData';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('criteria_id')
                    ->relationship('criteria', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabledOn(['edit']),
                TextInput::make('value')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('criteria.name')
                    ->searchable(),
                TextColumn::make('value')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
