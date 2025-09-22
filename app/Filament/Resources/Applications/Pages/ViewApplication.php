<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\LabJobResource\Actions\ApplicationStatusAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ActionGroup::make([
                ApplicationStatusAction::requestVerify(),
                ApplicationStatusAction::verify(),
                ApplicationStatusAction::reject()
            ])->label('update status')->icon(Heroicon::ArrowUp)->button()->color('success')
        ];
    }
}
