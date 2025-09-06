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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('faculty_id', 'fk_departments_faculty')
                ->references('id')
                ->on('faculties')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::table('students', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_students_user')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('faculty_id', 'fk_students_faculty')
                ->references('id')
                ->on('faculties')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('department_id', 'fk_students_department')
                ->references('id')
                ->on('departments')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::table('criterias', function (Blueprint $table) {
            $table->foreign('scholarship_id', 'fk_criterias_scholarship')
                ->references('id')
                ->on('scholarships')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('scholarship_id', 'fk_applications_scholarship')
                ->references('id')
                ->on('scholarships')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('student_id', 'fk_applications_student')
                ->references('id')
                ->on('students')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::table('application_scores', function (Blueprint $table) {
            $table->foreign('application_id', 'fk_application_scores_application')
                ->references('id')
                ->on('applications')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('criteria_id', 'fk_application_scores_criteria')
                ->references('id')
                ->on('criterias')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
