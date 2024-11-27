<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Division
 *
 * @property int $id
 * @property string $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft query()
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Aircraft whereActive($value)
 * @mixin \Eloquent
 */
class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'id',
        'active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
