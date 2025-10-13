<?php

namespace App\Filament\Resources\Applications\Actions;

use App\Models\ApplicationData;
use App\Models\Enums\ApplicationDataStatusEnum;
use App\Models\Enums\ApplicationStatusEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

class ApplicationDataStatusAction
{
    public const PERMISSION_VERIFY_APPLICATION_DATA = 'verify-application-data';

    protected static function isAuthorized(string $permission): bool
    {
        return Auth::user()->can($permission);
    }

    public static function verifyApplicationData($url = null): Action
    {
        $authorized = static::isAuthorized(static::PERMISSION_VERIFY_APPLICATION_DATA);
        return     Action::make('verify-application-data')
            ->label('Verifikasi')
            ->icon('heroicon-o-pencil-square')
            ->color('info')
            ->authorize(fn($record) => $record->status === ApplicationStatusEnum::RequestVerify->value && $authorized)
            ->schema([
                Radio::make('status')
                    ->label('Status')
                    ->default(fn($record) => $record->status)
                    ->options([
                        ApplicationDataStatusEnum::Verified->value => ApplicationDataStatusEnum::Verified->label(),
                        ApplicationDataStatusEnum::Revision->value => ApplicationDataStatusEnum::Revision->label(),
                        ApplicationDataStatusEnum::Rejected->value => ApplicationDataStatusEnum::Rejected->label(),
                    ])
                    ->required()
                    ->live()->inline(),
                Textarea::make('note')
                    ->label('Catatan')
                    ->placeholder('Tambahkan catatan jika diperlukan...')
                    ->rows(3)
                    ->visible(fn(Get $get) => in_array($get('status'), [
                        ApplicationDataStatusEnum::Rejected->value,
                        ApplicationDataStatusEnum::Revision->value
                    ]))
            ])
            ->action(function (array $arguments, Repeater $component, $data, $livewire) {
                $itemData = $component->getItemState($arguments['item']);
                ApplicationData::where('id', $itemData['id'])->update([
                    'status' => $data['status'],
                    'note' => $data['note'] ?? null
                ]);
                $livewire->refreshFormData([
                    'application',
                    'applicationData'
                ]);
                Notification::make()
                    ->title('Status berhasil diupdate')
                    ->success()
                    ->send();
            })
            ->requiresConfirmation()
            ->modalHeading('Update Status')
            ->modalSubmitActionLabel('Update Status')
            ->databaseTransaction();
    }
}
