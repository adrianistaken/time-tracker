<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Factories\ProjectFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with time tracking insights.
     */
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        // Get date range from request
        $range = $request->get('range', '7d');
        $startDate = $this->getStartDate($range);

        // Get projects for the dropdown
        $projects = $user->projects()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'color' => $project->color,
                'total_seconds' => $project->total_seconds,
            ]);

        // Today's total time
        $todaySeconds = $user->sessions()
            ->completed()
            ->whereDate('started_at', today())
            ->sum('duration_seconds');

        // This week's total time
        $weekSeconds = $user->sessions()
            ->completed()
            ->where('started_at', '>=', now()->startOfWeek())
            ->sum('duration_seconds');

        // Time per project (for bar chart)
        $projectBreakdown = $user->sessions()
            ->completed()
            ->where('started_at', '>=', $startDate)
            ->with('project')
            ->get()
            ->groupBy('project_id')
            ->map(function ($sessions) {
                $project = $sessions->first()->project;

                return [
                    'project_id' => $project->id,
                    'name' => $project->name,
                    'color' => $project->color,
                    'total_seconds' => $sessions->sum('duration_seconds'),
                ];
            })
            ->sortByDesc('total_seconds')
            ->values();

        // Daily trend (for line chart) - last 7 days
        $dailyTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $seconds = $user->sessions()
                ->completed()
                ->whereDate('started_at', $date)
                ->sum('duration_seconds');

            $dailyTrend->push([
                'date' => $date->format('M j'),
                'full_date' => $date->format('Y-m-d'),
                'seconds' => (int) $seconds,
                'hours' => round($seconds / 3600, 2),
            ]);
        }

        // Recent sessions for history list
        $recentSessions = $user->sessions()
            ->completed()
            ->with('project')
            ->orderByDesc('ended_at')
            ->limit(20)
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'project' => [
                    'id' => $session->project->id,
                    'name' => $session->project->name,
                    'color' => $session->project->color,
                ],
                'started_at' => $session->started_at->toISOString(),
                'ended_at' => $session->ended_at->toISOString(),
                'duration_seconds' => $session->duration_seconds,
                'formatted_duration' => $session->formatted_duration,
                'note' => $session->note,
            ]);

        // Check for active session
        $activeSession = $user->activeSession;

        return Inertia::render('Dashboard', [
            'projects' => $projects,
            'colors' => ProjectFactory::COLORS,
            'todaySeconds' => (int) $todaySeconds,
            'weekSeconds' => (int) $weekSeconds,
            'projectBreakdown' => $projectBreakdown,
            'dailyTrend' => $dailyTrend,
            'recentSessions' => $recentSessions,
            'activeSession' => $activeSession ? [
                'id' => $activeSession->id,
                'started_at' => $activeSession->started_at->toISOString(),
                'project' => [
                    'id' => $activeSession->project->id,
                    'name' => $activeSession->project->name,
                    'color' => $activeSession->project->color,
                ],
            ] : null,
            'range' => $range,
        ]);
    }

    /**
     * Get the start date based on the range filter.
     */
    private function getStartDate(string $range): Carbon
    {
        return Carbon::parse(match ($range) {
            'today' => now()->startOfDay(),
            '30d' => now()->subDays(30)->startOfDay(),
            default => now()->subDays(7)->startOfDay(),
        });
    }
}
