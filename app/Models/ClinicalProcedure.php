<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'procedure_id',
        'patient_tooth_id',
        'notes',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
        ];
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function patientTooth()
    {
        return $this->belongsTo(PatientTooth::class);
    }
}