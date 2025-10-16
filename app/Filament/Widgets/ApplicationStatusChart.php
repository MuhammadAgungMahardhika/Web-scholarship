<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Enums\ApplicationStatusEnum;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ApplicationStatusChart extends ChartWidget
{
    protected  ?string $heading = 'Distribusi Status Aplikasi';
    protected  ?string $description = 'Jumlah total aplikasi berdasarkan statusnya.';
    protected static ?int $sort = 2; // Atur urutan widget di dashboard

    protected function getType(): string
    {
        // Anda bisa mengubahnya menjadi 'bar' jika lebih suka bar chart
        return 'doughnut';
    }

    protected function getData(): array
    {
        // 1. Ambil data dari database: Group by status dan hitung jumlahnya
        $applicationCounts = Application::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status'); // Hasilnya: [1 => 10, 2 => 5, ...]

        // 2. Siapkan array untuk data chart
        $chartData = [];
        $chartLabels = [];
        $chartColors = [];

        // 3. Gunakan Enum sebagai sumber kebenaran untuk label dan warna
        $statuses = ApplicationStatusEnum::labels();

        // 4. Loop melalui semua kemungkinan status dari Enum
        foreach ($statuses as $value => $label) {
            // Tambahkan label ke chart
            $chartLabels[] = $label;

            // Tambahkan data hitungan (atau 0 jika tidak ada)
            $chartData[] = $applicationCounts[$value] ?? 0;

            // Tambahkan warna dari Enum
            $chartColors[] = ApplicationStatusEnum::cssColor($value);
        }

        // 5. Kembalikan data dalam format yang dimengerti oleh ChartWidget
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Aplikasi',
                    'data' => $chartData,
                    'backgroundColor' => $chartColors,
                ],
            ],
            'labels' => $chartLabels,
        ];
    }
}
