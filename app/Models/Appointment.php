<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_profile_id',
        'start_at',
        'end_at',
        'status',
        'notes',
        'visit_type_id',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function visitType()
    {
        return $this->belongsTo(VisitType::class);
    }
    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }
    public function queueEntry()
    {
        return $this->hasOne(QueueEntry::class);
    }
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
    public function visit()
{
    return $this->hasOne(Visit::class);
}
}