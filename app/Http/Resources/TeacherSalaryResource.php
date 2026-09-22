<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a TeacherSalary model into JSON, including a short summary
 * of the teacher it belongs to.
 */
class TeacherSalaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'month' => $this->month,
            'paid_at' => $this->paid_at?->toISOString(),
            'teacher' => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->user?->name,
                'employee_number' => $this->teacher->employee_number,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
