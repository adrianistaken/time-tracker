<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Session extends Model
{
    /** @use HasFactory<\Database\Factories\SessionFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tracking_sessions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_seconds' => 'integer',
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
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope to get only active (running) sessions.
     *
     * @param  Builder<Session>  $query
     * @return Builder<Session>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope to get only completed sessions.
     *
     * @param  Builder<Session>  $query
     * @return Builder<Session>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('ended_at');
    }

    /**
     * Check if this session is currently running.
     */
    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    /**
     * Stop the session and calculate duration.
     */
    public function stop(?string $note = null): void
    {
        if (! $this->isActive()) {
            return;
        }

        $endedAt = Carbon::now();
        $durationSeconds = (int) abs($endedAt->diffInSeconds($this->started_at));

        $this->update([
            'ended_at' => $endedAt,
            'duration_seconds' => $durationSeconds,
            'note' => $note,
        ]);
    }

    /**
     * Get the elapsed seconds (for active sessions, calculates from now).
     */
    public function getElapsedSecondsAttribute(): int
    {
        if ($this->ended_at) {
            return $this->duration_seconds ?? 0;
        }

        return (int) Carbon::now()->diffInSeconds($this->started_at);
    }

    /**
     * Format duration as HH:MM:SS.
     */
    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->elapsed_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
