<?php

namespace App\Models;

use App\Services\ApplicationService;
use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;
    use Blameable;

    public static function boot()
    {
        parent::boot();
        static::created(function (Application $application) {
            try {
                // Resolve ApplicationService dari container
                $applicationService = app(ApplicationService::class);
                // Generate application data secara otomatis
                $applicationService->generateApplicationData($application);
                // Log untuk tracking
                Log::info("Application data generated successfully for Application ID: {$application->id}");
            } catch (\Exception $e) {
                // Log error tapi jangan gagalkan proses create application
                Log::error("Failed to generate application data for Application ID: {$application->id}. Error: " . $e->getMessage());

                // Opsional: bisa throw exception jika ingin proses create gagal
                throw $e;
            }
        });

        // Opsional: Event ketika Application di-update
        static::updated(function (Application $application) {
            // Jika scholarship_id berubah, regenerate application data
            if ($application->wasChanged('scholarship_id')) {
                try {
                    $applicationService = app(ApplicationService::class);
                    $application->applicationData()->delete();
                    $applicationService->generateApplicationData($application);
                    Log::info("Application data regenerated for Application ID: {$application->id} due to scholarship change");
                } catch (\Exception $e) {
                    Log::error("Failed to regenerate application data for Application ID: {$application->id}. Error: " . $e->getMessage());
                    throw $e;
                }
            }
        });
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
    public function applicationData()
    {
        return $this->hasMany(ApplicationData::class);
    }

    public function documents()
    {
        return $this->hasManyThrough(Document::class, ApplicationData::class);
    }
    public function applicationScores()
    {
        return $this->hasMany(ApplicationScore::class);
    }
}
