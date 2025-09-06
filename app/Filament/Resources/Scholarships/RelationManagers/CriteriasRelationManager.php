<?php

namespace App\Filament\Resources\Scholarships\RelationManagers;

use App\Filament\Resources\Criterias\CriteriaResource;
use App\Filament\Resources\Scholarships\ScholarshipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CriteriasRelationManager extends RelationManager
{
    protected static string $relationship = 'criterias';
    protected static ?string $parentResource = ScholarshipResource::class;
    protected static ?string $relatedResource = CriteriaResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
