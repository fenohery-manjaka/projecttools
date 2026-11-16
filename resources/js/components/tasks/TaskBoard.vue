<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import TaskMoveController from '@/actions/App/Modules/Tasks/Http/Controllers/TaskMoveController';
import StatusBadge from '@/components/StatusBadge.vue';
import BlockReasonDialog from '@/components/tasks/BlockReasonDialog.vue';
import TaskCard from '@/components/tasks/TaskCard.vue';
import type { Status, TaskSummary } from '@/types';

/**
 * Kanban board: one column per configured task status, in the order of the settings.
 * Native HTML drag and drop, plus a "Move to…" menu on each card for the keyboard.
 */
const props = defineProps<{
    tasks: TaskSummary[];
    statuses: Status[];
    canMove: boolean;
}>();

const columns = computed(() =>
    props.statuses.map((status) => ({
        status,
        tasks: props.tasks.filter((task) => task.status.id === status.id),
    })),
);

const dragged = ref<TaskSummary | null>(null);
const over = ref<{ statusId: number; index: number } | null>(null);

/** Move waiting for the reason of the block. */
const pending = ref<{
    task: TaskSummary;
    status: Status;
    position: number;
} | null>(null);

function dropIndex(event: DragEvent, list: HTMLElement): number {
    const cards = [...list.querySelectorAll<HTMLElement>('[data-card]')].filter(
        (card) => card.dataset.card !== String(dragged.value?.id),
    );
    const index = cards.findIndex((card) => {
        const box = card.getBoundingClientRect();

        return event.clientY < box.top + box.height / 2;
    });

    return index === -1 ? cards.length : index;
}

function onDragStart(event: DragEvent, task: TaskSummary) {
    dragged.value = task;
    event.dataTransfer?.setData('text/plain', String(task.id));

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
}

function onDragOver(event: DragEvent, status: Status) {
    if (!dragged.value) {
        return;
    }

    event.preventDefault();
    over.value = {
        statusId: status.id,
        index: dropIndex(event, event.currentTarget as HTMLElement),
    };
}

function onDrop(event: DragEvent, status: Status) {
    const task = dragged.value;

    if (!task) {
        return;
    }

    event.preventDefault();
    const position = dropIndex(event, event.currentTarget as HTMLElement);
    endDrag();
    move(task, status, position);
}

function endDrag() {
    dragged.value = null;
    over.value = null;
}

function move(task: TaskSummary, status: Status, position: number) {
    // Entering a "blocked" column asks why; cancelling leaves the card where it was.
    if (status.category === 'blocked' && task.status.id !== status.id) {
        pending.value = { task, status, position };

        return;
    }

    send(task, status, position);
}

function moveToEnd(task: TaskSummary, status: Status) {
    move(
        task,
        status,
        props.tasks.filter((t) => t.status.id === status.id).length,
    );
}

function send(
    task: TaskSummary,
    status: Status,
    position: number,
    blockedReason?: string,
) {
    router.patch(
        TaskMoveController.update.url(task.id),
        {
            status_id: status.id,
            position,
            blocked_reason: blockedReason ?? null,
        },
        { preserveScroll: true, onFinish: () => (pending.value = null) },
    );
}
</script>

<template>
    <!-- On a phone the columns scroll sideways inside the board, the page does not. -->
    <div class="-mx-1 overflow-x-auto px-1 pb-2">
        <div
            class="grid auto-cols-[minmax(250px,1fr)] grid-flow-col gap-3"
            :style="{ minWidth: `${statuses.length * 262}px` }"
        >
            <section
                v-for="column in columns"
                :key="column.status.id"
                class="flex min-h-48 flex-col gap-2 rounded-2xl bg-sunk/70 p-2.5"
                :aria-label="`Colonne ${column.status.name}, ${column.tasks.length} tâche${column.tasks.length > 1 ? 's' : ''}`"
            >
                <header class="flex items-center justify-between px-1 py-0.5">
                    <StatusBadge :status="column.status" />
                    <span class="text-xs text-muted-foreground">{{
                        column.tasks.length
                    }}</span>
                </header>

                <div
                    class="flex flex-1 flex-col gap-2"
                    :class="
                        over?.statusId === column.status.id &&
                        'rounded-xl outline-2 outline-offset-2 outline-line-2 outline-dashed'
                    "
                    @dragover="onDragOver($event, column.status)"
                    @dragleave.self="over = null"
                    @drop="onDrop($event, column.status)"
                >
                    <TaskCard
                        v-for="task in column.tasks"
                        :key="task.id"
                        :data-card="task.id"
                        :task="task"
                        :statuses="statuses"
                        :can-move="canMove"
                        :class="dragged?.id === task.id && 'opacity-40'"
                        @dragstart="onDragStart($event, task)"
                        @dragend="endDrag"
                        @move-to="(status) => moveToEnd(task, status)"
                    />
                    <p
                        v-if="column.tasks.length === 0"
                        class="px-1 py-4 text-center text-xs text-muted-foreground"
                    >
                        Aucune tâche
                    </p>
                </div>
            </section>
        </div>
    </div>

    <BlockReasonDialog
        :open="pending !== null"
        :status-name="pending?.status.name"
        @confirm="
            (reason) =>
                pending &&
                send(pending.task, pending.status, pending.position, reason)
        "
        @cancel="pending = null"
    />
</template>
