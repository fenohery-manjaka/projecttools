<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { TaskFormOptions, TaskFormValues } from '@/types';

const props = defineProps<
    Omit<TaskFormOptions, 'defaults'> & {
        values: TaskFormValues;
        errors: Record<string, string | undefined>;
    }
>();

// Project and status drive other fields: milestones and assignees come from the
// chosen project, and a "blocked" status asks for its reason.
const projectId = ref(props.values.project_id);
const statusId = ref(props.values.status_id);
const assigneeIds = ref<number[]>([...props.values.assignee_ids]);

const project = computed(() =>
    props.projects.find((p) => p.id === projectId.value),
);
const isBlocked = computed(
    () =>
        props.statuses.find((s) => s.id === statusId.value)?.category ===
        'blocked',
);

watch(projectId, () => {
    const members = new Set(project.value?.members.map((m) => m.id));
    assigneeIds.value = assigneeIds.value.filter((id) => members.has(id));
});
</script>

<template>
    <div class="grid gap-2">
        <Label for="title">Titre</Label>
        <Input
            id="title"
            name="title"
            :default-value="values.title"
            maxlength="200"
            required
        />
        <InputError :message="errors.title" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="project_id">Projet</Label>
            <select
                id="project_id"
                v-model="projectId"
                name="project_id"
                class="control"
            >
                <option
                    v-for="option in projects"
                    :key="option.id"
                    :value="option.id"
                >
                    {{ option.name }}
                </option>
            </select>
            <InputError :message="errors.project_id" />
        </div>
        <div class="grid gap-2">
            <Label for="milestone_id">Jalon</Label>
            <select
                id="milestone_id"
                :key="projectId ?? 0"
                name="milestone_id"
                class="control"
            >
                <option value="">Aucun</option>
                <option
                    v-for="milestone in project?.milestones ?? []"
                    :key="milestone.id"
                    :value="milestone.id"
                    :selected="milestone.id === values.milestone_id"
                >
                    {{ milestone.name }}
                </option>
            </select>
            <InputError :message="errors.milestone_id" />
        </div>
        <div class="grid gap-2">
            <Label for="status_id">Statut</Label>
            <select
                id="status_id"
                v-model="statusId"
                name="status_id"
                class="control"
            >
                <option
                    v-for="status in statuses"
                    :key="status.id"
                    :value="status.id"
                >
                    {{ status.name }}
                </option>
            </select>
            <InputError :message="errors.status_id" />
        </div>
        <div class="grid gap-2">
            <Label for="priority">Priorité</Label>
            <select id="priority" name="priority" class="control">
                <option
                    v-for="priority in priorities"
                    :key="priority.value"
                    :value="priority.value"
                    :selected="priority.value === values.priority"
                >
                    {{ priority.label }}
                </option>
            </select>
            <InputError :message="errors.priority" />
        </div>
        <div class="grid gap-2">
            <Label for="due_on">Échéance</Label>
            <Input
                id="due_on"
                name="due_on"
                type="date"
                :default-value="values.due_on ?? ''"
            />
            <InputError :message="errors.due_on" />
        </div>
        <div class="grid gap-2">
            <Label for="estimate_hours">Estimation (heures)</Label>
            <Input
                id="estimate_hours"
                name="estimate_hours"
                type="number"
                min="0"
                step="0.5"
                :default-value="values.estimate_hours ?? ''"
            />
            <InputError :message="errors.estimate_hours" />
        </div>
    </div>

    <fieldset class="grid gap-2">
        <legend class="mb-2 text-sm font-medium">Responsables</legend>
        <p
            v-if="!project || project.members.length === 0"
            class="text-sm text-muted-foreground"
        >
            Aucun membre dans ce projet.
        </p>
        <div v-else class="flex flex-wrap gap-x-4 gap-y-2">
            <label
                v-for="member in project.members"
                :key="member.id"
                class="flex items-center gap-2 text-sm"
            >
                <input
                    v-model="assigneeIds"
                    type="checkbox"
                    name="assignee_ids[]"
                    :value="member.id"
                    class="size-4 accent-brand"
                />
                {{ member.name }}
            </label>
        </div>
        <InputError :message="errors.assignee_ids" />
    </fieldset>

    <div v-if="isBlocked" class="grid gap-2">
        <Label for="blocked_reason">Raison du blocage</Label>
        <textarea
            id="blocked_reason"
            name="blocked_reason"
            rows="2"
            class="control"
            required
            :defaultValue="values.blocked_reason ?? ''"
        />
        <InputError :message="errors.blocked_reason" />
    </div>

    <div class="grid gap-2">
        <Label for="description">Description</Label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="control"
            :defaultValue="values.description ?? ''"
        />
        <InputError :message="errors.description" />
    </div>
</template>
