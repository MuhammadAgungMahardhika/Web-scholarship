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
        Schema::create('criteria_required_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('criteria_id'); // FK to scholarships (added later)
            $table->string('name', 255); // Example: GPA, income, achievement
            $table->boolean('is_required')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteria_required_documents');
    }
};
