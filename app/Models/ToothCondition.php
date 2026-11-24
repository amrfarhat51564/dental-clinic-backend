<?php

namespace App\Models;

use App\Enums\ToothConditionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToothCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_tooth_id',
        'visit_id',
        'condition_type',
        'surfaces',
        'notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'condition_type' => ToothConditionType::class,
            'surfaces' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    public function patientTooth()
    {
        return $this->belongsTo(PatientTooth::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }
}