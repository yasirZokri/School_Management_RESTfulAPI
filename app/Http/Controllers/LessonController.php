<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LessonController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Lesson::class);

        return LessonResource::collection(
            Lesson::with(['teachingAssignment.teacher.user', 'teachingAssignment.class', 'teachingAssignment.subject'])->paginate()
        );
    }

    public function store(StoreLessonRequest $request): JsonResponse
    {
        $this->authorize('create', Lesson::class);

        $lesson = Lesson::create($request->validated());

        return LessonResource::make(
            $lesson->load(['teachingAssignment.teacher.user', 'teachingAssignment.class', 'teachingAssignment.subject'])
        )->response()->setStatusCode(201);
    }

    public function show(Lesson $lesson): LessonResource
    {
        $this->authorize('view', $lesson);

        return LessonResource::make(
            $lesson->load(['teachingAssignment.teacher.user', 'teachingAssignment.class', 'teachingAssignment.subject'])
        );
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): LessonResource
    {
        $this->authorize('update', $lesson);

        $lesson->update($request->validated());

        return LessonResource::make(
            $lesson->load(['teachingAssignment.teacher.user', 'teachingAssignment.class', 'teachingAssignment.subject'])
        );
    }

    public function destroy(Lesson $lesson): Response
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return response()->noContent();
    }
}
