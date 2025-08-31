<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Supervisor|null $supervisor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supervisor> $supervisors
 * @property-read int|null $supervisors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Technician> $technicians
 * @property-read int|null $technicians_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function supervisors(): HasMany
    {
        return $this->hasMany(Supervisor::class);
    }

    public function supervisor(): HasOne
    {
        return $this->hasOne(Supervisor::class);
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(Technician::class);
    }
}
