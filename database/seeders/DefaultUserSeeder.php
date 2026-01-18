<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Session;
use App\Models\User;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or find the default user
        $user = User::firstOrCreate(
            ['email' => 'user@timetracker.local'],
            [
                'name' => 'Default User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Default user: {$user->email}");

        // Create sample projects if none exist
        if ($user->projects()->count() === 0) {
            $this->createSampleProjects($user);
        }
    }

    /**
     * Create sample projects with sessions for demo purposes.
     */
    private function createSampleProjects(User $user): void
    {
        $projectNames = [
            'Side Project' => ProjectFactory::COLORS[0], // Indigo
            'Client Work' => ProjectFactory::COLORS[4],  // Orange
            'Learning' => ProjectFactory::COLORS[6],     // Green
        ];

        foreach ($projectNames as $name => $color) {
            $project = Project::factory()->create([
                'user_id' => $user->id,
                'name' => $name,
                'color' => $color,
            ]);

            // Create some historical sessions for each project
            $this->createSampleSessions($user, $project);

            $this->command->info("Created project: {$name}");
        }
    }

    /**
     * Create sample sessions for a project over the past week.
     */
    private function createSampleSessions(User $user, Project $project): void
    {
        // Create 3-5 sessions over the past 7 days
        $sessionCount = rand(3, 5);

        for ($i = 0; $i < $sessionCount; $i++) {
            $daysAgo = rand(0, 6);
            $startHour = rand(9, 18);
            $durationMinutes = rand(15, 120);

            $startedAt = Carbon::now()
                ->subDays($daysAgo)
                ->setHour($startHour)
                ->setMinute(rand(0, 59))
                ->setSecond(0);

            $endedAt = $startedAt->copy()->addMinutes($durationMinutes);

            // Don't create sessions that would end in the future
            if ($endedAt->isFuture()) {
                continue;
            }

            Session::factory()->create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $durationMinutes * 60,
                'note' => rand(0, 10) > 7 ? fake()->sentence() : null,
            ]);
        }
    }
}
