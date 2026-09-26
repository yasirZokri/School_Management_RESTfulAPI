<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TeacherController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Teacher::class);

        return TeacherResource::collection(
            // Load the account + school up front (see StudentController).
            Teacher::with(['user', 'school'])->paginate()
        );
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $this->authorize('create', Teacher::class);

        // Creating a teacher also creates their login account. Only admins
        // reach this point, and the payload is validated by the Form Request.
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed automatically by the User cast
            'role' => UserRole::Teacher,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'school_id' => $data['school_id'],
            'employee_number' => $data['employee_number'],
        ]);

        return TeacherResource::make($teacher->load(['user', 'school']))->response()->setStatusCode(201);
    }

    public function show(Teacher $teacher): TeacherResource
    {
        $this->authorize('view', $teacher);

        return TeacherResource::make($teacher->load(['user', 'school']));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): TeacherResource
    {
        $this->authorize('update', $teacher);

        $teacher->update($request->validated());

        return TeacherResource::make($teacher->load(['user', 'school']));
    }

    public function destroy(Teacher $teacher): Response
    {
        $this->authorize('delete', $teacher);

        // Soft delete (see StudentController for the explanation).
        $teacher->delete();

        return response()->noContent();
    }
}
