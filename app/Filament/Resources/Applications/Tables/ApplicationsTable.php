<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Models\Application;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\Scholarship;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApplicationsTable
{
    public const PERMISSION_VIEW_ALL_STUDENT_APPLICATION = 'view-all-student-application';
    public const PERMISSION_SELECT_ALL_STUDENT_APPLICATION =  'select-all-student-application';
    public const PERMISSION_APPROVE_APPLICATION = 'approve-application';
    protected static function isAuthorized(string $permission): bool
    {
        return Auth::user()->can($permission);
    }

    public static function configure(Table $table): Table
    {
        $studentId = Auth::user()->student?->id;
        return $table
            ->modifyQueryUsing(
                fn($query)
                => static::isAuthorized(static::PERMISSION_VIEW_ALL_STUDENT_APPLICATION)
                    ? $query
                    : ($studentId ? $query->where('student_id', $studentId) : $query->whereRaw('0=1'))
            )
            ->defaultSort('final_score', 'desc')
            ->columns([
                TextColumn::make('scholarship.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.fullname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('final_score')
                    ->numeric()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('status')->formatStateUsing(fn($state) => ApplicationStatusEnum::labels()[$state])->color(fn($state) => ApplicationStatusEnum::color($state))->badge(),
                TextColumn::make('created_by')
                    ->searchable(),
                TextColumn::make('updated_by')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('scholarship_id')
                    ->label('Beasiswa')
                    ->relationship('scholarship', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('student.faculty')
                    ->label('Fakultas')
                    ->relationship('student.faculty', 'name')
                    ->searchable()
                    ->visible(fn() => static::isAuthorized(static::PERMISSION_VIEW_ALL_STUDENT_APPLICATION))
                    ->preload(),
                SelectFilter::make('student.department')
                    ->label('Departemen')
                    ->visible(fn() => static::isAuthorized(static::PERMISSION_VIEW_ALL_STUDENT_APPLICATION))
                    ->relationship('student.department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('student_id')
                    ->label('Mahasiswa')
                    ->visible(fn() => static::isAuthorized(static::PERMISSION_VIEW_ALL_STUDENT_APPLICATION))
                    ->relationship('student', 'fullname')
                    ->searchable()
                    ->preload(),

            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Di dalam file ApplicationsTable.php

                    BulkAction::make('approve')
                        ->label(ApplicationStatusEnum::Approved->label())
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->authorize(fn() => static::isAuthorized(static::PERMISSION_APPROVE_APPLICATION))
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $validRecords = $records->filter(function ($record) {
                                return $record->status === ApplicationStatusEnum::Verified->value;
                            });

                            // Jika tidak ada aplikasi valid yang dipilih, hentikan aksi
                            if ($validRecords->isEmpty()) {
                                Notification::make()->title('Tidak Ada Aplikasi Valid')->warning()
                                    ->body('Tidak ada aplikasi dengan status "Valid" yang dipilih untuk disetujui.')->send();
                                return;
                            }

                            // --- Lanjutkan validasi kuota HANYA dengan record yang valid ---
                            $scholarshipId = $validRecords->first()->scholarship_id;
                            if ($validRecords->pluck('scholarship_id')->unique()->count() > 1) {
                                Notification::make()->title('Aksi Dibatalkan')->danger()
                                    ->body('Anda hanya bisa menyetujui aplikasi dari satu jenis beasiswa dalam satu waktu.')->send();
                                return;
                            }

                            $scholarship = Scholarship::find($scholarshipId);
                            if (!$scholarship || is_null($scholarship->quota)) {
                                Notification::make()->title('Aksi Dibatalkan')->danger()
                                    ->body("Kuota untuk beasiswa '{$scholarship->name}' belum diatur.")->send();
                                return;
                            }

                            $selectedCount = $validRecords->count();
                            $availableQuota = $scholarship->quota - $scholarship->used_quota;

                            if ($selectedCount > $availableQuota) {
                                Notification::make()->title('Kuota Terlampaui!')->danger()
                                    ->body("Sisa kuota tidak mencukupi yaitu {$availableQuota}. Anda mencoba menyetujui {$selectedCount} aplikasi.")
                                    ->persistent()->send();
                                return;
                            }
                            DB::transaction(function () use ($validRecords, $scholarship, $selectedCount) {
                                $validRecords->each->update(['status' => ApplicationStatusEnum::Approved->value]);
                                // Increment kuota terpakai di tabel scholarship
                                $scholarship->increment('used_quota', $selectedCount);
                            });
                            Notification::make()->title('Aplikasi Telah Disetujui')->success()
                                ->body("Berhasil menyetujui {$validRecords->count()} aplikasi yang valid.")->send();
                        })
                ]),
            ]);
    }
}
