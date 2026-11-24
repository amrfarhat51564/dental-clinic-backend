<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorScheduleException extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_profile_id',
        'date',
        'is_available',
        'start_time',
        'end_time',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }
}