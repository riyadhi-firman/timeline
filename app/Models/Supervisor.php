<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $division_id
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Division $division
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supervisor whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Supervisor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'division_id',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'phone'];
    }

    public function getGlobalSearchResultTitle(): string
    {
        return "Supervisor - {$this->user->name}";
    }

    public function getGlobalSearchResultDetails(): array
    {
        return [
            'Divisi' => $this->division->name,
            'Telepon' => $this->phone ?? '-',
            'Email' => $this->user->email,
            'Bergabung' => $this->created_at->format('d M Y'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        
        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $query->where('division_id', $userDivisionId);
            }
        }
        
        return $query;
    }
}
