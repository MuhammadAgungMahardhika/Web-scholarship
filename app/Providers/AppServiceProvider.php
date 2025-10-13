<?php

namespace App\Providers;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Table::configureUsing(fn(Table $table) => $table
            ->recordActions([], position: RecordActionsPosition::BeforeColumns)
            ->modifyUngroupedRecordActionsUsing(fn(Action $action) => $action->iconButton())
            ->striped()
            ->paginationMode(PaginationMode::Simple)
            ->deferFilters(false));

        $googleMapsAPi = config('global.google_maps_api_key');


        FilamentAsset::registerScriptData([
            'baseUrl' => url(''),
        ]);;
    }
}
