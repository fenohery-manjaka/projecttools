<script setup lang="ts">
<<<<<<< HEAD
import { Head, Link } from '@inertiajs/vue3';
import { mine, show } from '@/routes/tasks';

defineProps<{
    tasks: {
        id: number;
        title: string;
        project: { id: number; name: string; color: string };
        status: { name: string; category: string; tone: string };
        priority: { value: string; label: string };
        due_on: string | null;
        is_late: boolean;
    }[];
=======
import { Head } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import CreateTaskDialog from '@/components/tasks/CreateTaskDialog.vue';
import TaskRow from '@/components/tasks/TaskRow.vue';
import { Button } from '@/components/ui/button';
import { mine } from '@/routes/tasks';
import type { TaskFormOptions, TaskSummary } from '@/types';

const props = defineProps<{
    groups: { key: string; label: string; tasks: TaskSummary[] }[];
    openCount: number;
    recentlyDone: TaskSummary[];
    load: {
        byPriority: { value: string; label: string; count: number }[];
        remainingHours: number;
    };
    tickStatuses: { done: number; todo: number } | null;
    can: { create: boolean };
    taskForm?: TaskFormOptions;
>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Mes tâches', href: mine() }],
    },
});
<<<<<<< HEAD
=======

const creating = ref(false);

const description = computed(() => {
    if (props.openCount === 0) {
        return 'Rien d’ouvert pour vous.';
    }

    const count = `${props.openCount} tâche${props.openCount > 1 ? 's' : ''} ouverte${props.openCount > 1 ? 's' : ''}.`;

    return props.tickStatuses ? `${count} Cochez pour terminer.` : count;
});
>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
</script>

<template>
    <Head title="Mes tâches" />
<<<<<<< HEAD
    <div class="space-y-6">
        <header>
            <h1 class="text-2xl font-semibold tracking-tight">Mes tâches</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Mes tâches ouvertes dans les projets visibles.
            </p>
        </header>
        <p v-if="tasks.length === 0" class="empty-state">
            Aucune tâche ouverte. Profitez-en pour faire avancer une idée.
        </p>
        <ul
            v-else
            class="divide-y divide-border rounded-2xl border border-border bg-card px-5"
        >
            <li
                v-for="task in tasks"
                :key="task.id"
                class="flex flex-wrap items-center justify-between gap-3 py-4"
            >
                <div class="min-w-0">
                    <Link
                        :href="show(task.id)"
                        class="font-medium hover:underline"
                        >{{ task.title }}</Link
                    >
                    <p class="text-sm text-muted-foreground">
                        {{ task.project.name }} · {{ task.status.name }} ·
                        {{ task.priority.label }}
                    </p>
                </div>
                <time
                    v-if="task.due_on"
                    :datetime="task.due_on"
                    class="text-sm"
                    :class="
                        task.is_late
                            ? 'text-destructive'
                            : 'text-muted-foreground'
                    "
                    >{{ task.due_on }}</time
                >
            </li>
        </ul>
=======

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <Heading title="Mes tâches" :description="description" />
            <Button v-if="can.create" @click="creating = true"
                ><Plus />Nouvelle tâche</Button
            >
        </div>

        <CreateTaskDialog
            v-if="can.create"
            v-model:open="creating"
            :options="taskForm"
        />

        <div class="grid items-start gap-4 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-4">
                <p v-if="groups.length === 0" class="empty-state">
                    Aucune tâche ouverte. Profitez-en pour faire avancer une
                    idée.
                </p>

                <section
                    v-for="group in groups"
                    :key="group.key"
                    class="panel p-5"
                    :aria-labelledby="`group-${group.key}`"
                >
                    <h2
                        :id="`group-${group.key}`"
                        :class="[
                            'mb-1 text-[15px] font-semibold',
                            group.key === 'late' && 'text-bad',
                        ]"
                    >
                        {{ group.label }}
                        <span class="font-normal text-muted-foreground"
                            >· {{ group.tasks.length }}</span
                        >
                    </h2>
                    <ul>
                        <TaskRow
                            v-for="task in group.tasks"
                            :key="task.id"
                            :task="task"
                            :tick-statuses="tickStatuses"
                        />
                    </ul>
                </section>

                <section
                    v-if="recentlyDone.length"
                    class="panel p-5"
                    aria-labelledby="group-done"
                >
                    <h2
                        id="group-done"
                        class="mb-1 text-[15px] font-semibold text-muted-foreground"
                    >
                        Terminées récemment
                    </h2>
                    <ul>
                        <TaskRow
                            v-for="task in recentlyDone"
                            :key="task.id"
                            :task="task"
                            :tick-statuses="tickStatuses"
                        />
                    </ul>
                </section>
            </div>

            <aside class="panel space-y-3 p-5" aria-labelledby="my-load">
                <Heading id="my-load" variant="small" title="Ma charge" />
                <dl class="grid gap-2 text-sm">
                    <div
                        v-for="priority in load.byPriority"
                        :key="priority.value"
                        class="flex justify-between"
                    >
                        <dt class="text-muted-foreground">
                            {{ priority.label }}
                        </dt>
                        <dd class="font-medium">{{ priority.count }}</dd>
                    </div>
                    <div
                        class="flex justify-between border-t border-border pt-2"
                    >
                        <dt class="text-muted-foreground">
                            Estimation restante
                        </dt>
                        <dd class="font-medium">
                            {{ load.remainingHours.toLocaleString('fr-FR') }} h
                        </dd>
                    </div>
                </dl>
            </aside>
        </div>
>>>>>>> 1262d364762aee46cda10c0c526e9a58395f4ee8
    </div>
</template>
