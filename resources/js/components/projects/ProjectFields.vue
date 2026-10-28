<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type ProjectFormValues = {
    name?: string;
    description?: string | null;
    objectives?: string | null;
    status: string;
    owner_id: number;
    color: string;
    starts_on?: string | null;
    ends_on?: string | null;
};

defineProps<{
    values: ProjectFormValues;
    statuses: { value: string; label: string }[];
    colors: string[];
    owners: { id: number; name: string }[];
    errors: Record<string, string | undefined>;
}>();
</script>

<template>
    <div class="grid gap-2">
        <Label for="name">Nom</Label>
        <Input id="name" name="name" :default-value="values.name" required />
        <InputError :message="errors.name" />
    </div>

    <div class="grid gap-2">
        <Label for="description">Description</Label>
        <textarea
            id="description"
            name="description"
            rows="3"
            class="control"
            :defaultValue="values.description ?? ''"
        />
        <InputError :message="errors.description" />
    </div>

    <div class="grid gap-2">
        <Label for="objectives">Objectifs (un par ligne)</Label>
        <textarea
            id="objectives"
            name="objectives"
            rows="3"
            class="control"
            :defaultValue="values.objectives ?? ''"
        />
        <InputError :message="errors.objectives" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="status">Statut</Label>
            <select id="status" name="status" class="control">
                <option
                    v-for="status in statuses"
                    :key="status.value"
                    :value="status.value"
                    :selected="status.value === values.status"
                >
                    {{ status.label }}
                </option>
            </select>
            <InputError :message="errors.status" />
        </div>
        <div class="grid gap-2">
            <Label for="owner_id">Responsable</Label>
            <select id="owner_id" name="owner_id" class="control">
                <option
                    v-for="owner in owners"
                    :key="owner.id"
                    :value="owner.id"
                    :selected="owner.id === values.owner_id"
                >
                    {{ owner.name }}
                </option>
            </select>
            <InputError :message="errors.owner_id" />
        </div>
        <div class="grid gap-2">
            <Label for="starts_on">Début</Label>
            <Input
                id="starts_on"
                name="starts_on"
                type="date"
                :default-value="values.starts_on ?? ''"
            />
            <InputError :message="errors.starts_on" />
        </div>
        <div class="grid gap-2">
            <Label for="ends_on">Fin</Label>
            <Input
                id="ends_on"
                name="ends_on"
                type="date"
                :default-value="values.ends_on ?? ''"
            />
            <InputError :message="errors.ends_on" />
        </div>
    </div>

    <fieldset class="grid gap-2">
        <legend class="mb-2 text-sm font-medium">Couleur</legend>
        <div class="flex flex-wrap gap-2">
            <label
                v-for="color in colors"
                :key="color"
                class="cursor-pointer"
                :title="color"
            >
                <input
                    type="radio"
                    name="color"
                    :value="color"
                    :checked="color === values.color"
                    class="peer sr-only"
                />
                <span
                    class="block size-8 rounded-full ring-offset-2 ring-offset-background peer-checked:ring-2 peer-checked:ring-foreground peer-focus-visible:ring-2 peer-focus-visible:ring-ring"
                    :style="{ backgroundColor: color }"
                />
                <span class="sr-only">{{ color }}</span>
            </label>
        </div>
        <InputError :message="errors.color" />
    </fieldset>
</template>
