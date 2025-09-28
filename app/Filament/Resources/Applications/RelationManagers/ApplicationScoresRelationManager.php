<?php

namespace App\Filament\Resources\Applications\RelationManagers;

use App\Models\Enums\ApplicationStatusEnum;
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
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ApplicationScoresRelationManager extends RelationManager
{
    protected static bool $isLazy = false;
    protected static string $relationship = 'applicationScores';
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->status === ApplicationStatusEnum::Verified->value;
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('criteria_id')
                    ->required()
                    ->relationship('criteria', 'name')
                    ->preload(),
                TextInput::make('score')->numeric(),
                TextInput::make('weight')->numeric(),
                TextInput::make('weighted_score')->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('criteria.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('score')
                    ->searchable()
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()),
                TextColumn::make('weight')
                    ->searchable()
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()),
                TextColumn::make('weighted_score')
                    ->searchable()
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()),
                TextColumn::make('created_by')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
