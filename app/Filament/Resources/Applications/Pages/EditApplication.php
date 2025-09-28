<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\LabJobResource\Actions\ApplicationStatusAction;
use App\Models\Application;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditApplication extends EditRecord
{
    protected ?bool $hasDatabaseTransactions = true;
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ActionGroup::make([
                ApplicationStatusAction::requestVerify(static::getResource()::getUrl('view', ['record' => $this->record->id])),
            ])->label('update status')->icon(Heroicon::ArrowUp)->button()->color('success')
        ];
    }
}
