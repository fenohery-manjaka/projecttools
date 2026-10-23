<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Projects\Actions\CreateProject;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Http\Requests\ProjectRequest;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $projects = Project::query()
            ->visibleTo($user)
            ->whereNull('archived_at')
            ->with('owner:id,name')
            ->withCount('members')
            ->latest()
            ->get();

        return Inertia::render('projects/Index', [
            'projects' => $projects->map(fn (Project $project): array => [
                ...$this->summary($project),
                'members_count' => $project->members_count,
            ]),
            'can' => ['create' => $user->can('create', Project::class)],
        ]);
    }

    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('projects/Create', [
            ...$this->formOptions(),
            'defaults' => ['owner_id' => $user->id, 'color' => Project::COLORS[0], 'status' => ProjectStatus::Planned->value],
        ]);
    }

    public function store(ProjectRequest $request, CreateProject $createProject): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $project = $createProject->handle($user, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projet créé.']);

        return to_route('projects.show', $project);
    }

    public function show(Request $request, Project $project): Response
    {
        /** @var User $user */
        $user = $request->user();

        $project->load(['owner:id,name', 'members' => fn ($query) => $query->select('users.id', 'users.name', 'users.email')->orderBy('name')]);

        $canManageMembers = $user->can('manageMembers', $project);

        return Inertia::render('projects/Show', [
            'project' => [
                ...$this->summary($project),
                'objectives' => $project->objectives,
                'starts_on' => $project->starts_on?->toDateString(),
                'members' => $project->members->map(fn (User $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'is_owner' => $member->id === $project->owner_id,
                ]),
            ],
            'availableMembers' => $canManageMembers
                ? User::query()
                    ->whereNull('deactivated_at')
                    ->whereNotIn('id', $project->members->modelKeys())
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : [],
            'can' => [
                'update' => $user->can('update', $project),
                'delete' => $user->can('delete', $project),
                'manageMembers' => $canManageMembers,
            ],
        ]);
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('projects/Edit', [
            ...$this->formOptions(),
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'objectives' => $project->objectives,
                'status' => $project->status->value,
                'owner_id' => $project->owner_id,
                'color' => $project->color,
                'starts_on' => $project->starts_on?->toDateString(),
                'ends_on' => $project->ends_on?->toDateString(),
            ],
        ]);
    }

    public function update(ProjectRequest $request, Project $project, RecordActivity $recordActivity): RedirectResponse
    {
        $project->update($request->validated());
        $project->members()->syncWithoutDetaching([$project->owner_id]);

        $recordActivity->handle($request->user(), "a modifié le projet « {$project->name} »", $project);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projet enregistré.']);

        return to_route('projects.show', $project);
    }

    public function destroy(Request $request, Project $project, RecordActivity $recordActivity): RedirectResponse
    {
        $project->delete();

        $recordActivity->handle($request->user(), "a supprimé le projet « {$project->name} »");

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Projet supprimé.']);

        return to_route('projects.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Project $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'status' => ['value' => $project->status->value, 'label' => $project->status->label()],
            'color' => $project->color,
            'owner' => ['id' => $project->owner->id, 'name' => $project->owner->name],
            'ends_on' => $project->ends_on?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'statuses' => ProjectStatus::options(),
            'colors' => Project::COLORS,
            'owners' => User::query()->whereNull('deactivated_at')->orderBy('name')->get(['id', 'name']),
        ];
    }
}
