<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectArchiveController extends Controller
{
    public function store(Request $request, Project $project, RecordActivity $recordActivity): RedirectResponse
    {
        if ($project->archived_at === null) {
            $project->archived_at = now();
            $project->save();
            $recordActivity->handle($request->user(), "a archivé le projet « {$project->name} »", $project);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projet archivé.']);

        return back();
    }

    public function destroy(Request $request, Project $project, RecordActivity $recordActivity): RedirectResponse
    {
        if ($project->archived_at !== null) {
            $project->archived_at = null;
            $project->save();
            $recordActivity->handle($request->user(), "a désarchivé le projet « {$project->name} »", $project);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projet désarchivé.']);

        return back();
    }
}
