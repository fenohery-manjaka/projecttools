<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import MemberAvatar from '@/components/MemberAvatar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { projectStatusTone } from '@/lib/tones';
import { create, index, show } from '@/routes/projects';

type ProjectSummary = {
    id: number;
    name: string;
    description: string | null;
    status: { value: string; label: string };
    color: string;
    owner: { id: number; name: string };
    ends_on: string | null;
    members_count: number;
};

defineProps<{
    projects: ProjectSummary[];
    can: { create: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projets', href: index() }],
    },
});

const formatDate = (date: string) =>
    new Date(`${date}T00:00`).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
</script>

<template>
    <Head title="Projets" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <Heading
                title="Projets"
                description="Les projets dont vous êtes membre, ou tous selon vos droits."
            />
            <Button v-if="can.create" as-child>
                <Link :href="create()"><Plus />Nouveau projet</Link>
            </Button>
        </div>

        <p v-if="projects.length === 0" class="empty-state">
            Aucun projet pour l’instant.
        </p>

        <div
            v-else
            class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4"
        >
            <!-- Project card of the prototype -->
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="show(project.id)"
                class="group flex flex-col gap-3.5 rounded-2xl border border-border bg-card p-[18px] text-left transition-[border-color,box-shadow] hover:border-line-2 hover:shadow-soft"
            >
                <div class="flex items-start justify-between gap-3">
                    <span
                        class="grid size-[38px] shrink-0 place-items-center rounded-[11px] font-display text-base font-bold text-white"
                        :style="{ backgroundColor: project.color }"
                        aria-hidden="true"
                        >{{ project.name[0] }}</span
                    >
                    <Badge :variant="projectStatusTone(project.status.value)">{{
                        project.status.label
                    }}</Badge>
                </div>
                <div class="space-y-1">
                    <h3 class="text-[17px]">{{ project.name }}</h3>
                    <p
                        class="line-clamp-2 min-h-10 text-[13.5px] text-muted-foreground"
                    >
                        {{ project.description }}
                    </p>
                </div>
                <div
                    class="mt-auto flex items-center justify-between gap-2 text-[12.5px] text-muted-foreground"
                >
                    <span class="flex min-w-0 items-center gap-2">
                        <MemberAvatar
                            :id="project.owner.id"
                            :name="project.owner.name"
                            size="xs"
                        />
                        <span class="truncate"
                            >{{ project.owner.name }} ·
                            {{ project.members_count }} membre{{
                                project.members_count > 1 ? 's' : ''
                            }}</span
                        >
                    </span>
                    <span v-if="project.ends_on" class="shrink-0"
                        >Fin {{ formatDate(project.ends_on) }}</span
                    >
                </div>
            </Link>
        </div>
    </div>
</template>
