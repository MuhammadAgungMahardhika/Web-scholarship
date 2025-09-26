<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Application;
use App\Models\Document;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\Enums\CriteriaDataTypeEnum;
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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Str;

class ApplicationForm
{
    public const PERMISSION_SELECT_ALL_STUDENT =  'select-all-student-application';
    public const PERMISSION_VERIFY_DOCUMENT =  'verify-document-application';

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
                    ->schema(fn($record) => [
                        TextEntry::make('criteria.name')->badge()->size(TextSize::Large)->color('primary')->hiddenLabel(),
                        ...static::getDynamicValueComponentSimple(),
                        Repeater::make('documents')
                            ->relationship()
                            ->itemLabel(fn($state) => $state['name'] ?? null)
                            ->hintIcon(Heroicon::InformationCircle)
                            ->hintIconTooltip('Upload dokumen yang tertera, pastikan data tidak blur dan scan asli. Admin akan mengecheck dan validasi dokumen dan data yang diberikan')
                            ->extraItemActions([
                                Action::make('verify-document')
                                    ->label('Verifikasi Dokumen')
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('info')
                                    ->authorize(fn($record) => $record->status === ApplicationStatusEnum::RequestVerify->value && true)
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
                                    ->hiddenLabel()
                                    ->hint('Maksimal 2 MB')
                                    ->required(fn($record) => $record->is_required ? true : false),
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
    protected static function getDynamicValueComponentSimple()
    {
        return [
            Grid::make(1)
                ->schema(function ($record): array {
                    $dataType = $record?->criteria?->data_type;
                    return match ($dataType) {
                        'number' => [
                            TextInput::make('value')
                                ->hiddenLabel()
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->placeholder('Masukkan nilai')
                                ->required(),
                        ],

                        'text' => [
                            TextInput::make('value')
                                ->hiddenLabel()
                                ->label('Nilai Teks')
                                ->maxLength(500)
                                ->placeholder('Masukkan teks')
                                ->helperText('Maksimal 500 karakter')
                                ->required(),
                        ],

                        'select' => [
                            Radio::make('value')
                                ->label('Pilihan')
                                ->hiddenLabel()
                                ->options(function () use ($record) {
                                    $criteriaId = $record?->criteria_id;
                                    if (!$criteriaId) return [];

                                    return \App\Models\ScoringScale::where('criteria_id', $criteriaId)
                                        ->orderBy('value')
                                        ->pluck('value', 'value')
                                        ->toArray();
                                })
                                ->inline()
                                ->required(),
                        ],

                        'file' => [
                            FileUpload::make('value')
                                ->label('Upload File')
                                ->acceptedFileTypes(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'])
                                ->maxSize(5120)
                                ->directory("criteria-files/" . Str::slug($record?->criteria?->name ?? 'criteria'))
                                ->preserveFilenames()
                                ->downloadable()
                                ->helperText('Format: PDF, JPG, PNG, DOC, DOCX. Maksimal 5MB')
                                ->required(),
                        ],

                        default => [
                            TextInput::make('value')
                                ->hiddenLabel()
                                ->placeholder('Masukkan nilai')
                                ->required(),
                        ],
                    };
                })
                ->key('dynamicValueField'),
        ];
    }
}
