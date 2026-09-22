<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The teachers table. Same idea as students:
 * - user_id unique (one account = one teacher).
 * - softDeletes() so salary records always point at a valid row.
 * - employee number is unique per school, not globally.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained();
            $table->string('employee_number', 50);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'employee_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};