<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitDiagnosis extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_tooth_id',
        'diagnosis',
        'notes',
        'diagnosed_at',
    ];

    protected function casts(): array
    {
        return [
            'diagnosed_at' => 'datetime',
        ];
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patientTooth()
    {
        return $this->belongsTo(PatientTooth::class);
    }
}