<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <!-- Sticky, translucent top bar of the prototype. -->
    <header
        class="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-2 border-b border-border bg-background/88 px-4 backdrop-blur-[10px] md:px-8"
    >
        <div class="flex min-w-0 items-center gap-2">
            <SidebarTrigger class="-ml-1 text-ink-2" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
    </header>
</template>
