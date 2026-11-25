<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Plus } from '@lucide/vue';
import { ref } from 'vue';
import AttentionList from '@/components/home/AttentionList.vue';
import AgendaList from '@/components/home/AgendaList.vue';
import CreateTaskDialog from '@/components/tasks/CreateTaskDialog.vue';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { dashboard } from '@/routes';
import { index as projectsIndex } from '@/routes/projects';
import { mine as myTasksIndex, show as taskShow } from '@/routes/tasks';
import type { TaskFormOptions } from '@/types';

type TaskSummary = {
    id: number;
    title: string;
    project: { id: number; name: string; color: string };
    status: { name: string; category: string; tone: string };
    priority: { value: string; label: string };
    due_on: string | null;
    is_late: boolean;
};

const props = defineProps<{
    today: string;
    attention: {
        severity: string;
        kind: 'blocked' | 'late';
        label: string;
        detail: string;
        url: string;
        projectName: string;
        daysLate: number | null;
        assignees: string[];
    }[];
    summary: { count: number };
    myTasks: TaskSummary[];
    agenda: {
        date: string;
        type: 'task_due' | 'milestone' | 'project_end';
        label: string;
        url: string;
        projectName: string;
        isLate: boolean;
        isDone: boolean;
    }[];
    inProgressProjects: number;
    can: { createTask: boolean };
    taskForm?: TaskFormOptions;
}>();

const creatingTask = ref(false);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Accueil', href: dashboard() }],
    },
});

const fullDate = (date: string) =>
    new Date(`${date}T00:00`).toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });
</script>

<template>
    <Head title="Accueil" />

    <div class="space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="space-y-2">
                <p class="text-sm text-muted-foreground capitalize">
                    {{ fullDate(today) }}
                </p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{
                        summary.count > 0
                            ? `${summary.count} élément${summary.count > 1 ? 's' : ''} demand${summary.count > 1 ? 'ent' : 'e'} une intervention.`
                            : 'Rien de bloquant aujourd’hui.'
                    }}
                </h1>
            </div>
            <Button v-if="can.createTask" @click="creatingTask = true"
                ><Plus />Nouvelle tâche</Button
            >
        </header>

        <CreateTaskDialog
            v-if="can.createTask"
            v-model:open="creatingTask"
            :options="taskForm"
        />

        <section class="rounded-2xl border border-border bg-card p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Demande une intervention</h2>
                <span class="text-sm text-muted-foreground">{{
                    summary.count
                }}</span>
            </div>
            <AttentionList :items="attention" />
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section
                class="min-w-0 rounded-2xl border border-border bg-card p-5"
            >
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">Mes prochaines tâches</h2>
                    <Link
                        :href="myTasksIndex()"
                        class="inline-flex shrink-0 items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                        >Tout voir <ArrowRight class="size-4"
                    /></Link>
                </div>
                <p v-if="myTasks.length === 0" class="empty-state">
                    Rien à signaler.
                </p>
                <ul v-else class="divide-y divide-border">
                    <li
                        v-for="task in myTasks"
                        :key="task.id"
                        class="flex min-w-0 items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                    >
                        <div class="min-w-0">
                            <Link
                                :href="taskShow(task.id)"
                                class="block truncate text-sm font-medium hover:underline"
                                >{{ task.title }}</Link
                            >
                            <span class="text-xs text-muted-foreground"
                                >{{ task.project.name }} ·
                                {{ task.status.name }}</span
                            >
                        </div>
                        <time
                            v-if="task.due_on"
                            :datetime="task.due_on"
                            class="shrink-0 text-xs text-muted-foreground"
                            >{{ formatDate(task.due_on) }}</time
                        >
                    </li>
                </ul>
            </section>

            <section
                class="min-w-0 rounded-2xl border border-border bg-card p-5"
            >
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">À venir</h2>
                </div>
                <AgendaList :items="agenda" />
            </section>
        </div>

        <Link
            :href="projectsIndex()"
            class="flex items-center justify-between gap-3 rounded-2xl border border-border bg-card p-5 hover:border-ring"
        >
            <span>
                <span class="block text-sm text-muted-foreground"
                    >Projets en cours</span
                >
                <span class="text-2xl font-semibold">{{
                    inProgressProjects
                }}</span>
            </span>
            <ArrowRight class="size-5 text-muted-foreground" />
        </Link>
    </div>
</template>
