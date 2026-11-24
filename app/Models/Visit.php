<?php

namespace App\Models;

use App\Enums\VisitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_profile_id',
        'visit_type_id',
        'appointment_id',
        'status',
        'chief_complaint',
        'examination_notes',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VisitStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function visitType()
    {
        return $this->belongsTo(VisitType::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function toothConditions()
{
    return $this->hasMany(ToothCondition::class);
}
public function diagnoses()
{
    return $this->hasMany(VisitDiagnosis::class);
}

public function clinicalProcedures()
{
    return $this->hasMany(ClinicalProcedure::class);
}
}