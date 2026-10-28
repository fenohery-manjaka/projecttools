<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const appName = usePage().props.name;
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="home()" class="flex items-center gap-2.5">
                        <div
                            class="grid size-[34px] place-items-center rounded-[10px] bg-primary text-primary-foreground"
                        >
                            <AppLogoIcon class="size-5" />
                        </div>
                        <span
                            class="font-display text-[19px] font-bold tracking-[-0.02em]"
                            >{{ appName }}</span
                        >
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-2xl">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
