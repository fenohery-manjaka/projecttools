<?php

namespace App\Modules\Projects\Actions;

use App\Models\User;
use App\Modules\Core\Actions\RecordActivity;
use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\DB;

class CreateProject
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $creator, array $attributes): Project
    {
        return DB::transaction(function () use ($creator, $attributes): Project {
            $project = Project::query()->create($attributes);

            // The owner and the creator always belong to the project.
            $project->members()->syncWithoutDetaching(array_unique([$project->owner_id, $creator->id]));

            $this->recordActivity->handle($creator, "a créé le projet « {$project->name} »", $project);

            return $project;
        });
    }
}
