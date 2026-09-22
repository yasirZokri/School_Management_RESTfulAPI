<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentPayment\StoreStudentPaymentRequest;
use App\Http\Requests\StudentPayment\UpdateStudentPaymentRequest;
use App\Http\Resources\StudentPaymentResource;
use App\Models\StudentPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StudentPaymentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', StudentPayment::class);

        return StudentPaymentResource::collection(
            StudentPayment::with(['student.user'])->paginate()
        );
    }

    public function store(StoreStudentPaymentRequest $request): JsonResponse
    {
        $this->authorize('create', StudentPayment::class);

        $payment = StudentPayment::create($request->validated());

        return StudentPaymentResource::make(
            $payment->load(['student.user'])
        )->response()->setStatusCode(201);
    }

    public function show(StudentPayment $studentPayment): StudentPaymentResource
    {
        $this->authorize('view', $studentPayment);

        return StudentPaymentResource::make($studentPayment->load(['student.user']));
    }

    public function update(UpdateStudentPaymentRequest $request, StudentPayment $studentPayment): StudentPaymentResource
    {
        $this->authorize('update', $studentPayment);

        $studentPayment->update($request->validated());

        return StudentPaymentResource::make($studentPayment->load(['student.user']));
    }

    public function destroy(StudentPayment $studentPayment): Response
    {
        $this->authorize('delete', $studentPayment);

        $studentPayment->delete();

        return response()->noContent();
    }
}
