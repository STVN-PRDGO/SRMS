<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
            $table->boolean('must_change_password')->default(false)->after('is_active');
        });

        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique('name');
            $table->index('is_current');
        });

        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_no')->unique();
            $table->string('department')->nullable();
            $table->string('contact_no')->nullable();
            $table->timestamps();
        });

        Schema::create('grading_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['school_year_id', 'name']);
            $table->index(['starts_on', 'ends_on']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('adviser_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_year_id', 'grade_level_id', 'name']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('student_no')->unique();
            $table->foreignId('section_id')->constrained()->restrictOnDelete();
            $table->date('birthdate')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_contact')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('section_id');
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->json('schedule')->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'subject_id']);
            $table->index('teacher_id');
        });

        Schema::create('grading_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 5, 2);
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grading_period_id')->constrained()->restrictOnDelete();
            $table->foreignId('grading_component_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('type');
            $table->text('description')->nullable();
            $table->decimal('max_score', 6, 2);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['class_subject_id', 'grading_period_id']);
        });

        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 6, 2)->nullable();
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['activity_id', 'student_id']);
            $table->index('student_id');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['class_subject_id', 'student_id', 'date']);
            $table->index(['student_id', 'date']);
        });

        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_grade', 5, 2);
            $table->decimal('max_grade', 5, 2);
            $table->string('letter');
            $table->string('remark');
            $table->timestamps();
        });

        Schema::create('performance_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_grade', 5, 2);
            $table->decimal('max_grade', 5, 2);
            $table->decimal('min_attendance', 5, 2)->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grading_period_id')->constrained()->cascadeOnDelete();
            $table->decimal('raw_grade', 5, 2)->nullable();
            $table->string('letter')->nullable();
            $table->string('remark')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'class_subject_id', 'grading_period_id']);
            $table->index(['student_id', 'grading_period_id']);
        });

        Schema::create('performance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grading_period_id')->constrained()->cascadeOnDelete();
            $table->decimal('general_average', 5, 2)->nullable();
            $table->decimal('attendance_rate', 5, 2)->nullable();
            $table->decimal('activity_completion_rate', 5, 2)->nullable();
            $table->foreignId('performance_level_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trend')->nullable();
            $table->text('teacher_remarks')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'grading_period_id']);
        });

        Schema::create('audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->morphs('auditable');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
        Schema::dropIfExists('performance_summaries');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('performance_levels');
        Schema::dropIfExists('grade_scales');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('scores');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('grading_components');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('grading_periods');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('school_years');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'must_change_password']);
        });
    }
};
