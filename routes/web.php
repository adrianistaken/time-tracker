<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SessionController;
use App\Models\User;
use Database\Factories\ProjectFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Entry Point - Smart Redirect Logic
|--------------------------------------------------------------------------
*/
Route::get('/', function (Request $request): RedirectResponse {
    /** @var User $user */
    $user = $request->user();

    // Check for active session first - redirect to it
    $activeSession = $user->activeSession;
    if ($activeSession) {
        return redirect()->route('session.show', $activeSession);
    }

    // Check if user has any projects
    $hasProjects = $user->projects()->exists();

    if (! $hasProjects) {
        // Create a new project with a random name and start a session
        $project = $user->projects()->create([
            'name' => ProjectFactory::generateRandomName(),
            'color' => ProjectFactory::COLORS[array_rand(ProjectFactory::COLORS)],
        ]);

        $session = $user->sessions()->create([
            'project_id' => $project->id,
            'started_at' => now(),
        ]);

        return redirect()->route('session.show', $session);
    }

    // Has projects but no active session - go to dashboard
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard (Insight Mode)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Sessions (Time Tracking)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/session/{session}', [SessionController::class, 'show'])->name('session.show');
    Route::post('/sessions/start', [SessionController::class, 'start'])->name('session.start');
    Route::post('/session/{session}/stop', [SessionController::class, 'stop'])->name('session.stop');
});

/*
|--------------------------------------------------------------------------
| Projects
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('projects')->group(function () {
    Route::post('/', [ProjectController::class, 'store'])->name('project.store');
    Route::put('/{project}', [ProjectController::class, 'update'])->name('project.update');
    Route::post('/{project}/archive', [ProjectController::class, 'archive'])->name('project.archive');
    Route::post('/{project}/unarchive', [ProjectController::class, 'unarchive'])->name('project.unarchive');
});

require __DIR__.'/settings.php';
