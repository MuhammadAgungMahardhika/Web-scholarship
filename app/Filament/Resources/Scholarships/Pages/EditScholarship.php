<?php

namespace App\Filament\Resources\Scholarships\Pages;

use App\Filament\Resources\Scholarships\ScholarshipResource;
use App\Services\AhpCalculatorService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class EditScholarship extends EditRecord
{
    protected static string $resource = ScholarshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            Action::make('calculateAhpWeights')
                ->label('Kelola Bobot AHP')
                ->icon('heroicon-o-calculator')
                ->color('primary')
                ->modalWidth('3xl')
                ->modalSubmitActionLabel('Hitung & Simpan Bobot')
                // Membangun form di dalam modal secara dinamis

                ->schema(function ($record) {
                    $criterias = $record->criterias()->orderBy('id')->get();

                    // Jika kriteria kurang dari 2, AHP tidak bisa dilakukan
                    if ($criterias->count() < 2) {
                        return [
                            // Tampilkan pesan informasi di dalam modal
                            TextEntry::make('info')
                                ->label('Informasi')
                                ->content(new HtmlString('Anda memerlukan minimal 2 kriteria untuk dapat melakukan perhitungan AHP. Silakan tambahkan kriteria pada beasiswa ini terlebih dahulu.')),
                        ];
                    }

                    $formComponents = [];
                    // Skala Saaty untuk dropdown
                    $saatyScale = [
                        '9' => '9 - Mutlak Lebih Penting',
                        '7' => '7 - Sangat Jauh L.P.',
                        '5' => '5 - Jauh L.P.',
                        '3' => '3 - Sedikit L.P.',
                        '1' => '1 - Sama Penting',
                        '0.333' => '1/3 - Sedikit Kurang Penting',
                        '0.2' => '1/5 - Jauh Kurang Penting',
                        '0.143' => '1/7 - Sangat Jauh Kurang Penting',
                        '0.111' => '1/9 - Mutlak Kurang Penting',
                    ];

                    // Loop untuk membuat satu field Select untuk setiap pasangan unik
                    foreach ($criterias as $i => $crit1) {
                        foreach ($criterias as $j => $crit2) {
                            if ($i < $j) {
                                $fieldName = "comparison_{$crit1->id}_{$crit2->id}";
                                $formComponents[] = Select::make($fieldName)
                                    ->label("{$crit1->name} vs {$crit2->name}")
                                    ->options($saatyScale)
                                    ->default('1')
                                    ->required();

                                //  Radio::make($fieldName)
                                // ->label("{$crit1->name} vs {$crit2->name}")
                                // ->options($saatyScale)
                                // ->default('1')
                                // ->required()
                                // ->inline();
                            }
                        }
                    }

                    // Bungkus semua komponen select di dalam Grid yang responsif
                    return [
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 3,
                        ])->schema($formComponents)
                    ];
                })

                // Logika yang berjalan saat form di-submit
                ->action(function (array $data, $record, AhpCalculatorService $ahpService) {
                    $criterias = $record->criterias()->orderBy('id')->get();
                    if ($criterias->count() < 2) {
                        return; // Hentikan aksi jika tidak ada cukup kriteria
                    }

                    $criteriaIds = $criterias->pluck('id')->toArray();
                    $n = count($criterias);

                    // 1. Rekonstruksi Matriks dari data form
                    $matrix = array_fill(0, $n, array_fill(0, $n, 0));
                    foreach ($data as $key => $value) {
                        if (strpos($key, 'comparison_') !== 0) continue;
                        list(, $id1, $id2) = explode('_', $key);
                        $i = array_search($id1, $criteriaIds);
                        $j = array_search($id2, $criteriaIds);
                        $val = (float)$value;
                        $matrix[$i][$j] = $val;
                        $matrix[$j][$i] = 1 / $val;
                    }
                    for ($k = 0; $k < $n; $k++) {
                        $matrix[$k][$k] = 1;
                    }

                    // 2. Panggil Service AHP untuk perhitungan
                    $result = $ahpService->calculate($matrix);

                    // 3. Proses hasilnya
                    if ($result['is_consistent']) {
                        DB::transaction(function () use ($result, $criteriaIds, $record) {
                            foreach ($result['weights'] as $index => $weight) {
                                $record->criterias()
                                    ->where('criteria_id', $criteriaIds[$index])
                                    ->update(['weight' => $weight]);
                            }
                            $record->update(['ahp_consistency_ratio' => $result['consistency_ratio']]);
                        });

                        Notification::make()->title('Sukses!')->success()->body('Bobot telah dihitung dan disimpan.')->send();

                        // Me-refresh halaman untuk menampilkan data baru di Relation Manager
                        return redirect(static::getResource()::getUrl('edit', ['record' => $record]));
                    } else {
                        Notification::make()->title('Gagal! Perbandingan Tidak Konsisten')->danger()
                            ->body('Nilai Consistency Ratio (CR) adalah ' . number_format($result['consistency_ratio'], 4) . '. Nilai yang baik harus di bawah 0.1. Mohon periksa kembali input Anda.')
                            ->persistent()
                            ->send();
                    }
                })->slideOver()->modalWidth('7xl'),

        ];
    }
}
