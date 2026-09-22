<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lessons belong to a teaching assignment (so the teacher, class, subject
 * and academic year are all implied by the assignment - no separate
 * teacher_id/subject_id on this table).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};