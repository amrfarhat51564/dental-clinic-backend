<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'is_active',
    ];
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function patients()
{
    return $this->hasMany(Patient::class);
}
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}