<script setup lang="ts">
import { AlertCircle, Clock3 } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    items: {
        severity: string;
        kind: 'blocked' | 'late';
        label: string;
        detail: string;
        url: string;
        projectName: string;
        daysLate: number | null;
        assignees: string[];
    }[];
}>();
</script>

<template>
    <p v-if="items.length === 0" class="empty-state">Rien à signaler.</p>
    <ul v-else class="divide-y divide-border">
        <li
            v-for="(item, index) in items"
            :key="`${item.url}-${index}`"
            class="flex gap-3 py-3 first:pt-0 last:pb-0"
        >
            <AlertCircle
                v-if="item.kind === 'blocked'"
                class="mt-0.5 size-4 shrink-0 text-destructive"
                aria-hidden="true"
            />
            <Clock3
                v-else
                class="mt-0.5 size-4 shrink-0 text-amber-600"
                aria-hidden="true"
            />
            <div class="min-w-0 flex-1">
                <div
                    class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm"
                >
                    <span class="font-medium">{{
                        item.kind === 'blocked' ? 'Bloquée' : 'En retard'
                    }}</span>
                    <Link
                        :href="item.url"
                        class="truncate text-foreground underline-offset-4 hover:underline"
                        >{{ item.label }}</Link
                    >
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    <template v-if="item.kind === 'blocked'">{{
                        item.detail
                    }}</template>
                    <template v-else
                        >{{ item.daysLate }} j de retard<span
                            v-if="item.assignees.length"
                        >
                            · {{ item.assignees.join(', ') }}</span
                        ></template
                    >
                    <span class="block text-xs">{{ item.projectName }}</span>
                </p>
            </div>
        </li>
    </ul>
</template>
