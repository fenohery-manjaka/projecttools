<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import ProjectController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectController';
import Heading from '@/components/Heading.vue';
import ProjectFields from '@/components/projects/ProjectFields.vue';
import type { ProjectFormValues } from '@/components/projects/ProjectFields.vue';
import { Button } from '@/components/ui/button';
import { edit, index, show } from '@/routes/projects';

const props = defineProps<{
    project: ProjectFormValues & { id: number; name: string };
    statuses: { value: string; label: string }[];
    colors: string[];
    owners: { id: number; name: string }[];
}>();

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Projets', href: index() },
            { title: props.project.name, href: show(props.project.id) },
            { title: 'Modifier', href: edit(props.project.id) },
        ],
    });
});
</script>

<template>
    <Head :title="`Modifier · ${project.name}`" />

    <div class="max-w-2xl space-y-6">
        <Heading :title="`Modifier « ${project.name} »`" />

        <Form
            v-bind="ProjectController.update.form(project.id)"
            class="panel space-y-6 p-6"
            v-slot="{ errors, processing }"
        >
            <ProjectFields
                :values="project"
                :statuses="statuses"
                :colors="colors"
                :owners="owners"
                :errors="errors"
            />
            <Button type="submit" :disabled="processing">Enregistrer</Button>
        </Form>
    </div>
</template>
