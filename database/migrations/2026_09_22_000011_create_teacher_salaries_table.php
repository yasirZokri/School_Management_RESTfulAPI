<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One salary row per teacher per month: no teacher can have two salaries
 * for the same month. Amount is stored as decimal(10,2). paid_at stays
 * null until the salary is actually paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('month', 7); // e.g. "2026-09"
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_salaries');
    }
};