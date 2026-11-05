<script setup lang="ts">
import { Form, Head, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import TaskController from '@/actions/App/Modules/Tasks/Http/Controllers/TaskController';
import Heading from '@/components/Heading.vue';
import TaskFields from '@/components/tasks/TaskFields.vue';
import { Button } from '@/components/ui/button';
import { edit, show } from '@/routes/tasks';
import type { TaskFormOptions, TaskFormValues } from '@/types';

const props = defineProps<
    Omit<TaskFormOptions, 'defaults'> & {
        task: TaskFormValues & { id: number; title: string };
    }
>();

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: props.task.title, href: show(props.task.id) },
            { title: 'Modifier', href: edit(props.task.id) },
        ],
    });
});
</script>

<template>
    <Head :title="`Modifier · ${task.title}`" />

    <div class="max-w-2xl space-y-6">
        <Heading :title="`Modifier « ${task.title} »`" />

        <Form
            v-bind="TaskController.update.form(task.id)"
            class="panel space-y-5 p-6"
            v-slot="{ errors, processing }"
        >
            <TaskFields
                :values="task"
                :projects="projects"
                :statuses="statuses"
                :priorities="priorities"
                :errors="errors"
            />
            <Button type="submit" :disabled="processing">Enregistrer</Button>
        </Form>
    </div>
</template>
