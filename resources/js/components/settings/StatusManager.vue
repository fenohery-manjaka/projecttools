<script setup lang="ts">
import { Form, router, usePage } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Status } from '@/types';

type Option = { value: string; label: string };

/**
 * Settings screen of a configurable status list (project statuses, task statuses…).
 * The server side is StatusSettingsController.
 */
const props = defineProps<{
    statuses: (Status & { usage_count: number })[];
    categories: (Option & { description: string })[];
    tones: Option[];
    /** "projet", "tâche": used in the sentences. */
    noun: string;
    actions: {
        store: string;
        update: (id: number) => string;
        move: (id: number) => string;
        destroy: (id: number) => string;
    };
}>();

const page = usePage();

/** Status being deleted while it is still used: its replacement is asked first. */
const deleting = ref<number | null>(null);
const replacement = ref<number | null>(null);

function categoryLabel(value: string): string {
    return props.categories.find((c) => c.value === value)?.label ?? value;
}

function move(status: Status, direction: 'up' | 'down') {
    router.patch(
        props.actions.move(status.id),
        { direction },
        { preserveScroll: true },
    );
}

function destroy(status: Status & { usage_count: number }) {
    if (status.usage_count > 0 && deleting.value !== status.id) {
        deleting.value = status.id;
        replacement.value =
            props.statuses.find((other) => other.id !== status.id)?.id ?? null;

        return;
    }

    if (!window.confirm(`Supprimer le statut « ${status.name} » ?`)) {
        return;
    }

    router.delete(props.actions.destroy(status.id), {
        data: { replacement_id: replacement.value },
        preserveScroll: true,
        onSuccess: () => (deleting.value = null),
    });
}
</script>

<template>
    <div class="space-y-6">
        <InputError :message="page.props.errors.status" />
        <InputError :message="page.props.errors.replacement_id" />

        <ul class="panel divide-y divide-border">
            <li
                v-for="(status, i) in statuses"
                :key="status.id"
                class="space-y-3 p-4"
                :data-test="`status-${status.id}`"
            >
                <Form
                    :action="actions.update(status.id)"
                    method="patch"
                    :options="{ preserveScroll: true }"
                    class="grid items-end gap-3 sm:grid-cols-[1fr_9rem_11rem_auto]"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-1.5">
                        <Label :for="`name-${status.id}`" class="sr-only"
                            >Nom</Label
                        >
                        <Input
                            :id="`name-${status.id}`"
                            name="name"
                            :default-value="status.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`tone-${status.id}`" class="sr-only"
                            >Couleur</Label
                        >
                        <select
                            :id="`tone-${status.id}`"
                            name="tone"
                            class="control"
                        >
                            <option
                                v-for="tone in tones"
                                :key="tone.value"
                                :value="tone.value"
                                :selected="tone.value === status.tone"
                            >
                                {{ tone.label }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`category-${status.id}`" class="sr-only"
                            >Comportement</Label
                        >
                        <select
                            :id="`category-${status.id}`"
                            name="category"
                            class="control"
                        >
                            <option
                                v-for="category in categories"
                                :key="category.value"
                                :value="category.value"
                                :selected="category.value === status.category"
                            >
                                {{ category.label }}
                            </option>
                        </select>
                        <InputError :message="errors.category" />
                    </div>
                    <div class="flex gap-1">
                        <Button
                            type="submit"
                            size="sm"
                            variant="outline"
                            :disabled="processing"
                            >Enregistrer</Button
                        >
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="ghost"
                            :disabled="i === 0"
                            :aria-label="`Monter « ${status.name} »`"
                            @click="move(status, 'up')"
                            ><ArrowUp
                        /></Button>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="ghost"
                            :disabled="i === statuses.length - 1"
                            :aria-label="`Descendre « ${status.name} »`"
                            @click="move(status, 'down')"
                            ><ArrowDown
                        /></Button>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="ghost"
                            :aria-label="`Supprimer « ${status.name} »`"
                            @click="destroy(status)"
                            ><Trash2
                        /></Button>
                    </div>
                </Form>

                <p
                    class="flex flex-wrap items-center gap-2 text-[13px] text-muted-foreground"
                >
                    <StatusBadge :status="status" />
                    {{ categoryLabel(status.category) }} ·
                    {{
                        status.usage_count === 0
                            ? `aucun ${noun}`
                            : `${status.usage_count} ${noun}${status.usage_count > 1 ? 's' : ''}`
                    }}
                </p>

                <div
                    v-if="deleting === status.id"
                    class="flex flex-wrap items-center gap-2 rounded-lg bg-sunk p-3 text-sm"
                >
                    <Label :for="`replacement-${status.id}`"
                        >Ce statut est utilisé. Le remplacer par :</Label
                    >
                    <select
                        :id="`replacement-${status.id}`"
                        v-model="replacement"
                        class="control w-auto"
                    >
                        <option
                            v-for="other in statuses.filter(
                                (s) => s.id !== status.id,
                            )"
                            :key="other.id"
                            :value="other.id"
                        >
                            {{ other.name }}
                        </option>
                    </select>
                    <Button
                        size="sm"
                        variant="destructive"
                        @click="destroy(status)"
                        >Remplacer et supprimer</Button
                    >
                    <Button size="sm" variant="ghost" @click="deleting = null"
                        >Annuler</Button
                    >
                </div>
            </li>
        </ul>

        <Form
            :action="actions.store"
            method="post"
            :options="{ preserveScroll: true }"
            reset-on-success
            class="panel grid items-end gap-3 p-4 sm:grid-cols-[1fr_9rem_11rem_auto]"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-1.5">
                <Label for="new-name">Nouveau statut</Label>
                <Input id="new-name" name="name" required />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-1.5">
                <Label for="new-tone">Couleur</Label>
                <select id="new-tone" name="tone" class="control">
                    <option
                        v-for="tone in tones"
                        :key="tone.value"
                        :value="tone.value"
                    >
                        {{ tone.label }}
                    </option>
                </select>
            </div>
            <div class="grid gap-1.5">
                <Label for="new-category">Comportement</Label>
                <select id="new-category" name="category" class="control">
                    <option
                        v-for="category in categories"
                        :key="category.value"
                        :value="category.value"
                    >
                        {{ category.label }}
                    </option>
                </select>
            </div>
            <Button type="submit" :disabled="processing">Ajouter</Button>
        </Form>

        <div class="space-y-1.5">
            <h3 class="text-sm font-semibold">Les comportements</h3>
            <dl class="grid gap-1 text-[13px] text-muted-foreground">
                <div v-for="category in categories" :key="category.value">
                    <dt class="inline font-medium text-foreground">
                        {{ category.label }} :
                    </dt>
                    <dd class="inline">{{ category.description }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>
