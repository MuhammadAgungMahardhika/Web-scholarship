<?php

namespace App\Filament\Resources\LabJobResource\Actions;

use App\Models\Document;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\Enums\DocumentStatusEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

class DocumentStatusAction
{
    public const PERMISSION_VERIFY_DOCUMENT = 'verify-document';

    protected static function isAuthorized(string $permission): bool
    {
        return Auth::user()->can($permission);
    }

    public static function verifyApplicationData($url = null): Action
    {
        $authorized = static::isAuthorized(static::PERMISSION_VERIFY_DOCUMENT);
        return      Action::make('verify-document')
            ->label('Verifikasi Dokumen')
            ->icon('heroicon-o-pencil-square')
            ->color('info')
            ->authorize(fn($record) => $record->status === ApplicationStatusEnum::RequestVerify->value && $authorized)
            ->schema([
                Radio::make('status')
                    ->label('Status Dokumen')
                    ->default(fn($record) => $record->status)
                    ->options([
                        DocumentStatusEnum::Verified->value => DocumentStatusEnum::Verified->label(),
                        DocumentStatusEnum::Revision->value => DocumentStatusEnum::Revision->label(),
                        DocumentStatusEnum::Rejected->value => DocumentStatusEnum::Rejected->label(),
                    ])
                    ->required()
                    ->live()->inline(),
                Textarea::make('note')
                    ->label('Catatan')
                    ->placeholder('Tambahkan catatan jika diperlukan...')
                    ->rows(3)
                    ->visible(fn(Get $get) => in_array($get('status'), [
                        DocumentStatusEnum::Rejected->value,
                        DocumentStatusEnum::Revision->value
                    ]))
            ])
            ->action(function (array $arguments, Repeater $component, $data, $livewire) {
                $itemData = $component->getItemState($arguments['item']);
                Document::where('id', $itemData['id'])->update([
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
            ->modalHeading('Update Status Dokumen')
            ->modalSubmitActionLabel('Update Status')
            ->databaseTransaction();
    }
}
