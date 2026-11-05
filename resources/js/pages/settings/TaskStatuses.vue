<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TaskStatusController from '@/actions/App/Modules/Tasks/Http/Controllers/Settings/TaskStatusController';
import Heading from '@/components/Heading.vue';
import StatusManager from '@/components/settings/StatusManager.vue';
import { index } from '@/routes/settings/task-statuses';
import type { Status } from '@/types';

defineProps<{
    statuses: (Status & { usage_count: number })[];
    categories: { value: string; label: string; description: string }[];
    tones: { value: string; label: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Statuts des tâches', href: index() }],
    },
});

const actions = {
    store: TaskStatusController.store.url(),
    update: (id: number) => TaskStatusController.update.url(id),
    move: (id: number) => TaskStatusController.move.url(id),
    destroy: (id: number) => TaskStatusController.destroy.url(id),
};
</script>

<template>
    <Head title="Statuts des tâches" />

    <h1 class="sr-only">Statuts des tâches</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Statuts des tâches"
            description="L’ordre ci-dessous est celui des colonnes du tableau Kanban et des listes. Le comportement dit ce que l’application fait d’un statut, quel que soit son nom."
        />

        <StatusManager
            :statuses="statuses"
            :categories="categories"
            :tones="tones"
            noun="tâche"
            :actions="actions"
        />
    </div>
</template>
