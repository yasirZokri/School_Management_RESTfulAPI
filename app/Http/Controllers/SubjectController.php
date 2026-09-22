<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SubjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Subject::class);

        return SubjectResource::collection(Subject::with('school')->paginate());
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $this->authorize('create', Subject::class);

        $subject = Subject::create($request->validated());

        return SubjectResource::make($subject->load('school'))->response()->setStatusCode(201);
    }

    public function show(Subject $subject): SubjectResource
    {
        $this->authorize('view', $subject);

        return SubjectResource::make($subject->load('school'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): SubjectResource
    {
        $this->authorize('update', $subject);

        $subject->update($request->validated());

        return SubjectResource::make($subject->load('school'));
    }

    public function destroy(Subject $subject): Response
    {
        $this->authorize('delete', $subject);

        $subject->delete();

        return response()->noContent();
    }
}
