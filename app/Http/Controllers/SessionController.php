<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartSessionRequest;
use App\Http\Requests\StopSessionRequest;
use App\Models\Project;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    /**
     * Display the focus mode for an active session.
     */
    public function show(Session $session): Response|RedirectResponse
    {
        // If session is not active, redirect to dashboard
        if (! $session->isActive()) {
            return redirect()->route('dashboard')->with('info', 'That session has ended.');
        }

        $session->load('project');

        return Inertia::render('Session/Show', [
            'session' => [
                'id' => $session->id,
                'started_at' => $session->started_at->toISOString(),
                'project' => [
                    'id' => $session->project->id,
                    'name' => $session->project->name,
                    'color' => $session->project->color,
                ],
            ],
        ]);
    }

    /**
     * Start a new tracking session.
     */
    public function start(StartSessionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Stop any currently active session first
        $activeSession = $user->activeSession;
        if ($activeSession) {
            $activeSession->stop();
        }

        // Verify the project belongs to this user
        $project = Project::where('id', $request->validated('project_id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Create new session
        $session = $user->sessions()->create([
            'project_id' => $project->id,
            'started_at' => now(),
        ]);

        return redirect()->route('session.show', $session);
    }

    /**
     * Stop the current tracking session.
     */
    public function stop(StopSessionRequest $request, Session $session): RedirectResponse
    {
        if (! $session->isActive()) {
            return redirect()->route('dashboard')->with('info', 'That session has already ended.');
        }

        $session->stop($request->validated('note'));

        return redirect()->route('dashboard')->with('success', 'Session stopped.');
    }
}
