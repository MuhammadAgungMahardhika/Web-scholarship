<?php
// app/Console/Commands/ExportTrainingData.php
namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Enums\ApplicationStatusEnum;
use Illuminate\Console\Command;
use League\Csv\Writer; // jalankan: composer require league/csv

class ExportTrainingData extends Command
{
    protected $signature = 'ml:export-data';
    protected $description = 'Export historical application data for ML training.';

    public function handle()
    {
        $this->info('Mengekspor data untuk training...');
        $path = storage_path('app/ml/training_data.csv');
        $writer = Writer::createFromPath($path, 'w+');

        // Header CSV (ini akan menjadi fitur model Anda)
        $headers = ['final_score', 'status'];
        $writer->insertOne($headers);

        // Ambil aplikasi yang statusnya sudah final
        $applications = Application::whereIn('status', [ApplicationStatusEnum::Approved->value, ApplicationStatusEnum::Rejected->value])
            ->with('student')->get();

        foreach ($applications as $app) {
            if (!$app->student) continue; // Lewati jika data student tidak ada
            $writer->insertOne([
                $app->final_score,
                $app->status === ApplicationStatusEnum::Approved->value ? 1 : 0, // Target (1=approved, 0=rejected)
            ]);
        }

        $this->info("Data berhasil diekspor ke {$path}");
        return 0;
    }
}
