<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherSalary\StoreTeacherSalaryRequest;
use App\Http\Requests\TeacherSalary\UpdateTeacherSalaryRequest;
use App\Http\Resources\TeacherSalaryResource;
use App\Models\TeacherSalary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TeacherSalaryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', TeacherSalary::class);

        return TeacherSalaryResource::collection(
            TeacherSalary::with(['teacher.user'])->paginate()
        );
    }

    public function store(StoreTeacherSalaryRequest $request): JsonResponse
    {
        $this->authorize('create', TeacherSalary::class);

        $salary = TeacherSalary::create($request->validated());

        return TeacherSalaryResource::make(
            $salary->load(['teacher.user'])
        )->response()->setStatusCode(201);
    }

    public function show(TeacherSalary $teacherSalary): TeacherSalaryResource
    {
        $this->authorize('view', $teacherSalary);

        return TeacherSalaryResource::make($teacherSalary->load(['teacher.user']));
    }

    public function update(UpdateTeacherSalaryRequest $request, TeacherSalary $teacherSalary): TeacherSalaryResource
    {
        $this->authorize('update', $teacherSalary);

        $teacherSalary->update($request->validated());

        return TeacherSalaryResource::make($teacherSalary->load(['teacher.user']));
    }

    public function destroy(TeacherSalary $teacherSalary): Response
    {
        $this->authorize('delete', $teacherSalary);

        $teacherSalary->delete();

        return response()->noContent();
    }
}
