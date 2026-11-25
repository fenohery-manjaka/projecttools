<?php

namespace App\Modules\Tasks\Http\Requests;

use App\Modules\Tasks\Enums\TaskPriority;
use App\Modules\Tasks\Support\TaskFilters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filters of /tasks, kept in the address so a filtered view can be shared or reloaded.
 * Authorization is on the route.
 */
class TaskFilterRequest extends FormRequest
{
    /**
     * An invalid filter comes back to the unfiltered list, with the error.
     */
    protected $redirectRoute = 'tasks.index';

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project' => ['nullable', 'integer'],
            'member' => ['nullable', 'integer'],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'due' => ['nullable', Rule::in(array_keys(TaskFilters::DUE_OPTIONS))],
            'q' => ['nullable', 'string', 'max:100'],
            'view' => ['nullable', Rule::in(['list', 'board'])],
        ];
    }

    /**
     * @return array{project: int|null, member: int|null, priority: string|null, due: string|null, q: string|null}
     */
    public function filters(): array
    {
        return [
            'project' => $this->filled('project') ? $this->integer('project') : null,
            'member' => $this->filled('member') ? $this->integer('member') : null,
            'priority' => $this->string('priority')->toString() ?: null,
            'due' => $this->string('due')->toString() ?: null,
            'q' => trim($this->string('q')->toString()) ?: null,
        ];
    }

    public function view(): string
    {
        return $this->input('view') === 'board' ? 'board' : 'list';
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'project' => 'projet',
            'member' => 'membre',
            'priority' => 'priorité',
            'due' => 'échéance',
            'q' => 'recherche',
            'view' => 'vue',
        ];
    }
}
