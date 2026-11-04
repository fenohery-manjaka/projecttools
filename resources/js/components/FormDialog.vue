<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';
import {
    Dialog,
    DialogDescription,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';

/**
 * Creation dialog of the application (docs/conventions.md, « Créer dans une modale »):
 * the page stays behind, and the options of the form (lists of projects, members…)
 * are loaded only when the dialog opens, through an optional Inertia prop.
 */
const props = defineProps<{
    title: string;
    description?: string;
    /** Name of the optional prop holding the form options. */
    optionsProp: string;
    /** True once that prop has been received. */
    ready: boolean;
}>();

const open = defineModel<boolean>('open', { default: false });

watch(open, (isOpen) => {
    if (isOpen && !props.ready) {
        router.reload({ only: [props.optionsProp] });
    }
});
</script>

<template>
    <Dialog v-model:open="open">
        <DialogScrollContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">{{
                    description
                }}</DialogDescription>
            </DialogHeader>

            <slot v-if="ready" />
            <p
                v-else
                class="flex items-center gap-2 py-6 text-muted-foreground"
                role="status"
            >
                <Spinner />Chargement du formulaire…
            </p>
        </DialogScrollContent>
    </Dialog>
</template>
