<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The students table.
 *
 * - user_id is unique: one user account belongs to one student.
 * - softDeletes() adds a `deleted_at` column. "Deleting" a student only
 *   marks the row as deleted; the record is never removed, so their payment
 *   history stays valid.
 * - a student number can repeat across schools, but never inside one school.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained();
            $table->string('student_number', 50);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'student_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
