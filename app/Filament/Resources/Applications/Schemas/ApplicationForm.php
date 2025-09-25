<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Application;
use App\Models\Document;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\Enums\DocumentStatusEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class ApplicationForm
{
    public const PERMISSION_SELECT_ALL_STUDENT =  'select-all-student-application';

    protected static function isAuthorized(string $permission): bool
    {

        return Auth::user()->can($permission);
    }

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
                        ->required()
                        ->preload(),
                    Select::make('student_id')
                        ->relationship('student', 'fullname', modifyQueryUsing: function ($query) {
                            if (static::isAuthorized(static::PERMISSION_SELECT_ALL_STUDENT)) {
                                return $query;
                            }
                            return $query->where('student_id', Auth::user()->student->id);
                        })
                        ->searchable()
                        ->required()
                        ->preload()
                        ->default(Auth::user()->student->id ?? null)
                        ->live() // Tambahkan live untuk reaktif
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: function (Unique $rule, Get $get) {
                                return $rule->where('scholarship_id', $get('scholarship_id'));
                            }
                        )->validationMessages([
                            'unique' => 'Sudah terdaftar pada beasiswa ini.',
                        ]),
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
                    ->relationship(
                        modifyQueryUsing: fn($query) => $query
                            ->with([
                                'criteria', // Eager load criteria dengan select fields
                                'documents' // Eager load documents
                            ])
                    )
                    ->schema([
                        TextEntry::make('criteria.name')->badge()->size(TextSize::Large)->color('primary'),
                        ...static::getDynamicValueComponent(),
                        Repeater::make('documents')
                            ->relationship()
                            ->itemLabel(fn($state) => $state['name'] ?? null)
                            ->hintIcon(Heroicon::InformationCircle)
                            ->hintIconTooltip('Upload dokumen yang tertera, pastikan data tidak blur dan scan asli')
                            ->extraItemActions([
                                Action::make('update-status')
                                    ->label('Update Status')
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('primary')
                                    ->authorize(fn($record) => $record->status === ApplicationStatusEnum::RequestVerify->value)
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
                                    ->databaseTransaction()
                            ])
                            ->schema([
                                Hidden::make('id'),
                                FileUpload::make('file_path')
                                    ->label('File (maksimal 2 MB)')
                                    ->required(),
                                TextInput::make('note')
                                    ->label('Komentar')
                                    ->live()
                                    ->visible(fn($record) => $record->note ? true : false),
                                TextEntry::make('status')
                                    ->size(TextSize::Large)
                                    ->icon(fn($state) => match ($state) {
                                        DocumentStatusEnum::Verified->value => Heroicon::Check,
                                        DocumentStatusEnum::Revision->value => Heroicon::PencilSquare,
                                        DocumentStatusEnum::Rejected->value => Heroicon::XCircle,
                                        default => Heroicon::Clock, // Untuk status lainnya (mis. pending)
                                    })
                                    ->badge()
                                    ->iconColor(fn($state) => DocumentStatusEnum::color($state))
                                    ->color(fn($state) => DocumentStatusEnum::color($state))
                                    ->formatStateUsing(fn($state) => DocumentStatusEnum::labels()[$state]),
                            ])->columnSpanFull()->columns(1)->grid(2)->visibleOn(['edit', 'view'])->addable(false)->deletable(false),

                    ])
                    ->columnSpanFull()->columns(2)->grid(2)->visibleOn(['edit', 'view'])->addable(false)->deletable(false)
            ]);
    }

    /**
     * Generate single dynamic value component based on criteria data_type
     */
    protected static function getDynamicValueComponent()
    {
        // Approach: Gunakan satu TextInput yang adaptif untuk number dan text
        // Kemudian tambahkan Select dan FileUpload dengan visibility
        // Ini hack terbaik untuk Filament

        return [
            // Primary component untuk text dan number
            TextInput::make('value')
                ->label('Nilai')
                ->numeric(fn($record): bool => $record->criteria->data_type  === 'number')
                ->placeholder(function ($record) {
                    return match ($record->criteria->data_type) {
                        'number' => 'Masukkan nilai numerik',
                        'text' => 'Masukkan teks',
                        default => 'Masukkan nilai'
                    };
                })
                ->required()
                ->visible(
                    fn($record): bool =>
                    in_array($record->criteria->data_type, ['number', 'text'])
                ),

            // Select component  
            Select::make('value')
                ->label('Pilihan')
                ->options(function ($record) {
                    $criteriaId = $record->criteria_id;
                    if (!$criteriaId) return [];

                    return \App\Models\ScoringScale::where('criteria_id', $criteriaId)
                        ->pluck('value', 'value')
                        ->toArray();
                })
                ->placeholder('Pilih opsi')
                ->required()
                ->visible(
                    fn($record): bool =>
                    $record->criteria->data_type === 'select'
                ),

            // File component
            FileUpload::make('value')
                ->label('Upload File')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                ->maxSize(2048)
                ->directory('criteria-files')
                ->preserveFilenames()
                ->required()
                ->visible(
                    fn($record): bool =>
                    $record->criteria->data_type === 'file'
                ),
        ];
    }
}
