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
        Schema::create('application_scores', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('application_id'); // FK to applications (added later)
            $table->unsignedBigInteger('criteria_id');    // FK to criteria (added later)
            $table->integer('score'); // Score for this criteria
            $table->decimal('weight', 5, 4)->nullable();
            $table->decimal('weighted_score', 6, 4)->nullable();

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Unique index for application_id + criteria_id
            $table->unique(['application_id', 'criteria_id'], 'idx_app_score_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_scores');
    }
};
