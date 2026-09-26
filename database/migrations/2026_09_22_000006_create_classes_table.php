<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Classes ("Grade 10 - A", ...). One class belongs to one school and to
 * one academic year. The model is named SchoolClass because "class" is a
 * reserved word in PHP; the table keeps the simple name `classes`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->string('name');
            $table->string('grade_level', 50);
            $table->string('section', 50)->nullable();
            $table->timestamps();

            // The same class name in the same year/school can only exist once.
            $table->unique(['school_id', 'academic_year_id', 'name', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
