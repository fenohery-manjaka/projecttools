<script setup lang="ts">
import { useDebounceFn } from '@vueuse/core';
import { Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';

export type TaskFilterValues = {
    project: number | null;
    member: number | null;
    priority: string | null;
    due: string | null;
    q: string | null;
};

export type TaskFilterOptions = {
    projects: { id: number; name: string }[];
    members: { id: number; name: string }[];
    priorities: { value: string; label: string }[];
    due: { value: string; label: string }[];
};

const props = defineProps<{
    filters: TaskFilterValues;
    options: TaskFilterOptions;
    filtered: boolean;
}>();

const emit = defineEmits<{
    change: [filters: TaskFilterValues];
}>();

const search = ref(props.filters.q ?? '');

watch(
    () => props.filters.q,
    (q) => (search.value = q ?? ''),
);

function set<K extends keyof TaskFilterValues>(
    key: K,
    value: TaskFilterValues[K],
) {
    emit('change', { ...props.filters, [key]: value });
}

const searchLater = useDebounceFn(
    (q: string) => set('q', q.trim() === '' ? null : q),
    350,
);

function number(value: string): number | null {
    return value === '' ? null : Number(value);
}

function reset() {
    search.value = '';
    emit('change', {
        project: null,
        member: null,
        priority: null,
        due: null,
        q: null,
    });
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2" role="search">
        <label class="relative min-w-52 flex-1">
            <span class="sr-only">Rechercher dans les tâches</span>
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <input
                v-model="search"
                type="search"
                class="control w-full pl-9"
                placeholder="Rechercher…"
                @input="searchLater(search)"
            />
        </label>

        <select
            class="control w-auto"
            aria-label="Projet"
            :value="filters.project ?? ''"
            @change="
                set(
                    'project',
                    number(($event.target as HTMLSelectElement).value),
                )
            "
        >
            <option value="">Tous les projets</option>
            <option
                v-for="project in options.projects"
                :key="project.id"
                :value="project.id"
            >
                {{ project.name }}
            </option>
        </select>

        <select
            class="control w-auto"
            aria-label="Membre"
            :value="filters.member ?? ''"
            @change="
                set(
                    'member',
                    number(($event.target as HTMLSelectElement).value),
                )
            "
        >
            <option value="">Tout le monde</option>
            <option
                v-for="member in options.members"
                :key="member.id"
                :value="member.id"
            >
                {{ member.name }}
            </option>
        </select>

        <select
            class="control w-auto"
            aria-label="Priorité"
            :value="filters.priority ?? ''"
            @change="
                set(
                    'priority',
                    ($event.target as HTMLSelectElement).value || null,
                )
            "
        >
            <option value="">Toutes priorités</option>
            <option
                v-for="priority in options.priorities"
                :key="priority.value"
                :value="priority.value"
            >
                {{ priority.label }}
            </option>
        </select>

        <select
            class="control w-auto"
            aria-label="Échéance"
            :value="filters.due ?? ''"
            @change="
                set('due', ($event.target as HTMLSelectElement).value || null)
            "
        >
            <option value="">Toutes échéances</option>
            <option
                v-for="due in options.due"
                :key="due.value"
                :value="due.value"
            >
                {{ due.label }}
            </option>
        </select>

        <Button v-if="filtered" variant="ghost" size="sm" @click="reset"
            >Réinitialiser</Button
        >
    </div>
</template>
