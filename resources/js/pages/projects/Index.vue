<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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

    <div class="space-y-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Projets"
                description="Les projets dont vous êtes membre, ou tous selon vos droits."
            />
            <Button v-if="can.create" as-child>
                <Link :href="create()"><Plus />Nouveau projet</Link>
            </Button>
        </div>

        <p
            v-if="projects.length === 0"
            class="rounded-lg border border-dashed p-8 text-center text-muted-foreground"
        >
            Aucun projet pour l’instant.
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="show(project.id)"
                class="group flex flex-col gap-3 rounded-xl border bg-card p-5 shadow-xs transition hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-3">
                    <span class="flex items-center gap-3">
                        <span
                            class="grid size-9 shrink-0 place-content-center rounded-lg font-semibold text-white"
                            :style="{ backgroundColor: project.color }"
                            aria-hidden="true"
                            >{{ project.name[0] }}</span
                        >
                        <span class="font-semibold group-hover:underline">{{
                            project.name
                        }}</span>
                    </span>
                    <Badge variant="outline">{{ project.status.label }}</Badge>
                </div>
                <p
                    v-if="project.description"
                    class="line-clamp-2 text-sm text-muted-foreground"
                >
                    {{ project.description }}
                </p>
                <p class="mt-auto text-sm text-muted-foreground">
                    Responsable : {{ project.owner.name }} ·
                    {{ project.members_count }} membre{{
                        project.members_count > 1 ? 's' : ''
                    }}
                    <template v-if="project.ends_on">
                        · fin {{ formatDate(project.ends_on) }}</template
                    >
                </p>
            </Link>
        </div>
    </div>
</template>
