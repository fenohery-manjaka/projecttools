<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import ProjectController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectController';
import Heading from '@/components/Heading.vue';
import ProjectFields from '@/components/projects/ProjectFields.vue';
import type { ProjectFormValues } from '@/components/projects/ProjectFields.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/projects';

defineProps<{
    defaults: ProjectFormValues;
    statuses: { value: string; label: string }[];
    colors: string[];
    owners: { id: number; name: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projets', href: index() },
            { title: 'Nouveau projet', href: create() },
        ],
    },
});
</script>

<template>
    <Head title="Nouveau projet" />

    <div class="max-w-2xl space-y-6">
        <Heading title="Nouveau projet" />

        <Form
            v-bind="ProjectController.store.form()"
            class="panel space-y-6 p-6"
            v-slot="{ errors, processing }"
        >
            <ProjectFields
                :values="defaults"
                :statuses="statuses"
                :colors="colors"
                :owners="owners"
                :errors="errors"
            />
            <Button type="submit" :disabled="processing"
                >Créer le projet</Button
            >
        </Form>
    </div>
</template>
