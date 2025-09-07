<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'module_id',
        'schedule',
        'weekday',
        'time',
        'shift',
        'students_number',
        'period',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'current_team_id');
    }
}
