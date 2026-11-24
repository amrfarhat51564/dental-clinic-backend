<?php

namespace App\Models;

use App\Enums\Dentition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientTooth extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'dentition',
        'tooth_code',
    ];

    protected function casts(): array
    {
        return [
            'dentition' => Dentition::class,
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function conditions()
    {
        return $this->hasMany(ToothCondition::class);
    }

    public function latestCondition()
    {
        return $this->hasOne(ToothCondition::class)
            ->latestOfMany('recorded_at');
    }
}