<script setup lang="ts">
import {
    Form,
    Head,
    Link,
    router,
    setLayoutProps,
    usePage,
} from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import ProjectController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectController';
import ProjectMemberController from '@/actions/App/Modules/Projects/Http/Controllers/ProjectMemberController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import MemberAvatar from '@/components/MemberAvatar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { projectStatusTone } from '@/lib/tones';
import { edit, index, show } from '@/routes/projects';

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

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Projets', href: index() },
            { title: props.project.name, href: show(props.project.id) },
        ],
    });
});

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

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-4">
                <span
                    class="grid size-11 shrink-0 place-items-center rounded-xl font-display text-lg font-bold text-white"
                    :style="{ backgroundColor: project.color }"
                    aria-hidden="true"
                    >{{ project.name[0] }}</span
                >
                <div class="space-y-1.5">
                    <h1 class="text-[30px] leading-[1.15]">
                        {{ project.name }}
                    </h1>
                    <p
                        class="flex flex-wrap items-center gap-2 text-muted-foreground"
                    >
                        <Badge
                            :variant="projectStatusTone(project.status.value)"
                            >{{ project.status.label }}</Badge
                        >
                        <span
                            >Responsable : {{ project.owner.name
                            }}<template
                                v-if="project.starts_on && project.ends_on"
                            >
                                · du {{ formatDate(project.starts_on) }} au
                                {{ formatDate(project.ends_on) }}</template
                            ></span
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

        <div class="grid items-start gap-4 lg:grid-cols-[2fr_1fr]">
            <section class="panel space-y-5 p-5">
                <div class="space-y-2">
                    <Heading variant="small" title="Description" />
                    <p
                        v-if="project.description"
                        class="whitespace-pre-line text-ink-2"
                    >
                        {{ project.description }}
                    </p>
                    <p v-else class="empty-state">Pas encore de description.</p>
                </div>
                <div v-if="project.objectives" class="space-y-2">
                    <Heading variant="small" title="Objectifs" />
                    <ul class="list-disc space-y-1 pl-5 text-ink-2">
                        <li
                            v-for="(objective, i) in project.objectives
                                .split('\n')
                                .filter(Boolean)"
                            :key="i"
                        >
                            {{ objective }}
                        </li>
                    </ul>
                </div>
            </section>

            <section class="panel space-y-3.5 p-5">
                <div class="flex items-center justify-between gap-2">
                    <Heading variant="small" title="Membres" />
                    <span class="text-[13px] text-muted-foreground">{{
                        project.members.length
                    }}</span>
                </div>
                <InputError :message="page.props.errors.member" />
                <ul class="flex flex-col">
                    <li
                        v-for="member in project.members"
                        :key="member.id"
                        class="flex items-center gap-3 border-b border-border py-[11px] last:border-b-0"
                    >
                        <MemberAvatar :id="member.id" :name="member.name" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium">{{
                                member.name
                            }}</span>
                            <span
                                class="block truncate text-xs text-muted-foreground"
                                >{{ member.email }}</span
                            >
                        </span>
                        <Badge v-if="member.is_owner" variant="accent"
                            >Responsable</Badge
                        >
                        <Button
                            v-else-if="can.manageMembers"
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
                    class="flex flex-wrap gap-2 border-t border-border pt-3.5"
                    v-slot="{ errors, processing }"
                >
                    <select
                        name="user_id"
                        aria-label="Membre à ajouter"
                        class="control flex-1"
                    >
                        <option
                            v-for="member in availableMembers"
                            :key="member.id"
                            :value="member.id"
                        >
                            {{ member.name }}
                        </option>
                    </select>
                    <Button
                        type="submit"
                        size="sm"
                        class="h-9"
                        :disabled="processing"
                        >Ajouter</Button
                    >
                    <InputError :message="errors.user_id" />
                </Form>
            </section>
        </div>
    </div>
</template>
