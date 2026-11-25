<script setup lang="ts">
import { Form, router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import SubtaskController from '@/actions/App/Modules/Tasks/Http/Controllers/SubtaskController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export type Subtask = { id: number; title: string; done: boolean };

const props = defineProps<{
    taskId: number;
    subtasks: Subtask[];
    canManage: boolean;
}>();

// Ticked boxes react at once; the server answer then replaces this local copy.
const optimistic = ref<Record<number, boolean>>({});
const editing = ref<number | null>(null);

const doneCount = computed(
    () => props.subtasks.filter((s) => optimistic.value[s.id] ?? s.done).length,
);

function params(subtask: Subtask) {
    return { task: props.taskId, subtask: subtask.id };
}

const reloadOptions = {
    preserveScroll: true,
    preserveState: true,
    only: ['task'],
};

function toggle(subtask: Subtask, done: boolean) {
    optimistic.value[subtask.id] = done;
    router.patch(
        SubtaskController.update.url(params(subtask)),
        { done },
        {
            ...reloadOptions,
            onFinish: () => delete optimistic.value[subtask.id],
        },
    );
}

function rename(subtask: Subtask, title: string) {
    editing.value = null;

    if (title.trim() !== '' && title !== subtask.title) {
        router.patch(
            SubtaskController.update.url(params(subtask)),
            { title },
            reloadOptions,
        );
    }
}

function destroy(subtask: Subtask) {
    router.delete(
        SubtaskController.destroy.url(params(subtask)),
        reloadOptions,
    );
}
</script>

<template>
    <section class="panel space-y-3 p-5">
        <div class="flex items-center justify-between gap-2">
            <Heading variant="small" title="Sous-tâches et vérifications" />
            <span
                v-if="subtasks.length"
                class="text-[13px] text-muted-foreground"
                :aria-label="`${doneCount} faites sur ${subtasks.length}`"
                >{{ doneCount }}/{{ subtasks.length }}</span
            >
        </div>

        <p v-if="subtasks.length === 0" class="empty-state">
            Aucune sous-tâche.
        </p>

        <ul v-else class="flex flex-col">
            <li
                v-for="subtask in subtasks"
                :key="subtask.id"
                class="group flex items-center gap-3 border-b border-border py-2 last:border-b-0"
            >
                <input
                    :id="`subtask-${subtask.id}`"
                    type="checkbox"
                    class="size-4 accent-brand"
                    :checked="optimistic[subtask.id] ?? subtask.done"
                    :disabled="!canManage"
                    @change="
                        toggle(
                            subtask,
                            ($event.target as HTMLInputElement).checked,
                        )
                    "
                />
                <input
                    v-if="editing === subtask.id"
                    v-focus
                    class="control h-8 flex-1"
                    :aria-label="`Renommer « ${subtask.title} »`"
                    :value="subtask.title"
                    maxlength="200"
                    @keydown.enter.prevent="
                        rename(
                            subtask,
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                    @keydown.esc="editing = null"
                    @blur="
                        rename(
                            subtask,
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
                <label
                    v-else
                    :for="`subtask-${subtask.id}`"
                    :class="[
                        'flex-1',
                        (optimistic[subtask.id] ?? subtask.done) &&
                            'text-muted-foreground line-through',
                    ]"
                    @dblclick="canManage && (editing = subtask.id)"
                    >{{ subtask.title }}</label
                >
                <template v-if="canManage && editing !== subtask.id">
                    <Button
                        size="sm"
                        variant="ghost"
                        class="h-7 px-2 text-xs"
                        @click="editing = subtask.id"
                        >Renommer</Button
                    >
                    <Button
                        size="icon-sm"
                        variant="ghost"
                        class="size-7"
                        :aria-label="`Supprimer « ${subtask.title} »`"
                        @click="destroy(subtask)"
                        ><Trash2
                    /></Button>
                </template>
            </li>
        </ul>

        <Form
            v-if="canManage"
            v-bind="SubtaskController.store.form(taskId)"
            :options="reloadOptions"
            reset-on-success
            class="grid gap-1"
            v-slot="{ errors }"
        >
            <Input
                name="title"
                placeholder="Ajouter une sous-tâche (Entrée pour valider)"
                aria-label="Ajouter une sous-tâche"
                maxlength="200"
                required
            />
            <InputError :message="errors.title" />
        </Form>
    </section>
</template>
