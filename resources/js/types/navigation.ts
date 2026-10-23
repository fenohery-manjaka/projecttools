import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

/**
 * Sidebar group shared by HandleInertiaRequests, built from the enabled modules.
 */
export type ModuleNavigationGroup = {
    key: string;
    label: string;
    items: {
        title: string;
        href: string;
        icon: string;
    }[];
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
};
