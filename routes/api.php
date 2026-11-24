<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DoctorScheduleController;
use App\Http\Controllers\Api\V1\PatientController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\QueueController;
use App\Http\Controllers\Api\V1\VisitController;
use App\Http\Controllers\Api\V1\OdontogramController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    /*
    |--------------------------------------------------------------------------
    | Patients
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'role:admin,reception,doctor'])
        ->group(function () {
            Route::get('/patients', [PatientController::class, 'index']);
            Route::get('/patients/{patient}', [PatientController::class, 'show']);
        });

    Route::middleware(['auth:sanctum', 'role:admin,reception'])
        ->group(function () {
            Route::post('/patients', [PatientController::class, 'store']);
            Route::patch('/patients/{patient}', [PatientController::class, 'update']);
        });

    /*
    |--------------------------------------------------------------------------
    | Doctors / Scheduling
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'role:admin,reception,doctor'])
        ->group(function () {
            Route::get(
                '/doctors/{doctor}/availability',
                [DoctorScheduleController::class, 'availability']
            );
        });

    /*
    |--------------------------------------------------------------------------
    | Appointments - Read
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'role:admin,reception,doctor'])
        ->group(function () {
            Route::get(
                '/appointments',
                [AppointmentController::class, 'index']
            );

            Route::get(
                '/appointments/{appointment}',
                [AppointmentController::class, 'show']
            );
        });

    /*
    |--------------------------------------------------------------------------
    | Appointments - Write / State Transitions
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'role:admin,reception'])
        ->group(function () {

            Route::post(
                '/appointments',
                [AppointmentController::class, 'store']
            );

            Route::post(
                '/appointments/{appointment}/confirm',
                [AppointmentController::class, 'confirm']
            );

            Route::post(
                '/appointments/{appointment}/reschedule',
                [AppointmentController::class, 'reschedule']
            );

            Route::post(
                '/appointments/{appointment}/cancel',
                [AppointmentController::class, 'cancel']
            );

            Route::post(
                '/appointments/{appointment}/no-show',
                [AppointmentController::class, 'noShow']
            );
        });
        /*
|--------------------------------------------------------------------------
| Queue
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin,reception'])
->group(function () {

    Route::post(
        '/appointments/{appointment}/check-in',
        [QueueController::class, 'checkIn']
    );

    Route::get(
        '/queue/today',
        [QueueController::class, 'today']
    );

    Route::post(
        '/queue/{queueEntry}/call',
        [QueueController::class, 'call']
    );

    Route::post(
        '/queue/{queueEntry}/start',
        [QueueController::class, 'start']
    );

    Route::post(
        '/queue/{queueEntry}/complete',
        [QueueController::class, 'complete']
    );

    Route::post(
        '/queue/{queueEntry}/skip',
        [QueueController::class, 'skip']
    );
});
/*
|--------------------------------------------------------------------------
| Clinical Visits
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin,doctor'])
    ->group(function () {

        Route::post(
            '/visits',
            [VisitController::class, 'store']
        );

        Route::get(
            '/visits/{visit}',
            [VisitController::class, 'show']
        );

        Route::post(
            '/visits/{visit}/complete',
            [VisitController::class, 'complete']
        );
    });

    /*
|--------------------------------------------------------------------------
| Odontogram / Dental Chart
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin,doctor'])
->group(function () {

    Route::get(
        '/patients/{patient}/teeth',
        [OdontogramController::class, 'patientChart']
    );

    Route::post(
        '/visits/{visit}/tooth-conditions',
        [OdontogramController::class, 'storeCondition']
    );
});
    
});