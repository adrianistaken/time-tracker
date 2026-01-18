<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_user_with_active_session_redirected_to_session(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->active()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertRedirect(route('session.show', $session));
    }

    public function test_user_with_no_projects_gets_auto_project_and_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // User has no projects initially
        $this->assertEquals(0, $user->projects()->count());

        $response = $this->get('/');

        // Should have created a project
        $this->assertEquals(1, $user->projects()->count());

        // Should have created an active session
        $this->assertEquals(1, $user->sessions()->count());
        $session = $user->sessions()->first();
        $this->assertNull($session->ended_at);

        // Should redirect to that session
        $response->assertRedirect(route('session.show', $session));
    }

    public function test_auto_created_project_has_random_witty_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get('/');

        $project = $user->projects()->first();

        // Name should have format "Adjective Noun"
        $this->assertMatchesRegularExpression('/^[A-Z][a-z]+ [A-Z][a-z]+$/', $project->name);
    }

    public function test_user_with_projects_but_no_active_session_goes_to_dashboard(): void
    {
        $user = User::factory()->create();
        Project::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_shows_all_required_data(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        // Create some sessions
        Session::factory()->count(3)->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('projects')
            ->has('colors')
            ->has('todaySeconds')
            ->has('weekSeconds')
            ->has('projectBreakdown')
            ->has('dailyTrend')
            ->has('recentSessions')
            ->has('range')
        );
    }

    public function test_dashboard_shows_active_session_if_exists(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $session = Session::factory()->active()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('activeSession')
            ->where('activeSession.id', $session->id)
        );
    }

    public function test_dashboard_range_filter_works(): void
    {
        $user = User::factory()->create();
        Project::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard', ['range' => '30d']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('range', '30d')
        );
    }
}
