<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrollments connect a student to a class for one academic year.
 * The unique constraint means a student cannot be enrolled twice in the
 * same class in the same year. Status is a plain string: active /
 * completed / withdrawn.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['student_id', 'class_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};