<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Models\Enums\ApplicationStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('final_score', 'desc')
            ->columns([
                TextColumn::make('scholarship.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.fullname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('final_score')
                    ->numeric()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('status')->formatStateUsing(fn($state) => ApplicationStatusEnum::labels()[$state])->color(fn($state) => ApplicationStatusEnum::color($state))->badge(),
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
                SelectFilter::make('scholarship_id')
                    ->label('Beasiswa')
                    ->relationship('scholarship', 'name')
                    ->preload()
                    ->searchable()
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
