<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPaymentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherSalaryController;
use App\Http\Controllers\TeachingAssignmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

/*
| Versioned API routes for the school management system.
| 'auth' uses the existing Breeze session login for now. All routes
| are protected: an unauthenticated request gets a 401 JSON response.
*/
Route::middleware(['auth'])->prefix('/v1')->group(function () {
    Route::apiResource('schools', SchoolController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('teachers', TeacherController::class);
    Route::apiResource('academic-years', AcademicYearController::class);
    Route::apiResource('classes', SchoolClassController::class);
    Route::apiResource('subjects', SubjectController::class);
    Route::apiResource('enrollments', EnrollmentController::class);
    Route::apiResource('teaching-assignments', TeachingAssignmentController::class);
    Route::apiResource('lessons', LessonController::class);
    Route::apiResource('teacher-salaries', TeacherSalaryController::class);
    Route::apiResource('student-payments', StudentPaymentController::class);
});

require __DIR__.'/auth.php';
