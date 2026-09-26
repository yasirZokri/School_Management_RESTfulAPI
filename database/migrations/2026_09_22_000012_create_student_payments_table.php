<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A payment made by a student. purpose and payment_method are plain
 * strings - the allowed values are enforced by the Form Requests, not
 * by the database (so adding a new value later only needs a code change).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('purpose', 50);           // e.g. tuition, books
            $table->timestamp('paid_at');            // when the money came in
            $table->string('payment_method', 50);    // e.g. cash, card
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_payments');
    }
};
