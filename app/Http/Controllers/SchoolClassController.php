<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClass\StoreSchoolClassRequest;
use App\Http\Requests\SchoolClass\UpdateSchoolClassRequest;
use App\Http\Resources\SchoolClassResource;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SchoolClassController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SchoolClass::class);

        return SchoolClassResource::collection(SchoolClass::with(['school', 'academicYear'])->paginate());
    }

    public function store(StoreSchoolClassRequest $request): JsonResponse
    {
        $this->authorize('create', SchoolClass::class);

        $class = SchoolClass::create($request->validated());

        return SchoolClassResource::make($class->load(['school', 'academicYear']))->response()->setStatusCode(201);
    }

    /**
     * NOTE on naming: the route parameter is {class}, so the method
     * variables must be called $class - otherwise Laravel injects a new,
     * empty model instead of the one from the URL.
     */
    public function show(SchoolClass $class): SchoolClassResource
    {
        $this->authorize('view', $class);

        return SchoolClassResource::make($class->load(['school', 'academicYear']));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $class): SchoolClassResource
    {
        $this->authorize('update', $class);

        $class->update($request->validated());

        return SchoolClassResource::make($class->load(['school', 'academicYear']));
    }

    public function destroy(SchoolClass $class): Response
    {
        $this->authorize('delete', $class);

        $class->delete();

        return response()->noContent();
    }
}
