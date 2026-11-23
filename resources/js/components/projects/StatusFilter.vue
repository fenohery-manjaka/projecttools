<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import StatusBadge from '@/components/StatusBadge.vue';
import { index } from '@/routes/projects';
import type { Status } from '@/types';

type StatusWithCount = Status & { count: number };

defineProps<{
    statuses: StatusWithCount[];
    counts: { all: number; archived: number };
    current: number | 'archived' | null;
}>();

function visit(status?: number | 'archived') {
    router.get(
        status === undefined ? index.url() : index.url({ query: { status } }),
        {},
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <div class="flex flex-wrap gap-2" aria-label="Filtrer les projets">
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm transition-colors"
            :class="
                current === null
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border bg-card hover:border-line-2'
            "
            :aria-current="current === null ? 'true' : undefined"
            @click="visit()"
        >
            Tous <span class="opacity-70">{{ counts.all }}</span>
        </button>
        <button
            v-for="status in statuses"
            :key="status.id"
            type="button"
            class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm transition-colors"
            :class="
                current === status.id
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border bg-card hover:border-line-2'
            "
            :aria-current="current === status.id ? 'true' : undefined"
            @click="visit(status.id)"
        >
            <StatusBadge :status="status" />
            <span class="opacity-70">{{ status.count }}</span>
        </button>
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm transition-colors"
            :class="
                current === 'archived'
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border bg-card hover:border-line-2'
            "
            :aria-current="current === 'archived' ? 'true' : undefined"
            @click="visit('archived')"
        >
            Archivés <span class="opacity-70">{{ counts.archived }}</span>
        </button>
    </div>
</template>
