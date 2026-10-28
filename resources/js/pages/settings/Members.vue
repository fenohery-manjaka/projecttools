<script setup lang="ts">
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import MemberController from '@/actions/App/Modules/Core/Http/Controllers/Settings/MemberController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import MemberAvatar from '@/components/MemberAvatar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/settings/members';

type Member = {
    id: number;
    name: string;
    email: string;
    role: string | null;
    is_super_admin: boolean;
    active: boolean;
    is_self: boolean;
};

defineProps<{
    members: Member[];
    roles: string[];
    can: { manage: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Membres', href: index() }],
    },
});

const page = usePage();

function update(
    member: Member,
    data: { role?: string | null; active?: boolean },
) {
    router.patch(MemberController.update.url(member.id), data, {
        preserveScroll: true,
    });
}

function editable(member: Member): boolean {
    return !member.is_super_admin && !member.is_self;
}
</script>

<template>
    <Head title="Membres" />

    <h1 class="sr-only">Membres</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Membres"
            description="Qui a accès à la plateforme, et avec quel rôle."
        />

        <InputError :message="page.props.errors.member" />

        <div class="panel overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Membre</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th v-if="can.manage"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="member in members"
                        :key="member.id"
                        :class="{ 'opacity-60': !member.active }"
                    >
                        <td>
                            <span class="flex items-center gap-3">
                                <MemberAvatar
                                    :id="member.id"
                                    :name="member.name"
                                    :inactive="!member.active"
                                />
                                <span class="min-w-0">
                                    <span class="font-medium">{{
                                        member.name
                                    }}</span>
                                    <span
                                        v-if="member.is_self"
                                        class="text-muted-foreground"
                                    >
                                        (vous)</span
                                    >
                                    <span
                                        class="block text-[13px] text-muted-foreground"
                                        >{{ member.email }}</span
                                    >
                                </span>
                            </span>
                        </td>
                        <td>
                            <Badge v-if="member.is_super_admin" variant="accent"
                                >Super admin</Badge
                            >
                            <select
                                v-else-if="can.manage && editable(member)"
                                class="control h-[30px] w-auto text-[12.5px]"
                                :value="member.role ?? ''"
                                :aria-label="`Rôle de ${member.name}`"
                                @change="
                                    update(member, {
                                        role:
                                            ($event.target as HTMLSelectElement)
                                                .value || null,
                                    })
                                "
                            >
                                <option value="">Aucun rôle</option>
                                <option
                                    v-for="role in roles"
                                    :key="role"
                                    :value="role"
                                >
                                    {{ role }}
                                </option>
                            </select>
                            <span v-else>{{
                                member.role ?? 'Aucun rôle'
                            }}</span>
                        </td>
                        <td>
                            <Badge v-if="member.active" variant="ok"
                                >Actif</Badge
                            >
                            <Badge v-else variant="secondary">Désactivé</Badge>
                        </td>
                        <td v-if="can.manage" class="text-right">
                            <Button
                                v-if="editable(member)"
                                variant="ghost"
                                size="sm"
                                @click="
                                    update(member, { active: !member.active })
                                "
                            >
                                {{ member.active ? 'Désactiver' : 'Réactiver' }}
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div v-if="can.manage" class="space-y-6">
        <Heading
            variant="small"
            title="Inviter un membre"
            description="Le compte est créé et la personne reçoit un e-mail pour choisir son mot de passe."
        />

        <Form
            v-bind="MemberController.store.form()"
            reset-on-success
            :options="{ preserveScroll: true }"
            class="panel grid gap-4 p-5 sm:grid-cols-3"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="invite-name">Nom</Label>
                <Input id="invite-name" name="name" required />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="invite-email">E-mail</Label>
                <Input id="invite-email" name="email" type="email" required />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="invite-role">Rôle</Label>
                <select id="invite-role" name="role" class="control" required>
                    <option value="" disabled selected>Choisir un rôle</option>
                    <option v-for="role in roles" :key="role" :value="role">
                        {{ role }}
                    </option>
                </select>
                <InputError :message="errors.role" />
            </div>
            <div class="sm:col-span-3">
                <Button type="submit" :disabled="processing"
                    >Envoyer l’invitation</Button
                >
            </div>
        </Form>
    </div>
</template>
