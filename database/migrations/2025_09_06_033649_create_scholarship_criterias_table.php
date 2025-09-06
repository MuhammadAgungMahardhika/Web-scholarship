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
        Schema::create('scholarship_criterias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scholarship_id');
            $table->unsignedBigInteger('criteria_id');

            $table->decimal('weight', 5, 2);
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
        Schema::dropIfExists('scholarship_criterias');
    }
};
