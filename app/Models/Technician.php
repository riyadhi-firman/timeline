<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $division_id
 * @property string|null $phone
 * @property string|null $skill_level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Division $division
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician whereSkillLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Technician whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'division_id',
        'phone',
        'skill_level',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_technician');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'phone', 'skill_level'];
    }

    public function getGlobalSearchResultTitle(): string
    {
        return "Teknisi - {$this->user->name}";
    }

    public function getGlobalSearchResultDetails(): array
    {
        return [
            'Divisi' => $this->division->name,
            'Telepon' => $this->phone ?? '-',
            'Skill Level' => $this->skill_level ?? '-',
            'Total Jadwal' => $this->schedules()->count(),
        ];
    }
}
