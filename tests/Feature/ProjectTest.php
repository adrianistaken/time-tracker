<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Factories\ProjectFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_project(): void
    {
        $response = $this->post(route('project.store'), [
            'name' => 'Test Project',
            'color' => ProjectFactory::COLORS[0],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('project.store'), [
            'name' => 'My New Project',
            'color' => ProjectFactory::COLORS[0],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'My New Project',
            'color' => ProjectFactory::COLORS[0],
        ]);
    }

    public function test_project_requires_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('project.store'), [
            'name' => '',
            'color' => ProjectFactory::COLORS[0],
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_project_requires_valid_color(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('project.store'), [
            'name' => 'Test Project',
            'color' => '#ffffff', // Not in allowed palette
        ]);

        $response->assertSessionHasErrors('color');
    }

    public function test_user_can_update_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Name',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('project.update', $project), [
            'name' => 'Updated Name',
            'color' => ProjectFactory::COLORS[1],
        ]);

        $response->assertRedirect();

        $project->refresh();
        $this->assertEquals('Updated Name', $project->name);
        $this->assertEquals(ProjectFactory::COLORS[1], $project->color);
    }

    public function test_user_can_archive_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'archived_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('project.archive', $project));

        $response->assertRedirect();

        $project->refresh();
        $this->assertNotNull($project->archived_at);
        $this->assertTrue($project->isArchived());
    }

    public function test_user_can_unarchive_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->archived()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('project.unarchive', $project));

        $response->assertRedirect();

        $project->refresh();
        $this->assertNull($project->archived_at);
        $this->assertFalse($project->isArchived());
    }

    public function test_project_total_seconds_accessor(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Create some completed sessions
        $project->sessions()->createMany([
            [
                'user_id' => $user->id,
                'started_at' => now()->subHours(2),
                'ended_at' => now()->subHour(),
                'duration_seconds' => 3600,
            ],
            [
                'user_id' => $user->id,
                'started_at' => now()->subMinutes(30),
                'ended_at' => now(),
                'duration_seconds' => 1800,
            ],
        ]);

        $this->assertEquals(5400, $project->total_seconds);
    }

    public function test_project_scopes_work_correctly(): void
    {
        $user = User::factory()->create();

        $activeProject = Project::factory()->create([
            'user_id' => $user->id,
            'archived_at' => null,
        ]);

        $archivedProject = Project::factory()->archived()->create([
            'user_id' => $user->id,
        ]);

        $activeProjects = Project::active()->get();
        $archivedProjects = Project::archived()->get();

        $this->assertTrue($activeProjects->contains($activeProject));
        $this->assertFalse($activeProjects->contains($archivedProject));

        $this->assertTrue($archivedProjects->contains($archivedProject));
        $this->assertFalse($archivedProjects->contains($activeProject));
    }

    public function test_random_project_name_generator(): void
    {
        $name1 = ProjectFactory::generateRandomName();
        $name2 = ProjectFactory::generateRandomName();

        // Name should contain a space (adjective + noun)
        $this->assertStringContainsString(' ', $name1);
        $this->assertStringContainsString(' ', $name2);

        // Names should be different (with very high probability)
        // Note: There's a small chance this could fail if random picks the same
        // so we generate many names and check uniqueness
        $names = [];
        for ($i = 0; $i < 20; $i++) {
            $names[] = ProjectFactory::generateRandomName();
        }
        $uniqueNames = array_unique($names);
        $this->assertGreaterThan(5, count($uniqueNames));
    }
}
