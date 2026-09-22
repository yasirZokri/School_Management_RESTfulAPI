<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a Lesson model into JSON, with a summary of the teaching
 * assignment it belongs to (teacher, class and subject).
 */
class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'teaching_assignment' => $this->teachingAssignment ? [
                'id' => $this->teachingAssignment->id,
                'teacher' => $this->teachingAssignment->teacher?->user?->name,
                'class' => $this->teachingAssignment->class?->name,
                'subject' => $this->teachingAssignment->subject?->name,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
