<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Projects\Http\Requests\MilestoneRequest;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MilestoneController extends Controller
{
    public function store(MilestoneRequest $request, Project $project, RecordActivity $recordActivity): RedirectResponse
    {
        $milestone = $project->milestones()->create($request->safe()->only(['name', 'due_on']));

        $recordActivity->handle($request->user(), "a ajouté le jalon « {$milestone->name} » au projet « {$project->name} »", $project);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Jalon ajouté.']);

        return back();
    }

    public function update(MilestoneRequest $request, Project $project, Milestone $milestone, RecordActivity $recordActivity): RedirectResponse
    {
        $milestone->fill($request->safe()->only(['name', 'due_on']));

        if ($request->has('reached')) {
            $reached = $request->boolean('reached');
            $milestone->reached_at = $reached ? ($milestone->reached_at ?? now()) : null;

            if ($reached && $milestone->isDirty('reached_at')) {
                $recordActivity->handle($request->user(), "a atteint le jalon « {$milestone->name} » du projet « {$project->name} »", $project);
            }
        }

        $milestone->save();

        return back();
    }

    public function destroy(Request $request, Project $project, Milestone $milestone): RedirectResponse
    {
        $milestone->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "Jalon « {$milestone->name} » supprimé."]);

        return back();
    }
}
