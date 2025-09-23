<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Document;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\Enums\DocumentStatusEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'sm' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->components([
                Section::make([
                    Select::make('scholarship_id')
                        ->relationship('scholarship', 'name', modifyQueryUsing: fn($query) => $query->where('is_active', true))
                        ->searchable()
                        ->preload(),
                    Select::make('student_id')
                        ->relationship('student', 'fullname')
                        ->searchable()
                        ->preload()
                        ->default(Auth::user()->student->id ?? null),
                    DatePicker::make('submission_date')
                        ->default(now())
                        ->required()
                        ->readOnly(),
                    Select::make('status')
                        ->options(ApplicationStatusEnum::labels())
                        ->default(ApplicationStatusEnum::default())
                        ->required()
                        ->disabled(),
                ])->disabledOn(['edit'])->columnSpanFull()->columns(4),
                Repeater::make('applicationData')
                    ->relationship()
                    ->schema([
                        Select::make('criteria_id')
                            ->required()
                            ->relationship('criteria', 'name')
                            ->preload()
                            ->searchable()
                            ->disabledOn(['edit']),
                        TextInput::make('value')
                            ->numeric()
                            ->required(),
                        Repeater::make('documents')
                            ->relationship()
                            ->extraItemActions([
                                Action::make('update-status')
                                    ->label('Update Status')
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('primary')
                                    ->schema([
                                        Hidden::make('id'),
                                        Select::make('status')
                                            ->label('Status Dokumen')
                                            ->options([
                                                DocumentStatusEnum::Verified->value => 'Verifikasi',
                                                DocumentStatusEnum::Rejected->value => 'Tolak',
                                                DocumentStatusEnum::Revision->value => 'Butuh perbaikan',
                                            ])
                                            ->live()
                                            ->default(fn($record) => $record->status)
                                            ->required(),
                                        Textarea::make('note')
                                            ->label('Catatan')
                                            ->placeholder('Tambahkan catatan jika diperlukan...')
                                            ->rows(3)
                                            ->visible(fn(Get $get) => in_array($get('status'), [
                                                DocumentStatusEnum::Rejected->value,
                                                DocumentStatusEnum::Revision->value
                                            ]))
                                    ])
                                    ->action(function (array $data, $state) {
                                        dd($data);
                                        Document::where('id', $state['id'])
                                            ->update([
                                                'status' => $data['status'],
                                                'note' => $data['note'] ?? null,
                                            ]);
                                        Notification::make()
                                            ->title('Status berhasil diupdate')
                                            ->success()
                                            ->send();
                                    })
                                    ->requiresConfirmation()
                                    ->modalHeading('Update Status Dokumen')
                                    ->modalDescription(fn($record) => "Anda akan mengubah status dokumen: {$record->name}")
                                    ->modalSubmitActionLabel('Update Status')
                            ])
                            ->schema([
                                TextInput::make('name')
                                    ->disabled()
                                    ->suffixIcon(fn($record) => $record->status == DocumentStatusEnum::Verified->value ? Heroicon::Check : ($record->status == DocumentStatusEnum::Rejected->value ? Heroicon::XCircle : Heroicon::Clock))
                                    ->suffixIconColor(
                                        fn($record) =>  DocumentStatusEnum::color($record->status)
                                    )
                                    ->required(),
                                FileUpload::make('file_path')
                                    ->label('File Path')
                                    ->required()
                                    ->disabled(fn($record) => $record->status === DocumentStatusEnum::Verified->value),
                                // If you want to allow uploading files, consider using a FileUpload component instead
                            ])->columnSpanFull()->columns(1)->grid(2)->visibleOn(['edit', 'view'])->addable(false)->deletable(false),

                    ])
                    ->columnSpanFull()->columns(2)->grid(2)->visibleOn(['edit', 'view'])->addable(false)->deletable(false)
            ]);
    }
}
