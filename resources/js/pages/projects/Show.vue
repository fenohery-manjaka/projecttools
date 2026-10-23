<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import ProjectController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectController';
import ProjectMemberController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectMemberController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/projects';

type Project = {
    id: number;
    name: string;
    description: string | null;
    objectives: string | null;
    status: { value: string; label: string };
    color: string;
    owner: { id: number; name: string };
    starts_on: string | null;
    ends_on: string | null;
    members: { id: number; name: string; email: string; is_owner: boolean }[];
};

const props = defineProps<{
    project: Project;
    availableMembers: { id: number; name: string }[];
    can: { update: boolean; delete: boolean; manageMembers: boolean };
}>();

const page = usePage();

const formatDate = (date: string) =>
    new Date(`${date}T00:00`).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

function destroy() {
    if (window.confirm(`Supprimer le projet « ${props.project.name} » ?`)) {
        router.delete(ProjectController.destroy.url(props.project.id));
    }
}

function removeMember(memberId: number) {
    router.delete(
        ProjectMemberController.destroy.url({
            project: props.project.id,
            member: memberId,
        }),
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="project.name" />

    <div class="space-y-8 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <span
                    class="grid size-12 place-content-center rounded-xl text-lg font-semibold text-white"
                    :style="{ backgroundColor: project.color }"
                    aria-hidden="true"
                    >{{ project.name[0] }}</span
                >
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ project.name }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        <Badge variant="outline" class="mr-2">{{
                            project.status.label
                        }}</Badge>
                        Responsable : {{ project.owner.name }}
                        <template v-if="project.starts_on && project.ends_on">
                            · du {{ formatDate(project.starts_on) }} au
                            {{ formatDate(project.ends_on) }}</template
                        >
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button v-if="can.update" variant="outline" as-child>
                    <Link :href="edit(project.id)">Modifier</Link>
                </Button>
                <Button v-if="can.delete" variant="destructive" @click="destroy"
                    >Supprimer</Button
                >
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-6">
                <section v-if="project.description" class="space-y-2">
                    <Heading variant="small" title="Description" />
                    <p class="whitespace-pre-line">{{ project.description }}</p>
                </section>
                <section v-if="project.objectives" class="space-y-2">
                    <Heading variant="small" title="Objectifs" />
                    <ul class="list-disc space-y-1 pl-5">
                        <li
                            v-for="(objective, i) in project.objectives
                                .split('\n')
                                .filter(Boolean)"
                            :key="i"
                        >
                            {{ objective }}
                        </li>
                    </ul>
                </section>
            </div>

            <section class="space-y-4">
                <Heading
                    variant="small"
                    :title="`Membres (${project.members.length})`"
                />
                <InputError :message="page.props.errors.member" />
                <ul class="divide-y rounded-lg border">
                    <li
                        v-for="member in project.members"
                        :key="member.id"
                        class="flex items-center justify-between gap-2 p-3 text-sm"
                    >
                        <span>
                            <span class="font-medium">{{ member.name }}</span>
                            <Badge
                                v-if="member.is_owner"
                                variant="secondary"
                                class="ml-2"
                                >Responsable</Badge
                            >
                        </span>
                        <Button
                            v-if="can.manageMembers && !member.is_owner"
                            variant="ghost"
                            size="sm"
                            @click="removeMember(member.id)"
                            >Retirer</Button
                        >
                    </li>
                </ul>

                <Form
                    v-if="can.manageMembers && availableMembers.length"
                    v-bind="ProjectMemberController.store.form(project.id)"
                    :options="{ preserveScroll: true }"
                    class="flex gap-2"
                    v-slot="{ errors, processing }"
                >
                    <select
                        name="user_id"
                        aria-label="Membre à ajouter"
                        class="h-9 flex-1 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
                    >
                        <option
                            v-for="member in availableMembers"
                            :key="member.id"
                            :value="member.id"
                        >
                            {{ member.name }}
                        </option>
                    </select>
                    <Button type="submit" size="sm" :disabled="processing"
                        >Ajouter</Button
                    >
                    <InputError :message="errors.user_id" />
                </Form>
            </section>
        </div>
    </div>
</template>
