<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctors\CheckAvailabilityRequest;
use App\Models\DoctorProfile;
use App\Services\Appointments\AppointmentAvailabilityService;
use Illuminate\Http\JsonResponse;

class DoctorScheduleController extends Controller
{
    public function availability(
        CheckAvailabilityRequest $request,
        DoctorProfile $doctor,
        AppointmentAvailabilityService $service
    ): JsonResponse {
        $data = $request->validated();

        $available = $service->isAvailable(
            $doctor,
            $data['date'],
            $data['start_time'],
            $data['end_time']
        );

        return response()->json([
            'available' => $available,
        ]);
    }
}