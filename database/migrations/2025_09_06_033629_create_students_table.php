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
        Schema::create('students', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('user_id')->unique(); // FK to users table (later)
            $table->unsignedBigInteger('faculty_id')->unique(); // FK to users table (later)
            $table->unsignedBigInteger('department_id')->unique(); // FK to users table (later)
            $table->string('student_number', 50)->unique(); // NIM
            $table->string('fullname', 50); // NIM
            $table->text('address')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->decimal('parent_income', 19, 2)->nullable();

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('student_number');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
