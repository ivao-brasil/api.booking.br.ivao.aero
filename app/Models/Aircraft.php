<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Aircraft
 *
 * @property int $id
 * @property string $iata
 * @property string $icao
 * @property string $name
 * @property int|null $speed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft query()
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereIata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereIcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
