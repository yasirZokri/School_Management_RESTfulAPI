<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes an Enrollment model into JSON.
 */
class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'student' => $this->student ? [
                'id' => $this->student->id,
                'name' => $this->student->user?->name,
                'student_number' => $this->student->student_number,
            ] : null,
            'class' => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
                'grade_level' => $this->class->grade_level,
            ] : null,
            'academic_year' => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null,
        ];
    }
}
