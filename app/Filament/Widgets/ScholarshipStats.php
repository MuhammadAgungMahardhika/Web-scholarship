<?php

namespace App\Filament\Widgets;

use App\Models\Scholarship;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ScholarshipStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Mendapatkan tanggal hari ini
        $today = Carbon::now();

        // Menghitung total kuota dan kuota terpakai untuk persentase
        $totalQuota = Scholarship::sum('quota');
        $totalUsedQuota = Scholarship::sum('used_quota');
        $percentageFilled = ($totalQuota > 0) ? ($totalUsedQuota / $totalQuota) * 100 : 0;

        return [
            // Stat #1: Menampilkan jumlah total beasiswa
            Stat::make('Total Beasiswa', Scholarship::count())
                ->description('Jumlah semua program beasiswa yang terdaftar')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            // Stat #2: Menampilkan jumlah beasiswa yang sedang aktif dibuka
            Stat::make('Beasiswa Sedang Dibuka', Scholarship::where('is_active', true)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->count())
                ->description('Beasiswa dalam periode pendaftaran aktif')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            // Stat #3: Menampilkan jumlah total kuota yang sudah terisi
            Stat::make('Total Kuota Terisi', $totalUsedQuota)
                ->description(number_format($percentageFilled, 1) . '% dari total kuota terisi')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}
