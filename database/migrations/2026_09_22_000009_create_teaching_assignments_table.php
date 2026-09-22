<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teaching assignments: "teacher X teaches subject Y to class Z in year W".
 * The unique constraint stops the same assignment being created twice.
 * Form Requests make sure teacher/class/subject/academic year all belong
 * to the same school (see docs - cross-school validation).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->timestamps();

            // Shorter name because the auto-generated index name would be
            // too long for MySQL (max 64 characters).
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'academic_year_id'], 'teaching_assignments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};