<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <!-- default = page title of the prototype; small = title of a card or section. -->
    <header :class="variant === 'small' ? '' : 'space-y-1.5'">
        <h2
            :class="
                variant === 'small'
                    ? 'mb-0.5 text-[17px] font-semibold'
                    : 'text-[30px] leading-[1.15] font-semibold'
            "
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            :class="[
                'text-muted-foreground',
                variant === 'small' ? 'text-[13px]' : 'max-w-[62ch]',
            ]"
        >
            {{ description }}
        </p>
    </header>
</template>
