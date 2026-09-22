<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicYear\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AcademicYearController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AcademicYear::class);

        return AcademicYearResource::collection(AcademicYear::with('school')->paginate());
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $this->authorize('create', AcademicYear::class);

        $year = AcademicYear::create($request->validated());

        return AcademicYearResource::make($year->load('school'))->response()->setStatusCode(201);
    }

    public function show(AcademicYear $academicYear): AcademicYearResource
    {
        $this->authorize('view', $academicYear);

        return AcademicYearResource::make($academicYear->load('school'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): AcademicYearResource
    {
        $this->authorize('update', $academicYear);

        $academicYear->update($request->validated());

        return AcademicYearResource::make($academicYear->load('school'));
    }

    public function destroy(AcademicYear $academicYear): Response
    {
        $this->authorize('delete', $academicYear);

        $academicYear->delete();

        return response()->noContent();
    }
}
