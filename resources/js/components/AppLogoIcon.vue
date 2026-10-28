<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!-- Brand mark of the prototype: two road-like curves. -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        :class="className"
        v-bind="$attrs"
    >
        <path d="M4 19c4-1 5-6 8-6s4 3 8 2" />
        <path d="M4 12c3 0 4-5 8-5s5 3 8 2" />
    </svg>
</template>
