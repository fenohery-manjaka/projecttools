<script setup lang="ts">
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
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Mes tâches', href: mine() }],
    },
});
</script>

<template>
    <Head title="Mes tâches" />
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
    </div>
</template>
