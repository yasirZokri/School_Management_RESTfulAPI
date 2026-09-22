<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a TeachingAssignment model into JSON.
 */
class TeachingAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher' => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->user?->name,
                'employee_number' => $this->teacher->employee_number,
            ] : null,
            'class' => $this->class ? [
                'id' => $this->class->id,
                'name' => $this->class->name,
            ] : null,
            'subject' => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null,
            'academic_year' => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null,
        ];
    }
}
