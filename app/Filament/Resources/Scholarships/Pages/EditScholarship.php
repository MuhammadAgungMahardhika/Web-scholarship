<?php

namespace App\Filament\Resources\Scholarships\Pages;

use App\Filament\Resources\Scholarships\ScholarshipResource;
use App\Services\AhpCalculatorService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

                ->schema(function ($record, $action) {
                    $criterias = $record->criterias()->orderBy('id')->get();

                    // Jika kriteria kurang dari 2, AHP tidak bisa dilakukan
                    if ($criterias->count() < 2) {
                        return [
                            // Tampilkan pesan informasi di dalam modal
                            TextEntry::make('info')
                                ->label('Informasi')
                                ->aboveContent(new HtmlString('Anda memerlukan minimal 2 kriteria untuk dapat melakukan perhitungan AHP. Silakan tambahkan kriteria pada beasiswa ini terlebih dahulu.')),
                        ];
                    }

                    $formComponents = [];
                    // Skala Saaty untuk dropdown

                    $saatyScale = [
                        '5' => '5',
                        '3' => '3 ',
                        '1' => '1',
                        '0.333333' => '1/3', // Lebih akurat dari 0.333
                        '0.2' => '1/5',
                    ];

                    $saatyScaleInformation = [
                        '5' => '5 - Jauh L.P.',
                        '3' => '3 - Sedikit L.P.',
                        '1' => '1 - Sama Penting',
                        '0.333' => '1/3 - Sedikit Kurang Penting',
                        '0.2' => '1/5 - Jauh Kurang Penting',
                    ];

                    $scaleList = '<ul style="margin-left:16px;">';
                    foreach ($saatyScaleInformation as  $label) {
                        $scaleList .= "<li>{$label}</li>";
                    }
                    $scaleList .= '</ul>';
                    $formComponents[] = TextEntry::make('info')
                        ->columnSpanFull()
                        ->hiddenLabel()
                        ->aboveContent(new HtmlString(
                            '<div class="mb-6 p-4 bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3">📊 Panduan Skala Perbandingan AHP:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-xs text-blue-700 dark:text-blue-300">
                        <div><span class="font-mono bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded text-blue-800 dark:text-blue-200">5</span> - Jauh L.P.</div>
                        <div><span class="font-mono bg-green-100 dark:bg-green-900 px-2 py-1 rounded text-green-800 dark:text-green-200">3</span> - Sedikit L.P.</div>
                        <div><span class="font-mono bg-yellow-100 dark:bg-yellow-900 px-2 py-1 rounded text-yellow-800 dark:text-yellow-200">1</span> - Sama Penting</div>
                        <div><span class="font-mono bg-orange-100 dark:bg-orange-900 px-2 py-1 rounded text-orange-800 dark:text-orange-200">1/3</span> - Sedikit Kurang Penting</div>
                        <div><span class="font-mono bg-red-100 dark:bg-red-900 px-2 py-1 rounded text-red-800 dark:text-red-200">1/5</span> - Jauh Kurang Penting</div>
                    </div>
                    <p class="mt-3 text-xs text-blue-600 dark:text-blue-400">
                        💡 <strong>Tips:</strong> Jika semua kriteria sama penting, pilih "1" untuk semua perbandingan. 
                        Setiap kriteria akan mendapat bobot yang sama (1/' . $criterias->count() . ' = ' . number_format(1 / $criterias->count(), 6) . ').
                    </p>
                </div>'
                        ));
                    // Loop untuk membuat field perbandingan
                    foreach ($criterias as $i => $crit1) {
                        foreach ($criterias as $j => $crit2) {
                            if ($i < $j) {
                                $fieldName = "comparison_{$crit1->id}_{$crit2->id}";
                                $formComponents[] = ToggleButtons::make($fieldName)
                                    ->label("{$crit1->name} vs {$crit2->name}")
                                    ->options($saatyScale)
                                    ->default('1')
                                    ->required()
                                    ->inline()
                                    ->helperText("Seberapa penting {$crit1->name} dibanding {$crit2->name}?");
                            }
                        }
                    }

                    // Bungkus semua komponen select di dalam Grid yang responsif
                    return [
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 2,
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

                    // 1. Rekonstruksi Matriks dengan presisi yang lebih baik
                    $matrix = array_fill(0, $n, array_fill(0, $n, 0));
                    foreach ($data as $key => $value) {
                        if (strpos($key, 'comparison_') !== 0) continue;
                        list(, $id1, $id2) = explode('_', $key);
                        $i = array_search($id1, $criteriaIds);
                        $j = array_search($id2, $criteriaIds);

                        // Konversi dengan presisi lebih tinggi
                        $val = (float)$value;
                        $matrix[$i][$j] = $val;
                        $matrix[$j][$i] = 1 / $val;
                    }

                    // Diagonal matrix = 1
                    for ($k = 0; $k < $n; $k++) {
                        $matrix[$k][$k] = 1;
                    }

                    // 2. Panggil Service AHP
                    $result = $ahpService->calculate($matrix);

                    // 3. Validasi hasil
                    $totalWeight = array_sum($result['weights']);

                    // Debug logging (optional - hapus di production)
                    Log::info('AHP Calculation Result', [
                        'weights' => $result['weights'],
                        'total_weight' => $totalWeight,
                        'consistency_ratio' => $result['consistency_ratio']
                    ]);
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
                            ->body('Nilai Consistency Ratio (CR) adalah ' . number_format($result['consistency_ratio'], 4) . '. Nilai yang baik harus di bawah 0.1. CR digunakan untuk mengukur konsistensi penilaian Anda. 
            Jika nilainya di atas 0.1, berarti ada ketidaksesuaian dalam perbandingan kriteria. 
            Mohon periksa kembali input Anda.')
                            ->send();
                        $this->halt();
                    }
                })->slideOver()->modalWidth('7xl'),

        ];
    }
}
