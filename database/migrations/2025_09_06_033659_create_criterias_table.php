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
        Schema::create('criterias', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('scholarship_id'); // FK to scholarships (added later)
            $table->string('name', 255); // Example: GPA, income, achievement
            $table->decimal('weight', 5, 2); // Example: 0.3, 0.5

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Index
            $table->index('scholarship_id', 'idx_criteria_scholarship');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterias');
    }
};
