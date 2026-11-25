<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\User;
use App\Modules\Core\Agenda\AgendaItemType;
use App\Modules\Core\ModuleRegistry;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use App\Modules\Tasks\TasksModule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Modules\Core\InteractsWithModules;
use Tests\TestCase;

class TaskAgendaTest extends TestCase
{
    use InteractsWithModules, RefreshDatabase;

    public function test_agenda_includes_only_open_due_tasks_in_the_period_for_visible_projects(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00'));
        $user = User::factory()->create(['name' => 'Tojo']);
        $visibleProject = Project::factory()->withMember($user)->create();
        $archivedProject = Project::factory()->withMember($user)->archived()->create();
        $invisibleProject = Project::factory()->create();
        Task::factory()->for($visibleProject)->dueOn('2026-11-27')->create(['title' => 'Échéance visible']);
        Task::factory()->for($visibleProject)->done()->dueOn('2026-11-28')->create(['title' => 'Tâche terminée']);
        Task::factory()->for($visibleProject)->dueOn('2026-12-11')->create(['title' => 'Hors période']);
        Task::factory()->for($archivedProject)->dueOn('2026-11-29')->create(['title' => 'Projet archivé']);
        Task::factory()->for($invisibleProject)->dueOn('2026-11-30')->create(['title' => 'Projet invisible']);

        $items = app(TasksModule::class)->agenda($user, CarbonImmutable::today(), CarbonImmutable::today()->addDays(14));

        $this->assertSame(['Échéance visible'], array_map(fn ($item): string => $item->label, $items));
    }

    public function test_attention_includes_block_reason_and_late_days_with_assignees(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00'));
        $user = User::factory()->create(['name' => 'Tojo']);
        $secondAssignee = User::factory()->create(['name' => 'Fanja']);
        $project = Project::factory()->withMember($user)->create();
        Task::factory()->for($project)->blocked()->create([
            'title' => 'Tâche bloquée',
            'blocked_reason' => 'Validation en attente',
        ]);
        Task::factory()->for($project)->dueOn('2026-11-22')->assignedTo($user, $secondAssignee)->create(['title' => 'Tâche en retard']);
        Task::factory()->for(Project::factory()->withMember($user)->archived()->create())->dueOn('2026-11-22')->create(['title' => 'Projet archivé']);
        Task::factory()->for(Project::factory()->create())->dueOn('2026-11-22')->create(['title' => 'Projet invisible']);

        $items = app(TasksModule::class)->attention($user);
        $blocked = collect($items)->firstWhere('label', 'Tâche bloquée');
        $late = collect($items)->firstWhere('label', 'Tâche en retard');

        $this->assertSame('Validation en attente', $blocked->detail);
        $this->assertSame(3, $late->daysLate);
        $this->assertSame(['Fanja', 'Tojo'], $late->assignees);
        $this->assertCount(2, $items);
    }

    public function test_renamed_and_new_blocked_statuses_still_appear_in_attention(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->withMember($user)->create();
        $renamedStatus = TaskStatus::query()
            ->where('category', TaskStatusCategory::Blocked->value)
            ->firstOrFail();
        $renamedStatus->update(['name' => 'En attente spéciale']);
        $newStatus = TaskStatus::query()->create([
            'name' => 'Bloquée par le client',
            'tone' => 'bad',
            'category' => TaskStatusCategory::Blocked,
            'position' => 99,
        ]);
        Task::factory()->for($project)->create([
            'title' => 'Statut renommé',
            'status_id' => $renamedStatus->id,
            'blocked_reason' => 'Raison A',
        ]);
        Task::factory()->for($project)->create([
            'title' => 'Nouveau statut bloqué',
            'status_id' => $newStatus->id,
            'blocked_reason' => 'Raison B',
        ]);

        $items = app(TasksModule::class)->attention($user);

        $this->assertEqualsCanonicalizing(['Statut renommé', 'Nouveau statut bloqué'], array_map(fn ($item): string => $item->label, $items));
    }

    public function test_attention_query_count_stays_constant_as_task_count_grows(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->withMember($user)->create();
        Task::factory()->for($project)->count(3)->dueOn(today()->subDay()->toDateString())->create();
        $queryCount = 0;
        DB::listen(function (QueryExecuted $query) use (&$queryCount): void {
            if (str_starts_with(strtolower(ltrim($query->sql)), 'select')) {
                $queryCount++;
            }
        });

        app(TasksModule::class)->attention($user);
        $threeTaskQueryCount = $queryCount;

        Task::factory()->for($project)->count(27)->dueOn(today()->subDay()->toDateString())->create();
        $queryCount = 0;
        app(TasksModule::class)->attention($user);

        $this->assertSame($threeTaskQueryCount, $queryCount);
    }

    public function test_registry_combines_task_due_dates_milestones_and_project_ends_in_date_order(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00'));
        $user = User::factory()->create();
        $project = Project::factory()->withMember($user)->create(['ends_on' => '2026-12-02']);
        Milestone::factory()->for($project)->create(['name' => 'Jalon', 'due_on' => '2026-11-29']);
        Task::factory()->for($project)->dueOn('2026-11-27')->create(['title' => 'Échéance']);

        $items = app(ModuleRegistry::class)->agendaFor($user, CarbonImmutable::today(), CarbonImmutable::today()->addDays(14));

        $this->assertSame([
            AgendaItemType::TaskDue,
            AgendaItemType::Milestone,
            AgendaItemType::ProjectEnd,
        ], $items->pluck('type')->all());
    }
}
