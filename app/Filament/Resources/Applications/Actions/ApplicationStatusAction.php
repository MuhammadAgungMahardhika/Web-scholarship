<?php

namespace App\Filament\Resources\LabJobResource\Actions;

use App\Models\Application;
use App\Models\Enums\ApplicationStatusEnum;
use App\Services\ApplicationService;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ApplicationStatusAction
{
    public const PERMISSION_REQUEST_VERIFY_APPLICATION =  'request-verify-application';
    public const PERMISSION_VERIFY_APPLICATION = 'verify-application';
    public const PERMISSION_REVISION_APPLICATION = 'revision-application';
    public const PERMISSION_REJECT_APPLICATION = 'reject-application';

    protected static function isAuthorized(string $permission): bool
    {
        return Auth::user()->can($permission);
    }

    public static function requestVerify($url = null): Action
    {
        $authorized = static::isAuthorized(static::PERMISSION_REQUEST_VERIFY_APPLICATION);
        return    Action::make(ApplicationStatusEnum::RequestVerify->label())
            ->icon(Heroicon::OutlinedClock)
            ->color('success')
            ->authorize(fn(Application $record) => in_array($record->status, [ApplicationStatusEnum::Draft->value, ApplicationStatusEnum::RevisionNeeded->value])  && $authorized)
            ->requiresConfirmation()
            ->action(function (Application $record, ApplicationService $service) use ($url) {
                try {
                    DB::transaction(function () use ($record, $service) {
                        $service->updateApplicationStatus($record, ApplicationStatusEnum::RequestVerify);
                    });
                    Notification::make()->title('Status aplikasi berubah menjadi request verifikasi.')->success()->send();
                    if ($url) {
                        return redirect()->to($url);
                    }
                } catch (Exception $e) {
                    Notification::make()
                        ->title('Gagal mengubah status aplikasi')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                    Log::error('Gagal mengubah status aplikasi', [
                        'application_id' => $record->id,
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id(),
                    ]);
                }
            });
    }
    public static function verify(): Action
    {
        $authorized = static::isAuthorized(static::PERMISSION_VERIFY_APPLICATION);
        return    Action::make(ApplicationStatusEnum::Verified->label())
            ->icon(Heroicon::OutlinedDocumentCheck)
            ->color('success')
            ->authorize(fn(Application $record) => $record->status === ApplicationStatusEnum::RequestVerify->value && $authorized)
            ->requiresConfirmation()
            ->action(function (Application $record, ApplicationService $service) {
                try {
                    DB::transaction(function () use ($record, $service) {
                        $service->updateApplicationStatus($record, ApplicationStatusEnum::Verified);
                    });
                    Notification::make()->title('Status aplikasi berubah menjadi valid.')->success()->send();
                } catch (Exception $e) {
                    Notification::make()
                        ->title('Gagal mengubah status aplikasi')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                    Log::error('Gagal mengubah status aplikasi', [
                        'application_id' => $record->id,
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id(),
                    ]);
                }
            });
    }
    public static function revision(): Action
    {
        // $authorized = static::isAuthorized(static::PERMISSION_REVISION_APPLICATION);
        $authorized = true;
        return    Action::make(ApplicationStatusEnum::RevisionNeeded->label())
            ->icon(Heroicon::OutlinedPencil)
            ->color('warning')
            ->authorize(fn(Application $record) => $record->status === ApplicationStatusEnum::RequestVerify->value && $authorized)
            ->requiresConfirmation()
            ->action(function (Application $record, ApplicationService $service) {
                try {
                    DB::transaction(function () use ($record, $service) {
                        $service->updateApplicationStatus($record, ApplicationStatusEnum::RevisionNeeded);
                    });
                    Notification::make()->title('Status aplikasi berubah menjadi butuh revisi.')->success()->send();
                } catch (Exception $e) {
                    Notification::make()
                        ->title('Gagal mengubah status aplikasi')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                    Log::error('Gagal mengubah status aplikasi', [
                        'application_id' => $record->id,
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id(),
                    ]);
                }
            });
    }
    public static function reject(): Action
    {
        $authorized = static::isAuthorized(static::PERMISSION_REJECT_APPLICATION);
        return    Action::make(ApplicationStatusEnum::Rejected->label())
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->authorize(fn(Application $record) => $record->status === ApplicationStatusEnum::RequestVerify->value && $authorized)
            ->requiresConfirmation()
            ->action(function (Application $record, ApplicationService $service) {
                try {
                    DB::transaction(function () use ($record, $service) {
                        $service->updateApplicationStatus($record, ApplicationStatusEnum::Rejected);
                    });
                    Notification::make()->title('Status aplikasi berubah menjadi tidak valid.')->success()->send();
                } catch (Exception $e) {
                    Notification::make()
                        ->title('Gagal mengubah status aplikasi')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                    Log::error('Gagal mengubah status aplikasi', [
                        'application_id' => $record->id,
                        'error' => $e->getMessage(),
                        'user_id' => Auth::id(),
                    ]);
                }
            });
    }
}
