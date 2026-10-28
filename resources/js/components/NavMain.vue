<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { navigationIcon } from '@/lib/navigationIcons';
import type { ModuleNavigationGroup } from '@/types';

defineProps<{
    groups: ModuleNavigationGroup[];
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();

// Sidebar entries as in the prototype: the active one sits on a white card with a honey icon.
const itemClass =
    'h-9 gap-[11px] rounded-[9px] px-2.5 text-[14.5px] font-medium text-ink-2 hover:bg-sunk hover:text-foreground [&>svg]:size-[18px] data-[active=true]:bg-card data-[active=true]:font-semibold data-[active=true]:text-foreground data-[active=true]:shadow-soft data-[active=true]:[&>svg]:text-brand';
</script>

<template>
    <SidebarGroup v-for="group in groups" :key="group.key" class="px-0 py-0">
        <SidebarGroupLabel
            class="h-auto px-2.5 pt-2 pb-1 text-[11px] font-semibold tracking-[0.09em] text-faint uppercase"
            >{{ group.label }}</SidebarGroupLabel
        >
        <SidebarMenu class="gap-0.5">
            <SidebarMenuItem v-for="item in group.items" :key="item.href">
                <SidebarMenuButton
                    as-child
                    :class="itemClass"
                    :is-active="isCurrentOrParentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="navigationIcon(item.icon)" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
