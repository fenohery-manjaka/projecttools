<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import ProjectController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectController';
import Heading from '@/components/Heading.vue';
import ProjectFields from '@/components/projects/ProjectFields.vue';
import type { ProjectFormValues } from '@/components/projects/ProjectFields.vue';
import { Button } from '@/components/ui/button';

defineProps<{
    project: ProjectFormValues & { id: number; name: string };
    statuses: { value: string; label: string }[];
    colors: string[];
    owners: { id: number; name: string }[];
}>();
</script>

<template>
    <Head :title="`Modifier · ${project.name}`" />

    <div class="max-w-2xl space-y-6 p-4">
        <Heading :title="`Modifier « ${project.name} »`" />

        <Form
            v-bind="ProjectController.update.form(project.id)"
            class="space-y-6"
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
