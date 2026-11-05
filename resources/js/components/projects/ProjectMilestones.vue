<script setup lang="ts">
import { Form, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import MilestoneController from '@/actions/App/Modules/Projects/Http/Controllers/MilestoneController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type ProjectMilestone = {
    id: number;
    name: string;
    due_on: string;
    reached: boolean;
    is_overdue: boolean;
    tasks_count?: number;
};

const props = defineProps<{
    projectId: number;
    milestones: ProjectMilestone[];
    canManage: boolean;
}>();

const editing = ref<number | null>(null);

const reachedCount = computed(
    () => props.milestones.filter((milestone) => milestone.reached).length,
);

const formatDate = (date: string) =>
    new Date(`${date}T00:00`).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

function params(milestone: ProjectMilestone) {
    return { project: props.projectId, milestone: milestone.id };
}

function setReached(milestone: ProjectMilestone, reached: boolean) {
    router.patch(
        MilestoneController.update.url(params(milestone)),
        { reached },
        { preserveScroll: true },
    );
}

function destroy(milestone: ProjectMilestone) {
    if (window.confirm(`Supprimer le jalon « ${milestone.name} » ?`)) {
        router.delete(MilestoneController.destroy.url(params(milestone)), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <section class="panel space-y-3.5 p-5">
        <div class="flex items-center justify-between gap-2">
            <Heading variant="small" title="Jalons" />
            <span class="text-[13px] text-muted-foreground"
                >{{ reachedCount }} / {{ milestones.length }} atteints</span
            >
        </div>

        <p v-if="milestones.length === 0" class="empty-state">
            Aucun jalon pour l’instant.
        </p>

        <ul v-else class="flex flex-col">
            <li
                v-for="milestone in milestones"
                :key="milestone.id"
                class="border-b border-border py-[11px] last:border-b-0"
            >
                <Form
                    v-if="editing === milestone.id"
                    v-bind="MilestoneController.update.form(params(milestone))"
                    :options="{ preserveScroll: true }"
                    class="flex flex-wrap items-start gap-2"
                    v-slot="{ errors, processing }"
                    @success="editing = null"
                >
                    <div class="grid min-w-48 flex-1 gap-1">
                        <Label
                            :for="`milestone-name-${milestone.id}`"
                            class="sr-only"
                            >Nom du jalon</Label
                        >
                        <Input
                            :id="`milestone-name-${milestone.id}`"
                            name="name"
                            :default-value="milestone.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-1">
                        <Label
                            :for="`milestone-date-${milestone.id}`"
                            class="sr-only"
                            >Date</Label
                        >
                        <Input
                            :id="`milestone-date-${milestone.id}`"
                            name="due_on"
                            type="date"
                            :default-value="milestone.due_on"
                            required
                        />
                        <InputError :message="errors.due_on" />
                    </div>
                    <Button
                        type="submit"
                        size="sm"
                        class="h-9"
                        :disabled="processing"
                        >Enregistrer</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        variant="ghost"
                        class="h-9"
                        @click="editing = null"
                        >Annuler</Button
                    >
                </Form>

                <div v-else class="flex items-center gap-3">
                    <input
                        :id="`milestone-${milestone.id}`"
                        type="checkbox"
                        class="size-4 accent-brand"
                        :checked="milestone.reached"
                        :disabled="!canManage"
                        @change="
                            setReached(
                                milestone,
                                ($event.target as HTMLInputElement).checked,
                            )
                        "
                    />
                    <label
                        :for="`milestone-${milestone.id}`"
                        class="min-w-0 flex-1"
                    >
                        <span
                            :class="[
                                'block truncate font-medium',
                                milestone.reached &&
                                    'text-muted-foreground line-through',
                            ]"
                            >{{ milestone.name }}</span
                        >
                        <span class="block text-xs text-muted-foreground"
                            >{{ formatDate(milestone.due_on)
                            }}<template
                                v-if="milestone.tasks_count !== undefined"
                            >
                                · {{ milestone.tasks_count }} tâche{{
                                    milestone.tasks_count > 1 ? 's' : ''
                                }}</template
                            ></span
                        >
                    </label>
                    <Badge v-if="milestone.reached" variant="ok">Atteint</Badge>
                    <Badge v-else-if="milestone.is_overdue" variant="bad"
                        >Dépassé</Badge
                    >
                    <template v-if="canManage">
                        <Button
                            size="icon-sm"
                            variant="ghost"
                            :aria-label="`Modifier le jalon « ${milestone.name} »`"
                            @click="editing = milestone.id"
                            ><Pencil
                        /></Button>
                        <Button
                            size="icon-sm"
                            variant="ghost"
                            :aria-label="`Supprimer le jalon « ${milestone.name} »`"
                            @click="destroy(milestone)"
                            ><Trash2
                        /></Button>
                    </template>
                </div>
            </li>
        </ul>

        <Form
            v-if="canManage"
            v-bind="MilestoneController.store.form(projectId)"
            :options="{ preserveScroll: true }"
            reset-on-success
            class="flex flex-wrap items-start gap-2 border-t border-border pt-3.5"
            v-slot="{ errors, processing }"
        >
            <div class="grid min-w-48 flex-1 gap-1">
                <Label for="new-milestone-name" class="sr-only"
                    >Nom du jalon</Label
                >
                <Input
                    id="new-milestone-name"
                    name="name"
                    placeholder="Nouveau jalon"
                    required
                />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-1">
                <Label for="new-milestone-date" class="sr-only">Date</Label>
                <Input
                    id="new-milestone-date"
                    name="due_on"
                    type="date"
                    required
                />
                <InputError :message="errors.due_on" />
            </div>
            <Button type="submit" size="sm" class="h-9" :disabled="processing"
                >Ajouter</Button
            >
        </Form>
    </section>
</template>
