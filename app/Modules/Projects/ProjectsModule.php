<?php

namespace App\Modules\Projects;

use App\Models\User;
use App\Modules\Core\Agenda\AgendaItem;
use App\Modules\Core\Agenda\AgendaItemType;
use App\Modules\Core\Module;
use App\Modules\Core\Navigation\NavigationGroup;
use App\Modules\Core\Navigation\NavigationItem;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use Carbon\CarbonImmutable;

/**
 * Reference module: copy its structure when building a new one (docs/architecture.md).
 */
class ProjectsModule extends Module
{
    public function key(): string
    {
        return 'projects';
    }

    public function name(): string
    {
        return 'Projets';
    }

    public function description(): string
    {
        return 'Projets, responsables et membres.';
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function permissions(): array
    {
        return [
            'projects.view' => 'Voir les projets dont on est membre',
            'projects.view-all' => 'Voir tous les projets',
            'projects.create' => 'Créer un projet',
            'projects.update' => 'Modifier un projet',
            'projects.delete' => 'Supprimer un projet',
            'projects.manage-members' => 'Gérer les membres d’un projet',
        ];
    }

    public function navigation(): array
    {
        return [
            new NavigationItem(
                title: 'Projets',
                route: 'projects.index',
                icon: 'Folder',
                group: NavigationGroup::Work,
                ability: 'viewAny',
                abilityArguments: [Project::class],
                order: 20,
            ),
        ];
    }

    /**
     * @return list<AgendaItem>
     */
    public function agenda(User $user, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $milestones = Milestone::query()
            ->whereNull('reached_at')
            ->whereBetween('due_on', [$from->toDateString(), $to->toDateString()])
            ->whereHas('project', fn ($query) => $query->visibleTo($user)->whereNull('archived_at'))
            ->with('project:id,name,color')
            ->get()
            ->map(fn (Milestone $milestone): AgendaItem => new AgendaItem(
                $milestone->due_on->toDateString(), null, AgendaItemType::Milestone,
                $milestone->name, route('projects.show', $milestone->project),
                $milestone->project->color, $milestone->is_overdue, false,
                $milestone->project->name,
            ));

        $projectEnds = Project::query()
            ->visibleTo($user)
            ->whereNull('archived_at')
            ->whereBetween('ends_on', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(fn (Project $project): AgendaItem => new AgendaItem(
                $project->ends_on->toDateString(), null, AgendaItemType::ProjectEnd,
                $project->name, route('projects.show', $project), $project->color,
                false, false, $project->name,
            ));

        return array_values($milestones->concat($projectEnds)->all());
    }
}
