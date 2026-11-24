<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_code',
        'full_name',
        'phone',
        'email',
        'date_of_birth',
        'gender',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function medicalProfile()
    {
        return $this->hasOne(PatientMedicalProfile::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function medicalAlerts()
    {
        return $this->hasMany(MedicalAlert::class);
    }
    public function visits()
{
    return $this->hasMany(Visit::class);
}
public function teeth()
{
    return $this->hasMany(PatientTooth::class);
}
}