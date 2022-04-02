<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    protected $table = 'aircraft';

    protected $fillable = [
        'iata',
        'icao',
        'name',
        'speed'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

}
