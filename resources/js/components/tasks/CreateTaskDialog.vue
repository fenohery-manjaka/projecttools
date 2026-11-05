<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import TaskController from '@/actions/App/Modules/Tasks/Http/Controllers/TaskController';
import FormDialog from '@/components/FormDialog.vue';
import TaskFields from '@/components/tasks/TaskFields.vue';
import { Button } from '@/components/ui/button';
import type { TaskFormOptions } from '@/types';

/**
 * "New task" dialog. The page sends its options in the optional prop "taskForm"
 * (TaskFormOptions on the server); after creation the user stays on the page.
 */
defineProps<{
    options?: TaskFormOptions;
}>();

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
    <FormDialog
        v-model:open="open"
        title="Nouvelle tâche"
        options-prop="taskForm"
        :options="options"
        v-slot="{ options: form }"
    >
        <p v-if="form.projects.length === 0" class="empty-state">
            Aucun projet où vous pouvez créer une tâche.
        </p>
        <Form
            v-else
            v-bind="TaskController.store.form()"
            :options="{ preserveScroll: true }"
            class="space-y-5"
            v-slot="{ errors, processing }"
            @success="open = false"
        >
            <TaskFields
                :values="form.defaults"
                :projects="form.projects"
                :statuses="form.statuses"
                :priorities="form.priorities"
                :errors="errors"
            />
            <div class="flex justify-end gap-2">
                <Button type="button" variant="outline" @click="open = false"
                    >Annuler</Button
                >
                <Button type="submit" :disabled="processing"
                    >Créer la tâche</Button
                >
            </div>
        </Form>
    </FormDialog>
</template>
