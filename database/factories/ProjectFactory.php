<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Available project colors palette.
     *
     * @var list<string>
     */
    public const COLORS = [
        '#6366f1', // Indigo
        '#8b5cf6', // Violet
        '#ec4899', // Pink
        '#ef4444', // Red
        '#f97316', // Orange
        '#eab308', // Yellow
        '#22c55e', // Green
        '#14b8a6', // Teal
        '#06b6d4', // Cyan
        '#3b82f6', // Blue
    ];

    /**
     * Adjectives for random project name generation.
     *
     * @var list<string>
     */
    public const ADJECTIVES = [
        'Cosmic', 'Swift', 'Brave', 'Quiet', 'Bold',
        'Wild', 'Gentle', 'Fierce', 'Clever', 'Nimble',
        'Radiant', 'Silent', 'Mighty', 'Mystic', 'Noble',
        'Vivid', 'Ancient', 'Daring', 'Golden', 'Silver',
    ];

    /**
     * Nouns for random project name generation.
     *
     * @var list<string>
     */
    public const NOUNS = [
        'Penguin', 'Thunder', 'Phoenix', 'Falcon', 'Panda',
        'Tiger', 'Orbit', 'Comet', 'Wave', 'Mountain',
        'River', 'Forest', 'Dragon', 'Wolf', 'Owl',
        'Spark', 'Storm', 'Aurora', 'Crystal', 'Shadow',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->generateRandomName(),
            'color' => fake()->randomElement(self::COLORS),
            'archived_at' => null,
        ];
    }

    /**
     * Indicate that the project is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }

    /**
     * Generate a random witty project name.
     */
    public static function generateRandomName(): string
    {
        $adjective = self::ADJECTIVES[array_rand(self::ADJECTIVES)];
        $noun = self::NOUNS[array_rand(self::NOUNS)];

        return "{$adjective} {$noun}";
    }
}
