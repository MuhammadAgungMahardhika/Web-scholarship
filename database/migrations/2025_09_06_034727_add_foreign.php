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
        Schema::table('cities', function (Blueprint $table) {
            $table->foreign('province_id', 'fk_cities_province')
                ->references('id')
                ->on('provinces')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
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
            $table->foreign('province_id', 'fk_students_province')
                ->references('id')
                ->on('provinces')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('city_id', 'fk_students_cities')
                ->references('id')
                ->on('cities')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::table('scoring_scales', function (Blueprint $table) {
            $table->foreign('criteria_id', 'fk_scoring_scales_criteria')
                ->references('id')
                ->on('criterias')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
        Schema::table('scholarship_criterias', function (Blueprint $table) {
            $table->foreign('scholarship_id', 'fk_scholarship_criterias_scholarship')
                ->references('id')
                ->on('scholarships')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('criteria_id', 'fk_scholarship_criterias_criteria')
                ->references('id')
                ->on('criterias')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('scholarship_id', 'fk_applications_scholarship')
                ->references('id')
                ->on('scholarships')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('student_number', 'fk_applications_student')
                ->references('student_number')
                ->on('students')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        Schema::table('application_scores', function (Blueprint $table) {
            $table->foreign('application_id', 'fk_application_scores_application')
                ->references('id')
                ->on('applications')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('criteria_id', 'fk_application_scores_criteria')
                ->references('id')
                ->on('criterias')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::table('application_data', function (Blueprint $table) {
            $table->foreign('application_id', 'fk_application_data_application')
                ->references('id')
                ->on('applications')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('criteria_id', 'fk_application_data_criteria')
                ->references('id')
                ->on('criterias')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('application_data_id', 'fk_documents_application_data')
                ->references('id')
                ->on('application_data')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('criteria_required_documents', function (Blueprint $table) {
            $table->foreign('criteria_id', 'fk_criteria_required_documents_criteria')
                ->references('id')
                ->on('criterias')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
