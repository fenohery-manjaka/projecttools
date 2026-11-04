<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import ProjectController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectController';
import FormDialog from '@/components/FormDialog.vue';
import ProjectFields from '@/components/projects/ProjectFields.vue';
import type { ProjectFormOptions } from '@/components/projects/ProjectFields.vue';
import { Button } from '@/components/ui/button';

defineProps<{
    options?: Omit<ProjectFormOptions, 'values'> & {
        defaults: ProjectFormOptions['values'];
    };
}>();

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
    <FormDialog
        v-model:open="open"
        title="Nouveau projet"
        description="Sa page s’ouvre ensuite pour ajouter membres, jalons et tâches."
        options-prop="projectForm"
        :ready="options !== undefined"
    >
        <Form
            v-if="options"
            v-bind="ProjectController.store.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <ProjectFields
                :values="options.defaults"
                :statuses="options.statuses"
                :colors="options.colors"
                :owners="options.owners"
                :errors="errors"
            />
            <div class="flex justify-end gap-2">
                <Button type="button" variant="outline" @click="open = false"
                    >Annuler</Button
                >
                <Button type="submit" :disabled="processing"
                    >Créer le projet</Button
                >
            </div>
        </Form>
    </FormDialog>
</template>
