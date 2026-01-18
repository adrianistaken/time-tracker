<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-7 days', 'now');
        $durationSeconds = fake()->numberBetween(300, 7200); // 5 mins to 2 hours
        $endedAt = Carbon::parse($startedAt)->addSeconds($durationSeconds);

        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $durationSeconds,
            'note' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the session is currently active (running).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => now(),
            'ended_at' => null,
            'duration_seconds' => null,
        ]);
    }

    /**
     * Create a session that started today.
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = fake()->dateTimeBetween('today', 'now');
            $durationSeconds = fake()->numberBetween(300, 3600);
            $endedAt = Carbon::parse($startedAt)->addSeconds($durationSeconds);

            // Make sure ended_at doesn't go past now
            if ($endedAt->isFuture()) {
                $endedAt = now();
                $durationSeconds = $endedAt->diffInSeconds(Carbon::parse($startedAt));
            }

            return [
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $durationSeconds,
            ];
        });
    }

    /**
     * Create a session from this week.
     */
    public function thisWeek(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = fake()->dateTimeBetween('monday this week', 'now');
            $durationSeconds = fake()->numberBetween(300, 5400);
            $endedAt = Carbon::parse($startedAt)->addSeconds($durationSeconds);

            if ($endedAt->isFuture()) {
                $endedAt = now();
                $durationSeconds = $endedAt->diffInSeconds(Carbon::parse($startedAt));
            }

            return [
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $durationSeconds,
            ];
        });
    }
}
