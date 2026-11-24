<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialty',
        'phone',
        'license_number',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }
    public function workingHours()
    {
        return $this->hasMany(DoctorWorkingHour::class);
    }
    
    public function scheduleExceptions()
    {
        return $this->hasMany(DoctorScheduleException::class);
    }
    public function appointments()
{
    return $this->hasMany(Appointment::class);
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function visits()
{
    return $this->hasMany(Visit::class);
}
}