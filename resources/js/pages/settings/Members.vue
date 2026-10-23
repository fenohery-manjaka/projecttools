<script setup lang="ts">
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import MemberController from '@/actions/App/Modules/Core/Http/Controllers/Settings/MemberController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
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

const selectClass =
    'h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 outline-none';

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

        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="p-3 text-left font-medium">Membre</th>
                        <th class="p-3 text-left font-medium">Rôle</th>
                        <th class="p-3 text-left font-medium">Statut</th>
                        <th v-if="can.manage" class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="member in members"
                        :key="member.id"
                        class="border-t"
                        :class="{ 'opacity-60': !member.active }"
                    >
                        <td class="p-3">
                            <span class="font-medium">{{ member.name }}</span>
                            <span
                                v-if="member.is_self"
                                class="text-muted-foreground"
                            >
                                (vous)</span
                            >
                            <span class="block text-muted-foreground">{{
                                member.email
                            }}</span>
                        </td>
                        <td class="p-3">
                            <Badge v-if="member.is_super_admin"
                                >Super admin</Badge
                            >
                            <select
                                v-else-if="can.manage && editable(member)"
                                :class="selectClass"
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
                        <td class="p-3">
                            <Badge v-if="member.active" variant="outline"
                                >Actif</Badge
                            >
                            <Badge v-else variant="secondary">Désactivé</Badge>
                        </td>
                        <td v-if="can.manage" class="p-3 text-right">
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
            class="grid gap-4 sm:grid-cols-3"
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
                <select
                    id="invite-role"
                    name="role"
                    :class="selectClass"
                    required
                >
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
