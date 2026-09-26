<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Student::class);

        return StudentResource::collection(
            // ->with() loads the account + school now, avoiding extra queries
            // per row (an "N + 1" problem) when the list is converted to JSON.
            Student::with(['user', 'school'])->paginate()
        );
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $this->authorize('create', Student::class);

        // Creating a student also creates their login account. Only admins
        // reach this point, and the payload is validated by the Form Request.
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed automatically by the User cast
            'role' => UserRole::Student,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'school_id' => $data['school_id'],
            'student_number' => $data['student_number'],
        ]);

        return StudentResource::make($student->load(['user', 'school']))->response()->setStatusCode(201);
    }

    public function show(Student $student): StudentResource
    {
        $this->authorize('view', $student);

        return StudentResource::make($student->load(['user', 'school']));
    }

    public function update(UpdateStudentRequest $request, Student $student): StudentResource
    {
        $this->authorize('update', $student);

        $student->update($request->validated());

        return StudentResource::make($student->load(['user', 'school']));
    }

    public function destroy(Student $student): Response
    {
        $this->authorize('delete', $student);

        // Because of SoftDeletes this is a soft delete: the row stays in
        // the database with a deleted_at value, hidden from normal listings.
        $student->delete();

        return response()->noContent();
    }
}
