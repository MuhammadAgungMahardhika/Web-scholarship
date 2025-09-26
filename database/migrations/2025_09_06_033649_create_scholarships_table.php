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
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name', 255)->unique(); // Scholarship name (e.g. Scholarship A, Scholarship B)
            $table->text('description')->nullable();
            $table->date('start_date'); // Application start date
            $table->date('end_date');   // Application end date
            $table->integer('quota')->nullable(); // Quota of recipients
            $table->boolean('is_active')->default(true); // Status (active/inactive)
            $table->decimal('ahp_consistency_ratio', 8, 5)->nullable();

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
