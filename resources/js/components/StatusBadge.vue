<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { statusTone } from '@/lib/tones';
import type { Status } from '@/types';

defineProps<{
    status: Pick<Status, 'name' | 'tone'>;
}>();
</script>

<template>
    <Badge :variant="statusTone(status.tone)">{{ status.name }}</Badge>
</template>
