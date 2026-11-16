import type { LucideIcon } from '@lucide/vue';
import {
    Circle,
    CircleCheck,
    Folder,
    LayoutGrid,
    ListChecks,
} from '@lucide/vue';

/**
 * Icons a module may name in its NavigationItem. A new module adds its icon here.
 */
const icons: Record<string, LucideIcon> = {
    CircleCheck,
    Folder,
    LayoutGrid,
    ListChecks,
};

export function navigationIcon(name: string): LucideIcon {
    return icons[name] ?? Circle;
}
