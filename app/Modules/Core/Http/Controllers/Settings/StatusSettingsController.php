<?php

namespace App\Modules\Core\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Modules\Core\Statuses\Status;
use App\Modules\Core\Statuses\StatusCategory;
use App\Modules\Core\Statuses\StatusTone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Settings screen of a configurable status list. A module extends it, names its
 * model, page and routes, and declares the routes with ->can('administer').
 *
 * The rules are the same for every list, so validation lives here rather than in
 * one Form Request per module.
 */
abstract class StatusSettingsController extends Controller
{
    /**
     * @return class-string<Status>
     */
    abstract protected function model(): string;

    /**
     * Inertia page, e.g. "settings/ProjectStatuses".
     */
    abstract protected function page(): string;

    /**
     * Name of the index route, e.g. "settings.project-statuses.index".
     */
    abstract protected function indexRoute(): string;

    public function index(): Response
    {
        $model = $this->model();
        /** @var class-string<StatusCategory&\BackedEnum> $categories */
        $categories = $model::categoryEnum();

        return Inertia::render($this->page(), [
            'statuses' => $model::query()->ordered()->get()->map(fn (Status $status): array => [
                ...$status->toSummary(),
                'usage_count' => $status->usageCount(),
            ]),
            'categories' => array_map(fn (StatusCategory&\BackedEnum $category): array => [
                'value' => $category->value,
                'label' => $category->label(),
                'description' => $category->description(),
            ], $categories::cases()),
            'tones' => StatusTone::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $model = $this->model();

        $model::query()->create([
            ...$this->validated($request),
            'position' => (int) $model::query()->max('position') + 1,
        ]);

        return $this->back('Statut ajouté.');
    }

    public function update(Request $request, int $status): RedirectResponse
    {
        $current = $this->find($status);
        $attributes = $this->validated($request);

        if ($attributes['category'] !== $current->category->value) {
            $this->ensureCanLeave($current, 'category');
        }

        $current->update($attributes);

        return $this->back('Statut enregistré.');
    }

    public function move(Request $request, int $status): RedirectResponse
    {
        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $current = $this->find($status);
        $ordered = $this->model()::query()->ordered()->get()->values();
        $index = $ordered->search(fn (Status $other): bool => $other->is($current));
        $target = $request->input('direction') === 'up' ? $index - 1 : $index + 1;

        if (is_int($index) && $ordered->has($target)) {
            $ordered->splice($index, 1);
            $ordered->splice($target, 0, [$current]);
            $this->renumber($ordered->all());
        }

        return to_route($this->indexRoute());
    }

    public function destroy(Request $request, int $status): RedirectResponse
    {
        $current = $this->find($status);
        $this->ensureCanLeave($current, 'status');

        $replacement = null;

        if ($current->usageCount() > 0) {
            $request->validate(
                ['replacement_id' => ['required', 'integer', Rule::exists($current->getTable(), 'id'), Rule::notIn([$current->id])]],
                ['replacement_id.required' => 'Ce statut est utilisé : choisissez celui qui le remplace.'],
                ['replacement_id' => 'statut de remplacement'],
            );

            $replacement = $this->find($request->integer('replacement_id'));
        }

        DB::transaction(function () use ($current, $replacement): void {
            if ($replacement !== null) {
                $current->reassignTo($replacement);
            }

            $current->delete();
            $this->renumber($this->model()::query()->ordered()->get()->all());
        });

        return $this->back("Statut « {$current->name} » supprimé.");
    }

    /**
     * @return array{name: string, tone: string, category: string}
     */
    private function validated(Request $request): array
    {
        /** @var array{name: string, tone: string, category: string} */
        return $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'tone' => ['required', Rule::enum(StatusTone::class)],
            'category' => ['required', Rule::enum($this->model()::categoryEnum())],
        ], attributes: ['name' => 'nom', 'tone' => 'couleur', 'category' => 'comportement']);
    }

    private function find(int $id): Status
    {
        return $this->model()::query()->findOrFail($id);
    }

    /**
     * Refuse to delete (or change the behaviour of) the last status, or the last one of a required category.
     */
    private function ensureCanLeave(Status $status, string $field): void
    {
        $model = $this->model();

        if ($model::query()->count() === 1) {
            throw ValidationException::withMessages([$field => 'Il faut garder au moins un statut.']);
        }

        if (in_array($status->category, $model::requiredCategories(), true)
            && $model::query()->where('category', $status->category->value)->count() === 1) {
            throw ValidationException::withMessages([
                $field => "Il faut garder au moins un statut au comportement « {$status->category->label()} ».",
            ]);
        }
    }

    /**
     * @param  array<int, Status>  $statuses
     */
    private function renumber(array $statuses): void
    {
        foreach (array_values($statuses) as $position => $status) {
            if ($status->position !== $position) {
                $status->update(['position' => $position]);
            }
        }
    }

    private function back(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route($this->indexRoute());
    }
}
