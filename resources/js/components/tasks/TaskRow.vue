<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import TaskStatusController from '@/actions/App/Modules/Tasks/Http/Controllers/TaskStatusController';
import { Badge } from '@/components/ui/badge';
import { formatDayMonth } from '@/lib/dates';
import { priorityTone } from '@/lib/tones';
import { show } from '@/routes/tasks';
import type { TaskSummary } from '@/types';

/**
 * A line of My tasks: tick to finish the task, untick to reopen it.
 * Without "tickStatuses" (no update permission) there is no box.
 */
const props = defineProps<{
    task: TaskSummary;
    tickStatuses: { done: number; todo: number } | null;
}>();

const sending = ref(false);

function tick(done: boolean) {
    if (!props.tickStatuses) {
        return;
    }

    sending.value = true;
    router.patch(
        TaskStatusController.update.url(props.task.id),
        {
            status_id: done ? props.tickStatuses.done : props.tickStatuses.todo,
        },
        { preserveScroll: true, onFinish: () => (sending.value = false) },
    );
}
</script>

<template>
    <li
        class="flex items-center gap-3 border-b border-border py-2.5 last:border-b-0"
    >
        <input
            v-if="tickStatuses"
            :id="`my-task-${task.id}`"
            type="checkbox"
            class="size-4 shrink-0 accent-brand"
            :checked="task.status.category === 'done'"
            :disabled="sending"
            :aria-label="
                task.status.category === 'done'
                    ? `Rouvrir « ${task.title} »`
                    : `Terminer « ${task.title} »`
            "
            @change="tick(($event.target as HTMLInputElement).checked)"
        />
        <div class="min-w-0 flex-1">
            <Link
                :href="show(task.id)"
                :class="[
                    'block truncate font-medium hover:underline',
                    task.status.category === 'done' &&
                        'text-muted-foreground line-through',
                ]"
                >{{ task.title }}</Link
            >
            <span
                class="flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <span
                    class="size-2 shrink-0 rounded-full"
                    :style="{ backgroundColor: task.project.color }"
                    aria-hidden="true"
                />{{ task.project.name }}
            </span>
        </div>
        <Badge
            v-if="task.priority.value !== 'normal'"
            :variant="priorityTone(task.priority.value)"
            >{{ task.priority.label }}</Badge
        >
        <span
            v-if="task.due_on"
            :class="[
                'shrink-0 text-xs whitespace-nowrap',
                task.is_late
                    ? 'font-semibold text-bad'
                    : 'text-muted-foreground',
            ]"
            >{{ formatDayMonth(task.due_on)
            }}{{ task.is_late ? ' · En retard' : '' }}</span
        >
    </li>
</template>
