<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MemberAvatar from '@/components/MemberAvatar.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { formatDayMonth } from '@/lib/dates';
import { priorityTone } from '@/lib/tones';
import { show } from '@/routes/tasks';
import type { TaskSummary } from '@/types';

withDefaults(
    defineProps<{
        tasks: TaskSummary[];
        /** Hide the project column (inside a project page). */
        withoutProject?: boolean;
    }>(),
    { withoutProject: false },
);
</script>

<template>
    <!-- On a phone the table scrolls inside its frame, the page does not. -->
    <div class="panel overflow-x-auto">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr
                    class="border-b border-border text-left text-xs text-muted-foreground"
                >
                    <th class="px-4 py-2.5 font-medium">Tâche</th>
                    <th v-if="!withoutProject" class="px-4 py-2.5 font-medium">
                        Projet
                    </th>
                    <th class="px-4 py-2.5 font-medium">Statut</th>
                    <th class="px-4 py-2.5 font-medium">Priorité</th>
                    <th class="px-4 py-2.5 font-medium">Responsables</th>
                    <th class="px-4 py-2.5 font-medium">Échéance</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="task in tasks"
                    :key="task.id"
                    class="border-b border-border last:border-b-0 hover:bg-sunk/60"
                >
                    <td class="max-w-[320px] px-4 py-2.5">
                        <Link
                            :href="show(task.id)"
                            class="block truncate font-medium hover:underline"
                            >{{ task.title }}</Link
                        >
                        <span
                            v-if="task.subtasks && task.subtasks.total > 0"
                            class="text-xs text-muted-foreground"
                            >{{ task.subtasks.done }}/{{
                                task.subtasks.total
                            }}
                            sous-tâches</span
                        >
                    </td>
                    <td v-if="!withoutProject" class="px-4 py-2.5">
                        <span class="flex items-center gap-2 whitespace-nowrap">
                            <span
                                class="size-2.5 shrink-0 rounded-full"
                                :style="{ backgroundColor: task.project.color }"
                                aria-hidden="true"
                            />{{ task.project.name }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        <StatusBadge :status="task.status" />
                    </td>
                    <td class="px-4 py-2.5">
                        <Badge :variant="priorityTone(task.priority.value)">{{
                            task.priority.label
                        }}</Badge>
                    </td>
                    <td class="px-4 py-2.5">
                        <span
                            v-if="task.assignees.length"
                            class="flex -space-x-1.5"
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
                        <span v-else class="text-muted-foreground">—</span>
                    </td>
                    <td class="px-4 py-2.5 whitespace-nowrap">
                        <template v-if="task.due_on">
                            <span
                                :class="
                                    task.is_late && 'font-semibold text-bad'
                                "
                                >{{ formatDayMonth(task.due_on) }}</span
                            >
                            <span
                                v-if="task.is_late"
                                class="ml-1.5 text-xs font-semibold text-bad"
                                >En retard</span
                            >
                        </template>
                        <span v-else class="text-muted-foreground">—</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
