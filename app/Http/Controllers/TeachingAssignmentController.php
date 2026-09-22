<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeachingAssignment\StoreTeachingAssignmentRequest;
use App\Http\Requests\TeachingAssignment\UpdateTeachingAssignmentRequest;
use App\Http\Resources\TeachingAssignmentResource;
use App\Models\TeachingAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', TeachingAssignment::class);

        return TeachingAssignmentResource::collection(
            TeachingAssignment::with(['teacher.user', 'class', 'subject', 'academicYear'])->paginate()
        );
    }

    public function store(StoreTeachingAssignmentRequest $request): JsonResponse
    {
        $this->authorize('create', TeachingAssignment::class);

        $assignment = TeachingAssignment::create($request->validated());

        return TeachingAssignmentResource::make(
            $assignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
        )->response()->setStatusCode(201);
    }

    public function show(TeachingAssignment $teachingAssignment): TeachingAssignmentResource
    {
        $this->authorize('view', $teachingAssignment);

        return TeachingAssignmentResource::make(
            $teachingAssignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
        );
    }

    public function update(UpdateTeachingAssignmentRequest $request, TeachingAssignment $teachingAssignment): TeachingAssignmentResource
    {
        $this->authorize('update', $teachingAssignment);

        $teachingAssignment->update($request->validated());

        return TeachingAssignmentResource::make(
            $teachingAssignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
        );
    }

    public function destroy(TeachingAssignment $teachingAssignment): Response
    {
        $this->authorize('delete', $teachingAssignment);

        $teachingAssignment->delete();

        return response()->noContent();
    }
}
