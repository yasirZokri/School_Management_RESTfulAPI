<?php

namespace App\Http\Controllers;

use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Http\Requests\Enrollment\UpdateEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Enrollment::class);

        return EnrollmentResource::collection(
            Enrollment::with(['student.user', 'class', 'academicYear'])->paginate()
        );
    }

    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $this->authorize('create', Enrollment::class);

        $enrollment = Enrollment::create($request->validated());

        // refresh() reloads the row from the database so the response shows
        // database defaults (like status = 'active') instead of null.
        return EnrollmentResource::make(
            $enrollment->refresh()->load(['student.user', 'class', 'academicYear'])
        )->response()->setStatusCode(201);
    }

    public function show(Enrollment $enrollment): EnrollmentResource
    {
        $this->authorize('view', $enrollment);

        return EnrollmentResource::make($enrollment->load(['student.user', 'class', 'academicYear']));
    }

    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment): EnrollmentResource
    {
        $this->authorize('update', $enrollment);

        $enrollment->update($request->validated());

        return EnrollmentResource::make($enrollment->load(['student.user', 'class', 'academicYear']));
    }

    public function destroy(Enrollment $enrollment): Response
    {
        $this->authorize('delete', $enrollment);

        $enrollment->delete();

        return response()->noContent();
    }
}
