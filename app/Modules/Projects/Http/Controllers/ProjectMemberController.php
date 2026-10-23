<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Projects\Http\Requests\StoreProjectMemberRequest;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProjectMemberController extends Controller
{
    public function store(StoreProjectMemberRequest $request, Project $project, RecordActivity $recordActivity): RedirectResponse
    {
        $member = User::query()->findOrFail($request->integer('user_id'));

        $project->members()->syncWithoutDetaching([$member->id]);

        $recordActivity->handle($request->user(), "a ajouté {$member->name} au projet « {$project->name} »", $project);

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$member->name} rejoint le projet."]);

        return back();
    }

    public function destroy(Request $request, Project $project, User $member, RecordActivity $recordActivity): RedirectResponse
    {
        if ($member->id === $project->owner_id) {
            throw ValidationException::withMessages(['member' => 'Le responsable du projet ne peut pas en être retiré.']);
        }

        $project->members()->detach($member->id);

        $recordActivity->handle($request->user(), "a retiré {$member->name} du projet « {$project->name} »", $project);

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$member->name} a été retiré du projet."]);

        return back();
    }
}
