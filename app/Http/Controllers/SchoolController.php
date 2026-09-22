<?php

namespace App\Http\Controllers;

use App\Http\Requests\School\StoreSchoolRequest;
use App\Http\Requests\School\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SchoolController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', School::class);

        return SchoolResource::collection(School::paginate());
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $this->authorize('create', School::class);

        $school = School::create($request->validated());

        return SchoolResource::make($school)->response()->setStatusCode(201);
    }

    public function show(School $school): SchoolResource
    {
        $this->authorize('view', $school);

        return SchoolResource::make($school);
    }

    public function update(UpdateSchoolRequest $request, School $school): SchoolResource
    {
        $this->authorize('update', $school);

        $school->update($request->validated());

        return SchoolResource::make($school);
    }

    public function destroy(School $school): Response
    {
        $this->authorize('delete', $school);

        $school->delete();

        return response()->noContent();
    }
}
