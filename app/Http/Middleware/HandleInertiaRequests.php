<?php

namespace App\Http\Middleware;

use App\Modules\Core\ModuleRegistry;
use App\Modules\Core\Navigation\NavigationGroup;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'navigation' => fn (): array => $this->navigation($request),
            // Not named "can": pages share their own "can" prop, which would replace this one.
            'abilities' => fn (): array => [
                'administer' => (bool) $request->user()?->can('administer'),
                'viewMembers' => (bool) $request->user()?->can('members.view'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Sidebar groups built from the enabled modules the user is allowed to see.
     *
     * @return list<array{key: string, label: string, items: list<array{title: string, href: string, icon: string}>}>
     */
    private function navigation(Request $request): array
    {
        $user = $request->user();

        if ($user === null) {
            return [];
        }

        $groups = [];

        foreach ($this->modules->navigationFor($user) as $item) {
            $groups[$item->group->value] ??= ['key' => $item->group->value, 'label' => $item->group->label(), 'items' => []];
            $groups[$item->group->value]['items'][] = [
                'title' => $item->title,
                'href' => route($item->route, absolute: false),
                'icon' => $item->icon,
            ];
        }

        return array_values(array_filter(array_map(
            fn (NavigationGroup $group): ?array => $groups[$group->value] ?? null,
            NavigationGroup::cases(),
        )));
    }
}
