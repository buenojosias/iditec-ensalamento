<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'position',
        'name',
        'active',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class);
    }
}
