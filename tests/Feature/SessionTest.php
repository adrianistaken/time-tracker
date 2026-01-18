<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_start_session(): void
    {
        $response = $this->post(route('session.start'), ['project_id' => 1]);
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_start_session(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('session.start'), [
            'project_id' => $project->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tracking_sessions', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'ended_at' => null,
        ]);
    }

    public function test_starting_session_stops_previous_active_session(): void
    {
        $user = User::factory()->create();
        $project1 = Project::factory()->create(['user_id' => $user->id]);
        $project2 = Project::factory()->create(['user_id' => $user->id]);

        // Create an active session
        $activeSession = Session::factory()->active()->create([
            'user_id' => $user->id,
            'project_id' => $project1->id,
        ]);

        $this->actingAs($user);

        // Start a new session
        $response = $this->post(route('session.start'), [
            'project_id' => $project2->id,
        ]);

        $response->assertRedirect();

        // Previous session should be stopped
        $activeSession->refresh();
        $this->assertNotNull($activeSession->ended_at);
        $this->assertNotNull($activeSession->duration_seconds);

        // New session should be active
        $newSession = Session::where('project_id', $project2->id)->first();
        $this->assertNotNull($newSession);
        $this->assertNull($newSession->ended_at);
    }

    public function test_user_can_view_active_session(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->active()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('session.show', $session));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Session/Show')
            ->has('session')
            ->where('session.id', $session->id)
            ->where('session.project.name', $project->name)
        );
    }

    public function test_viewing_completed_session_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('session.show', $session));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_stop_session(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->active()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('session.stop', $session), [
            'note' => 'Completed the feature',
        ]);

        $response->assertRedirect(route('dashboard'));

        $session->refresh();
        $this->assertNotNull($session->ended_at);
        $this->assertNotNull($session->duration_seconds);
        $this->assertEquals('Completed the feature', $session->note);
    }

    public function test_user_can_stop_session_without_note(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->active()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('session.stop', $session));

        $response->assertRedirect(route('dashboard'));

        $session->refresh();
        $this->assertNotNull($session->ended_at);
        $this->assertNull($session->note);
    }

    public function test_stopping_already_stopped_session_redirects(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('session.stop', $session));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_cannot_start_session_for_nonexistent_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('session.start'), [
            'project_id' => 99999,
        ]);

        $response->assertSessionHasErrors('project_id');
    }

    public function test_session_calculates_duration_correctly(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $startedAt = now()->subMinutes(30);
        $session = Session::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'started_at' => $startedAt,
            'ended_at' => null,
            'duration_seconds' => null,
        ]);

        $session->stop();

        $session->refresh();

        // Duration should be approximately 30 minutes (1800 seconds)
        // Allow some margin for test execution time
        $this->assertGreaterThanOrEqual(1799, $session->duration_seconds);
        $this->assertLessThanOrEqual(1810, $session->duration_seconds);
    }
}
