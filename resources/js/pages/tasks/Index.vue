<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Columns3, List, Plus } from '@lucide/vue';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import CreateTaskDialog from '@/components/tasks/CreateTaskDialog.vue';
import TaskBoard from '@/components/tasks/TaskBoard.vue';
import TaskFiltersBar from '@/components/tasks/TaskFiltersBar.vue';
import type {
    TaskFilterOptions,
    TaskFilterValues,
} from '@/components/tasks/TaskFiltersBar.vue';
import TaskTable from '@/components/tasks/TaskTable.vue';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/tasks';
import type { Status, TaskFormOptions, TaskSummary } from '@/types';

const props = defineProps<{
    view: 'list' | 'board';
    list?: {
        data: TaskSummary[];
        current_page: number;
        last_page: number;
        prev_url: string | null;
        next_url: string | null;
    };
    board?: TaskSummary[];
    total: number;
    filters: TaskFilterValues;
    filtered: boolean;
    options: TaskFilterOptions;
    statuses: Status[];
    can: { create: boolean; update: boolean };
    taskForm?: TaskFormOptions;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Tâches', href: index() }],
    },
});

const page = usePage();
const creating = ref(false);

const summary = computed(() => {
    const count = `${props.total} tâche${props.total > 1 ? 's' : ''}`;

    if (props.filtered) {
        return `${count} correspond${props.total > 1 ? 'ent' : ''} aux filtres.`;
    }

    return `${count} dans les projets actifs.`;
});

/** Filters and view live in the address: a filtered view can be shared or reloaded. */
function visit(filters: TaskFilterValues, view = props.view) {
    const query = Object.fromEntries(
        Object.entries({ ...filters, view: view === 'board' ? 'board' : null })
            .filter(([, value]) => value !== null && value !== '')
            .map(([key, value]) => [key, String(value)]),
    );

    router.get(
        index.url({ query }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}
</script>

<template>
    <Head title="Tâches" />

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <Heading title="Tâches" :description="summary" />
            <div class="flex flex-wrap items-center gap-2">
                <div
                    class="inline-flex rounded-[10px] border border-border bg-card p-0.5"
                    role="group"
                    aria-label="Affichage"
                >
                    <Button
                        size="sm"
                        :variant="view === 'board' ? 'default' : 'ghost'"
                        :aria-pressed="view === 'board'"
                        @click="visit(filters, 'board')"
                        ><Columns3 />Tableau</Button
                    >
                    <Button
                        size="sm"
                        :variant="view === 'list' ? 'default' : 'ghost'"
                        :aria-pressed="view === 'list'"
                        @click="visit(filters, 'list')"
                        ><List />Liste</Button
                    >
                </div>
                <Button v-if="can.create" @click="creating = true"
                    ><Plus />Nouvelle tâche</Button
                >
            </div>
        </div>

        <CreateTaskDialog
            v-if="can.create"
            v-model:open="creating"
            :options="taskForm"
        />

        <TaskFiltersBar
            :filters="filters"
            :options="options"
            :filtered="filtered"
            @change="(next) => visit(next)"
        />

        <InputError
            v-for="(message, key) in page.props.errors"
            :key="key"
            :message="message"
        />

        <TaskBoard
            v-if="view === 'board' && board"
            :tasks="board"
            :statuses="statuses"
            :can-move="can.update"
        />

        <template v-else-if="list">
            <p v-if="list.data.length === 0" class="empty-state">
                {{
                    filtered
                        ? 'Aucune tâche ne correspond à ces filtres.'
                        : 'Aucune tâche pour l’instant.'
                }}
            </p>
            <TaskTable v-else :tasks="list.data" />

            <nav
                v-if="list.last_page > 1"
                class="flex items-center justify-between gap-2 text-sm"
                aria-label="Pages"
            >
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!list.prev_url"
                    as-child
                >
                    <Link
                        :href="list.prev_url ?? ''"
                        preserve-scroll
                        :class="
                            !list.prev_url && 'pointer-events-none opacity-50'
                        "
                        >Précédente</Link
                    >
                </Button>
                <span class="text-muted-foreground"
                    >Page {{ list.current_page }} sur {{ list.last_page }}</span
                >
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!list.next_url"
                    as-child
                >
                    <Link
                        :href="list.next_url ?? ''"
                        :class="
                            !list.next_url && 'pointer-events-none opacity-50'
                        "
                        >Suivante</Link
                    >
                </Button>
            </nav>
        </template>
    </div>
</template>
