<script setup lang="ts">
import { computed } from 'vue';
import { useInitials } from '@/composables/useInitials';
import { cn } from '@/lib/utils';
import { memberColor } from '@/lib/tones';

const props = withDefaults(
    defineProps<{
        id: number;
        name: string;
        size?: 'xs' | 'md' | 'lg';
        inactive?: boolean;
        class?: string;
    }>(),
    {
        size: 'md',
        inactive: false,
        class: undefined,
    },
);

const { getInitials } = useInitials();

const sizeClass = computed(
    () =>
        ({
            xs: 'size-[22px] text-[9.5px]',
            md: 'size-7 text-[11px]',
            lg: 'size-11 text-[15px]',
        })[props.size],
);
</script>

<template>
    <!-- Round avatar of the prototype: initials on the member's colour. -->
    <span
        :class="
            cn(
                'inline-grid shrink-0 place-items-center rounded-full border-2 border-card font-bold tracking-[0.02em] text-white',
                sizeClass,
                inactive && 'opacity-55 grayscale',
                props.class,
            )
        "
        :style="{ backgroundColor: memberColor(id) }"
        :title="name"
        aria-hidden="true"
        >{{ getInitials(name) }}</span
    >
</template>
