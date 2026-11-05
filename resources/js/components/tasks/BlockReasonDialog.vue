<script setup lang="ts">
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

/**
 * Asks why a task is blocked before moving it to a "blocked" status.
 * Emits "confirm" with the reason, or "cancel" (closed without a reason).
 */
const props = defineProps<{
    open: boolean;
    statusName?: string;
}>();

const emit = defineEmits<{
    confirm: [reason: string];
    cancel: [];
}>();

const reason = ref('');

watch(
    () => props.open,
    (open) => {
        if (open) {
            reason.value = '';
        }
    },
);

function submit() {
    if (reason.value.trim() !== '') {
        emit('confirm', reason.value.trim());
    }
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="(value: boolean) => !value && emit('cancel')"
    >
        <DialogContent>
            <form class="grid gap-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle
                        >Passer à « {{ statusName ?? 'Bloqué' }} »</DialogTitle
                    >
                    <DialogDescription
                        >Qu’est-ce qui bloque ? La raison s’affiche sur la tâche
                        et sur l’accueil.</DialogDescription
                    >
                </DialogHeader>
                <div class="grid gap-2">
                    <Label for="block-reason">Raison du blocage</Label>
                    <textarea
                        id="block-reason"
                        v-model="reason"
                        rows="3"
                        class="control"
                        required
                    />
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('cancel')"
                        >Annuler</Button
                    >
                    <Button type="submit" :disabled="reason.trim() === ''"
                        >Confirmer</Button
                    >
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
