<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Filament\Resources\Applications\Actions\ApplicationDataStatusAction;
use App\Models\Enums\ApplicationDataStatusEnum;
use App\Models\Enums\ApplicationStatusEnum;
use App\Services\PredictionService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Str;

class ApplicationForm
{
    public const PERMISSION_SELECT_ALL_STUDENT =  'select-all-student-application';
    public const PERMISSION_VERIFY_DOCUMENT_APPLICATION =  'verify-document-application';
    public const PERMISSION_VIEW_PREDICTION_APPLICATION =  'view-prediction-application';

    protected static function isAuthorized(string $permission): bool
    {

        return Auth::user()->can($permission);
    }

    public static function configure(Schema $schema): Schema
    {
        $studentId = Auth::user()->student?->id;
        return $schema
            ->columns([
                'sm' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->components([
                TextEntry::make('ml_recommendation')
                    ->label('Prediksi')
                    ->html()

                    ->visible(fn() => static::isAuthorized(static::PERMISSION_VIEW_PREDICTION_APPLICATION))
                    ->getStateUsing(function ($record) {
                        $predictionService = new PredictionService();
                        $result = $predictionService->getPrediction($record);

                        if (!$result || isset($result['error'])) {
                            return '<span class="text-gray-500">Prediksi tidak tersedia.</span>';
                        }

                        $probability = $result['probability_approved'] * 100;
                        if ($result['prediction'] == 1) {
                            $badgeColor = 'success';
                            $text = 'Direkomendasikan Lulus';
                        } else {
                            $badgeColor = 'danger';
                            $text = 'Tidak Direkomendasikan';
                        }

                        return new HtmlString(
                            '<span class="fi-badge fi-color-' . $badgeColor . '">' . $text . '</span>' .
                                '<p class="mt-1 text-sm text-gray-500">Probabilitas Disetujui: <strong>' . number_format($probability, 2) . '%</strong></p>'
                        );
                    })->visibleOn(['view', 'edit']),
                Section::make([
                    Select::make('scholarship_id')
                        ->relationship(
                            'scholarship',
                            'name',
                            modifyQueryUsing: fn($query) => $query
                                ->where('is_active', true)
                                ->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now())
                        )
                        ->searchable()
                        ->required()
                        ->preload(),
                    Select::make('student_id')
                        ->relationship(
                            'student',
                            'fullname',
                            modifyQueryUsing: fn($query) =>
                            static::isAuthorized(static::PERMISSION_SELECT_ALL_STUDENT)
                                ? $query
                                : ($studentId
                                    ? $query->where('id', $studentId)
                                    : $query->whereRaw('0=1'))
                        )
                        ->helperText('Jika Anda tidak menemukan nama Anda, silakan lengkapi data mahasiswa pada menu profil.')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->default($studentId)
                        ->live()
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn(Unique $rule, Get $get)
                            => $rule->where('scholarship_id', $get('scholarship_id'))
                        )
                        ->validationMessages([
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
                    ->extraItemActions([
                        ApplicationDataStatusAction::verifyApplicationData()
                    ])
                    ->collapsible()
                    ->schema(fn($record) => [
                        Hidden::make('id'),
                        TextEntry::make('criteria.name')->badge()->size(TextSize::Large)->color('primary')->hiddenLabel(),
                        ...static::getDynamicValueComponentSimple(),
                        Textarea::make('note')
                            ->label('Komentar')
                            ->autosize()
                            ->live()
                            ->disabled(fn() => !static::isAuthorized(static::PERMISSION_VERIFY_DOCUMENT_APPLICATION))
                            ->visible(fn($record) => $record->note ? true : false),
                        TextEntry::make('status')
                            ->size(TextSize::Large)
                            ->icon(fn($state) => match ($state) {
                                ApplicationDataStatusEnum::Verified->value => Heroicon::Check,
                                ApplicationDataStatusEnum::Revision->value => Heroicon::PencilSquare,
                                ApplicationDataStatusEnum::Rejected->value => Heroicon::XCircle,
                                default => Heroicon::Clock, // Untuk status lainnya (mis. pending)
                            })
                            ->badge()
                            ->iconColor(fn($state) => ApplicationDataStatusEnum::color($state))
                            ->color(fn($state) => ApplicationDataStatusEnum::color($state))
                            ->formatStateUsing(fn($state) => ApplicationDataStatusEnum::labels()[$state]),

                        Repeater::make('documents')
                            ->relationship()
                            ->itemLabel(fn($state) => $state['name'] ?? null)
                            ->hintIcon(Heroicon::InformationCircle)
                            ->hintIconTooltip('Upload dokumen yang tertera, pastikan data tidak blur dan scan asli. Admin akan mengecheck dan validasi dokumen dan data yang diberikan')
                            ->schema([
                                Hidden::make('id'),
                                FileUpload::make('file_path')
                                    ->hiddenLabel()
                                    ->hint('Maksimal 2 MB')
                                    ->required(fn($record) =>  $record->is_required ? true : false),

                            ])->columnSpanFull()->columns(1)->grid(2)->visibleOn(['edit', 'view'])->visible(fn($state) => !empty($state))->addable(false)->deletable(false),

                    ])
                    ->columnSpanFull()->columns(2)->grid(3)->visibleOn(['edit', 'view'])->addable(false)->deletable(false)
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
