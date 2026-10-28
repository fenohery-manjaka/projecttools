<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const name = usePage().props.name;
</script>

<template>
    <div
        class="grid size-[34px] shrink-0 place-items-center rounded-[10px] bg-primary text-primary-foreground"
    >
        <AppLogoIcon class="size-5" />
    </div>
    <div class="ml-0.5 grid flex-1 text-left">
        <span
            class="truncate font-display text-[19px] leading-[1.1] font-bold tracking-[-0.02em]"
            >{{ name }}</span
        >
        <span class="truncate text-xs text-muted-foreground"
            >Gestion de projets</span
        >
    </div>
</template>
