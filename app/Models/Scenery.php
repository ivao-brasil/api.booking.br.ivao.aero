<?php

namespace App\Models;

use Database\Factories\ScenaryFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Scenery
 *
 * @property int $id
 * @property string $title
 * @property string $license
 * @property string $link
 * @property string $simulator
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $icao
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery query()
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereIcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereSimulator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scenery whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Scenery extends Model
{
    protected $fillable = [
        'title',
        'license',
        'simulator',
        'link',
        'icao'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static function _factory()
    {
        return ScenaryFactory::new();
    }
}
