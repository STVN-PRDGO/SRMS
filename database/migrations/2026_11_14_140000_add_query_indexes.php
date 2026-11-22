<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->index(['class_subject_id', 'grading_period_id'], 'grades_class_period_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['class_subject_id', 'date'], 'attendances_class_date_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index(['section_id', 'status'], 'students_section_status_index');
        });

        Schema::table('performance_summaries', function (Blueprint $table) {
            $table->index(['grading_period_id', 'performance_level_id'], 'summaries_period_level_index');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->index('created_at', 'audits_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('grades_class_period_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_class_date_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_section_status_index');
        });

        Schema::table('performance_summaries', function (Blueprint $table) {
            $table->dropIndex('summaries_period_level_index');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex('audits_created_at_index');
        });
    }
};
