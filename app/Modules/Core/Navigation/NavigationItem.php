<?php

namespace App\Modules\Core\Navigation;

final readonly class NavigationItem
{
    /**
     * @param  string  $route  Name of the route the entry links to.
     * @param  string  $icon  Lucide icon name, resolved by resources/js/lib/navigationIcons.ts.
     * @param  string|null  $ability  Gate ability the user needs to see the entry.
     * @param  array<int, mixed>  $abilityArguments  Arguments passed to the gate (for example a model class).
     */
    public function __construct(
        public string $title,
        public string $route,
        public string $icon,
        public NavigationGroup $group,
        public ?string $ability = null,
        public array $abilityArguments = [],
        public int $order = 100,
    ) {}
}
