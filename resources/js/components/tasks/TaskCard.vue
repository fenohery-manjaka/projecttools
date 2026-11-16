<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { EllipsisVertical } from '@lucide/vue';
import MemberAvatar from '@/components/MemberAvatar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { formatDayMonth } from '@/lib/dates';
import { priorityTone } from '@/lib/tones';
import { show } from '@/routes/tasks';
import type { Status, TaskSummary } from '@/types';

defineProps<{
    task: TaskSummary;
    statuses: Status[];
    canMove: boolean;
}>();

const emit = defineEmits<{
    moveTo: [status: Status];
}>();
</script>

<template>
    <article
        class="group relative rounded-xl border border-border bg-card p-3 shadow-soft"
        :draggable="canMove"
    >
        <div class="flex items-start gap-2">
            <Link
                :href="show(task.id)"
                class="min-w-0 flex-1 text-[14px] leading-snug font-medium after:absolute after:inset-0 after:content-['']"
                draggable="false"
                >{{ task.title }}</Link
            >
            <DropdownMenu v-if="canMove">
                <DropdownMenuTrigger as-child>
                    <Button
                        size="icon-sm"
                        variant="ghost"
                        class="relative z-10 -mt-1 -mr-1 size-7"
                        :aria-label="`Déplacer « ${task.title} » vers…`"
                        ><EllipsisVertical
                    /></Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>Déplacer vers…</DropdownMenuLabel>
                    <DropdownMenuItem
                        v-for="status in statuses"
                        :key="status.id"
                        :disabled="status.id === task.status.id"
                        @select="emit('moveTo', status)"
                        >{{ status.name }}</DropdownMenuItem
                    >
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <p class="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground">
            <span
                class="size-2 shrink-0 rounded-full"
                :style="{ backgroundColor: task.project.color }"
                aria-hidden="true"
            /><span class="truncate">{{ task.project.name }}</span>
        </p>

        <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
            <Badge
                v-if="['urgent', 'high'].includes(task.priority.value)"
                :variant="priorityTone(task.priority.value)"
                >{{ task.priority.label }}</Badge
            >
            <span
                v-if="task.due_on"
                :class="[
                    'text-xs',
                    task.is_late
                        ? 'font-semibold text-bad'
                        : 'text-muted-foreground',
                ]"
                >{{ formatDayMonth(task.due_on)
                }}{{ task.is_late ? ' · En retard' : '' }}</span
            >
            <span
                v-if="task.subtasks && task.subtasks.total > 0"
                class="text-xs text-muted-foreground"
                :aria-label="`${task.subtasks.done} sous-tâches faites sur ${task.subtasks.total}`"
                >☑ {{ task.subtasks.done }}/{{ task.subtasks.total }}</span
            >
            <span
                v-if="task.assignees.length"
                class="ml-auto flex -space-x-1.5"
            >
                <MemberAvatar
                    v-for="assignee in task.assignees"
                    :id="assignee.id"
                    :key="assignee.id"
                    :name="assignee.name"
                    :title="assignee.name"
                    size="xs"
                    class="ring-2 ring-card"
                />
            </span>
        </div>
    </article>
</template>
