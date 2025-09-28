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
        Schema::create('documents', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('application_data_id'); // FK to applications (added later)
            $table->string('name', 100); // e.g., Transcript, Letter of Recommendation
            $table->string('file_path', 255)->nullable(); // File storage path
            $table->boolean('is_required');
            $table->tinyInteger('status');
            $table->string('note', 255)->nullable(); // Optional note about the document
            $table->timestamp('uploaded_at')->useCurrent(); // Default CURRENT_TIMESTAMP

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
