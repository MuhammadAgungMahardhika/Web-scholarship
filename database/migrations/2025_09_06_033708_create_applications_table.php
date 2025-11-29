<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('scholarship_id'); // FK to scholarships (added later)
            $table->string('student_number');   // FK to students (added later)
            $table->date('submission_date'); // Date of submission
            $table->tinyInteger('status')->default(1); // Application status

            $table->decimal('final_score', 10, 4)->nullable()->default(0);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('scholarship_id', 'idx_applications_scholarship');
            $table->index('student_number', 'idx_applications_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
