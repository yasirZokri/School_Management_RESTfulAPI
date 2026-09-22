<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes an AcademicYear model into JSON.
 */
class AcademicYearResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'starts_on' => $this->starts_on?->toDateString(),
            'ends_on' => $this->ends_on?->toDateString(),
            'is_current' => $this->is_current,
            'school' => $this->school ? [
                'id' => $this->school->id,
                'name' => $this->school->name,
            ] : null,
        ];
    }
}
