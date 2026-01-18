<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ProjectController extends Controller
{
    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $project = $user->projects()->create($request->validated());

        return redirect()->back()->with('success', "Project '{$project->name}' created.");
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->back()->with('success', "Project '{$project->name}' updated.");
    }

    /**
     * Archive the specified project.
     */
    public function archive(Project $project): RedirectResponse
    {
        $project->update(['archived_at' => now()]);

        return redirect()->back()->with('success', "Project '{$project->name}' archived.");
    }

    /**
     * Unarchive the specified project.
     */
    public function unarchive(Project $project): RedirectResponse
    {
        $project->update(['archived_at' => null]);

        return redirect()->back()->with('success', "Project '{$project->name}' restored.");
    }
}
