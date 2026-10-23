import type { LucideIcon } from '@lucide/vue';
import { Circle, Folder, LayoutGrid } from '@lucide/vue';

/**
 * Icons a module may name in its NavigationItem. A new module adds its icon here.
 */
const icons: Record<string, LucideIcon> = {
    Folder,
    LayoutGrid,
};

export function navigationIcon(name: string): LucideIcon {
    return icons[name] ?? Circle;
}
