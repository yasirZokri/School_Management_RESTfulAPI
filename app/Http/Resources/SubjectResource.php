<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a Subject model into JSON.
 */
class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'school' => $this->school ? [
                'id' => $this->school->id,
                'name' => $this->school->name,
            ] : null,
        ];
    }
}
