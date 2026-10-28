<script setup lang="ts">
import { computed } from 'vue';
import MemberAvatar from '@/components/MemberAvatar.vue';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <Avatar v-if="showAvatar" class="h-8 w-8 overflow-hidden rounded-full">
        <AvatarImage :src="user.avatar!" :alt="user.name" />
    </Avatar>
    <MemberAvatar v-else :id="user.id" :name="user.name" class="size-8" />

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">{{
            user.email
        }}</span>
    </div>
</template>
