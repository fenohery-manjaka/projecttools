<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { formatDate } from '@/lib/dates';

defineProps<{
    items: {
        date: string;
        type: 'task_due' | 'milestone' | 'project_end';
        label: string;
        url: string;
        projectName: string;
        isLate: boolean;
        isDone: boolean;
    }[];
}>();

const typeLabel: Record<string, string> = {
    task_due: 'Échéance',
    milestone: 'Jalon',
    project_end: 'Fin de projet',
};
</script>

<template>
    <p v-if="items.length === 0" class="empty-state">Rien à signaler.</p>
    <ul v-else class="divide-y divide-border">
        <li
            v-for="(item, index) in items"
            :key="`${item.url}-${index}`"
            class="flex flex-wrap items-center justify-between gap-2 py-3 first:pt-0 last:pb-0"
        >
            <div class="min-w-0">
                <span class="text-xs text-muted-foreground">{{
                    typeLabel[item.type]
                }}</span>
                <Link
                    :href="item.url"
                    class="block truncate text-sm font-medium underline-offset-4 hover:underline"
                    >{{ item.label }}</Link
                >
                <span class="text-xs text-muted-foreground">{{
                    item.projectName
                }}</span>
            </div>
            <time
                :datetime="item.date"
                class="shrink-0 text-sm text-muted-foreground"
                >{{ formatDate(item.date) }}</time
            >
        </li>
    </ul>
</template>
