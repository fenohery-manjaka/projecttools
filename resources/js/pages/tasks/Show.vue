<script setup lang="ts">
import { Head, Link, router, setLayoutProps } from '@inertiajs/vue3';
import { TriangleAlert } from '@lucide/vue';
import { watchEffect } from 'vue';
import TaskController from '@/actions/App/Modules/Tasks/Http/Controllers/TaskController';
import Heading from '@/components/Heading.vue';
import MemberAvatar from '@/components/MemberAvatar.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import TaskStatusButtons from '@/components/tasks/TaskStatusButtons.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { priorityTone } from '@/lib/tones';
import { index as projectsIndex, show as showProject } from '@/routes/projects';
import { edit, show } from '@/routes/tasks';
import type { Status, TaskSummary } from '@/types';

type Task = TaskSummary & {
    description: string | null;
    blocked_reason: string | null;
    estimate_hours: string | null;
    milestone: { id: number; name: string; due_on: string } | null;
    creator: string | null;
    created_at: string | null;
    completed_at: string | null;
};

const props = defineProps<{
    task: Task;
    statuses: Status[];
    can: { update: boolean; delete: boolean };
}>();

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Projets', href: projectsIndex() },
            {
                title: props.task.project.name,
                href: showProject(props.task.project.id),
            },
            { title: props.task.title, href: show(props.task.id) },
        ],
    });
});

function destroy() {
    if (window.confirm(`Supprimer la tâche « ${props.task.title} » ?`)) {
        router.delete(TaskController.destroy.url(props.task.id));
    }
}
</script>

<template>
    <Head :title="task.title" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <Link
                    :href="showProject(task.project.id)"
                    class="inline-flex items-center gap-2 text-[13px] font-medium text-muted-foreground hover:text-foreground"
                >
                    <span
                        class="size-2.5 rounded-full"
                        :style="{ backgroundColor: task.project.color }"
                        aria-hidden="true"
                    />{{ task.project.name }}
                </Link>
                <h1 class="text-[28px] leading-[1.15]">{{ task.title }}</h1>
                <p class="flex flex-wrap items-center gap-2">
                    <StatusBadge :status="task.status" />
                    <Badge :variant="priorityTone(task.priority.value)"
                        >Priorité {{ task.priority.label.toLowerCase() }}</Badge
                    >
                    <Badge v-if="task.is_late" variant="bad">En retard</Badge>
                </p>
            </div>
            <div class="flex gap-2">
                <Button v-if="can.update" variant="outline" as-child>
                    <Link :href="edit(task.id)">Modifier</Link>
                </Button>
                <Button v-if="can.delete" variant="destructive" @click="destroy"
                    >Supprimer</Button
                >
            </div>
        </div>

        <TaskStatusButtons
            v-if="can.update"
            :task-id="task.id"
            :current="task.status"
            :statuses="statuses"
        />

        <div
            v-if="task.status.category === 'blocked' && task.blocked_reason"
            class="flex gap-3 rounded-xl border border-bad/30 bg-bad/8 p-4"
            role="note"
        >
            <TriangleAlert class="mt-0.5 size-5 shrink-0 text-bad" />
            <div>
                <p class="font-semibold text-bad">Bloquée</p>
                <p class="whitespace-pre-line text-ink-2">
                    {{ task.blocked_reason }}
                </p>
            </div>
        </div>

        <div class="grid items-start gap-4 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-4">
                <section class="panel space-y-2 p-5">
                    <Heading variant="small" title="Description" />
                    <p
                        v-if="task.description"
                        class="whitespace-pre-line text-ink-2"
                    >
                        {{ task.description }}
                    </p>
                    <p v-else class="empty-state">Pas de description.</p>
                </section>
            </div>

            <section class="panel p-5">
                <dl class="grid gap-3.5 text-sm">
                    <div>
                        <dt class="text-muted-foreground">Responsables</dt>
                        <dd v-if="task.assignees.length" class="mt-1.5 space-y-1.5">
                            <span
                                v-for="assignee in task.assignees"
                                :key="assignee.id"
                                class="flex items-center gap-2"
                            >
                                <MemberAvatar
                                    :id="assignee.id"
                                    :name="assignee.name"
                                    size="xs"
                                />{{ assignee.name }}
                            </span>
                        </dd>
                        <dd v-else class="mt-1">Personne</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Échéance</dt>
                        <dd class="mt-1">
                            <template v-if="task.due_on"
                                >{{ formatDate(task.due_on, 'long')
                                }}<span
                                    v-if="task.is_late"
                                    class="font-semibold text-bad"
                                >
                                    · en retard</span
                                ></template
                            >
                            <template v-else>Aucune</template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Jalon</dt>
                        <dd class="mt-1">
                            <template v-if="task.milestone"
                                >{{ task.milestone.name }} ·
                                {{ formatDate(task.milestone.due_on) }}</template
                            >
                            <template v-else>Aucun</template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Estimation</dt>
                        <dd class="mt-1">
                            {{
                                task.estimate_hours
                                    ? `${Number(task.estimate_hours)} h`
                                    : 'Aucune'
                            }}
                        </dd>
                    </div>
                    <div v-if="task.creator">
                        <dt class="text-muted-foreground">Créée par</dt>
                        <dd class="mt-1">{{ task.creator }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</template>
