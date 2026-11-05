<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import TaskStatusController from '@/actions/App/Modules/Tasks/Http/Controllers/TaskStatusController';
import BlockReasonDialog from '@/components/tasks/BlockReasonDialog.vue';
import { Button } from '@/components/ui/button';
import type { Status } from '@/types';

/**
 * One button per configured status; a "blocked" status asks for the reason first.
 */
const props = defineProps<{
    taskId: number;
    current: Status;
    statuses: Status[];
}>();

const asking = ref<Status | null>(null);

function choose(status: Status) {
    if (status.category === 'blocked') {
        asking.value = status;

        return;
    }

    send(status);
}

function send(status: Status, blockedReason?: string) {
    router.patch(
        TaskStatusController.update.url(props.taskId),
        { status_id: status.id, blocked_reason: blockedReason ?? null },
        { preserveScroll: true, onFinish: () => (asking.value = null) },
    );
}
</script>

<template>
    <div class="flex flex-wrap gap-1.5" role="group" aria-label="Changer le statut">
        <Button
            v-for="status in statuses"
            :key="status.id"
            size="sm"
            :variant="status.id === current.id ? 'default' : 'outline'"
            :aria-pressed="status.id === current.id"
            :disabled="status.id === current.id"
            @click="choose(status)"
            >{{ status.name }}</Button
        >
    </div>

    <BlockReasonDialog
        :open="asking !== null"
        :status-name="asking?.name"
        @confirm="(reason) => asking && send(asking, reason)"
        @cancel="asking = null"
    />
</template>
