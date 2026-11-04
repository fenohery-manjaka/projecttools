<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ProjectStatusController from '@/actions/App/Modules/Projects/Http/Controllers/Settings/ProjectStatusController';
import Heading from '@/components/Heading.vue';
import StatusManager from '@/components/settings/StatusManager.vue';
import { index } from '@/routes/settings/project-statuses';
import type { Status } from '@/types';

defineProps<{
    statuses: (Status & { usage_count: number })[];
    categories: { value: string; label: string; description: string }[];
    tones: { value: string; label: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Statuts des projets', href: index() }],
    },
});

const actions = {
    store: ProjectStatusController.store.url(),
    update: (id: number) => ProjectStatusController.update.url(id),
    move: (id: number) => ProjectStatusController.move.url(id),
    destroy: (id: number) => ProjectStatusController.destroy.url(id),
};
</script>

<template>
    <Head title="Statuts des projets" />

    <h1 class="sr-only">Statuts des projets</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Statuts des projets"
            description="L’ordre ci-dessous est celui des listes et des filtres. Le comportement dit ce que l’application fait d’un statut, quel que soit son nom."
        />

        <StatusManager
            :statuses="statuses"
            :categories="categories"
            :tones="tones"
            noun="projet"
            :actions="actions"
        />
    </div>
</template>
