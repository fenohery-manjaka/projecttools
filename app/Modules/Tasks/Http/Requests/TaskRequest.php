<?php

namespace App\Modules\Tasks\Http\Requests;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Enums\TaskStatusCategory;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Models\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Creating and editing a task share the same rules. The project comes from the request,
 * so creating (or moving a task to another project) is authorized here.
 */
class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        $project = $this->project();

        if ($project === null) {
            return true; // the validation rules answer "project not found"
        }

        /** @var Task|null $task */
        $task = $this->route('task');

        if ($task !== null && $task->project_id === $project->id) {
            return true; // the route already checked "update"
        }

        return $user->can('create', [Task::class, $project]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->integer('project_id');

        return [
            'title' => ['required', 'string', 'max:200'],
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'milestone_id' => ['nullable', 'integer', Rule::exists('milestones', 'id')->where('project_id', $projectId)],
            'status_id' => ['required', 'integer', Rule::exists('task_statuses', 'id')],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'assignee_ids' => ['array'],
            'assignee_ids.*' => ['integer', 'distinct'],
            'due_on' => ['nullable', 'date'],
            'estimate_hours' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'blocked_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $project = $this->project();

                if ($project !== null) {
                    $members = $project->members()->pluck('users.id')->push($project->owner_id)->all();
                    $outsiders = array_diff(array_map('intval', (array) $this->input('assignee_ids', [])), $members);

                    if ($outsiders !== []) {
                        $validator->errors()->add('assignee_ids', 'Les responsables doivent être membres du projet.');
                    }
                }

                $status = TaskStatus::query()->find($this->integer('status_id'));

                if ($status?->category === TaskStatusCategory::Blocked && blank($this->input('blocked_reason'))) {
                    $validator->errors()->add('blocked_reason', 'Indiquez la raison du blocage.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'milestone_id.exists' => 'Ce jalon n’appartient pas au projet choisi.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'project_id' => 'projet',
            'milestone_id' => 'jalon',
            'status_id' => 'statut',
            'priority' => 'priorité',
            'assignee_ids' => 'responsables',
            'due_on' => 'échéance',
            'estimate_hours' => 'estimation',
            'blocked_reason' => 'raison du blocage',
        ];
    }

    /**
     * @return list<int>
     */
    public function assigneeIds(): array
    {
        return array_values(array_unique(array_map('intval', (array) $this->validated('assignee_ids', []))));
    }

    /**
     * The chosen status, once validated.
     */
    public function taskStatus(): TaskStatus
    {
        return TaskStatus::query()->findOrFail($this->integer('status_id'));
    }

    private function project(): ?Project
    {
        return Project::query()->find($this->integer('project_id'));
    }
}
