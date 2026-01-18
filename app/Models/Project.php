<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'color',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Session, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Get total tracked time in seconds for this project.
     */
    public function getTotalSecondsAttribute(): int
    {
        return (int) $this->sessions()
            ->whereNotNull('ended_at')
            ->sum('duration_seconds');
    }

    /**
     * Get the last time work was done on this project.
     */
    public function getLastWorkedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        $lastSession = $this->sessions()
            ->whereNotNull('ended_at')
            ->latest('ended_at')
            ->first();

        return $lastSession?->ended_at;
    }

    /**
     * Check if this project is archived.
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
