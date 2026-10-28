<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import RoleController from '@/actions/App/Modules/Core/Http/Controllers/Settings/RoleController';
import RolePermissionController from '@/actions/App/Modules/Core/Http/Controllers/Settings/RolePermissionController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { index } from '@/routes/settings/roles';

type Role = {
    id: number;
    name: string;
    users_count: number;
    permissions: string[];
};

type PermissionGroup = {
    key: string;
    name: string;
    enabled: boolean;
    permissions: { name: string; label: string }[];
};

defineProps<{
    roles: Role[];
    groups: PermissionGroup[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Rôles et permissions', href: index() }],
    },
});

const confirm = (message: string) => window.confirm(message);

function toggle(role: Role, permission: string, granted: boolean) {
    router.put(
        RolePermissionController.update.url(role.id),
        { permission, granted },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Rôles et permissions" />

    <h1 class="sr-only">Rôles et permissions</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Rôles"
            description="Créez, renommez ou supprimez les rôles attribués aux membres. Le super admin n’a pas de rôle : il passe tous les contrôles."
        />

        <ul class="panel divide-y divide-border">
            <li
                v-for="role in roles"
                :key="role.id"
                class="flex flex-wrap items-center gap-3 p-3"
            >
                <Form
                    v-bind="RoleController.update.form(role.id)"
                    :options="{ preserveScroll: true }"
                    class="flex flex-1 items-center gap-2"
                    v-slot="{ errors, processing }"
                >
                    <Input
                        name="name"
                        :default-value="role.name"
                        :aria-label="`Nom du rôle ${role.name}`"
                        class="max-w-xs"
                        required
                    />
                    <Button
                        type="submit"
                        variant="outline"
                        size="sm"
                        :disabled="processing"
                        >Renommer</Button
                    >
                    <InputError :message="errors.name" />
                </Form>
                <span class="text-sm text-muted-foreground">
                    {{ role.users_count }} membre{{
                        role.users_count > 1 ? 's' : ''
                    }}
                </span>
                <Form
                    v-bind="RoleController.destroy.form(role.id)"
                    :options="{ preserveScroll: true }"
                    @before="
                        () =>
                            confirm(
                                `Supprimer le rôle « ${role.name} » et ses permissions ?`,
                            )
                    "
                    v-slot="{ errors, processing }"
                >
                    <Button
                        type="submit"
                        variant="ghost"
                        size="sm"
                        :disabled="processing || role.users_count > 0"
                        :title="
                            role.users_count > 0
                                ? 'Retirez d’abord ce rôle à ses membres'
                                : undefined
                        "
                        >Supprimer</Button
                    >
                    <InputError :message="errors.role" />
                </Form>
            </li>
        </ul>

        <Form
            v-bind="RoleController.store.form()"
            reset-on-success
            :options="{ preserveScroll: true }"
            class="flex items-start gap-2"
            v-slot="{ errors, processing }"
        >
            <div class="grid flex-1 gap-1">
                <Input
                    name="name"
                    placeholder="Nouveau rôle, ex. Stagiaire"
                    aria-label="Nom du nouveau rôle"
                    class="max-w-xs"
                    required
                />
                <InputError :message="errors.name" />
            </div>
            <Button type="submit" :disabled="processing">Créer le rôle</Button>
        </Form>
    </div>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Permissions"
            description="Cochez ce que chaque rôle peut faire. Le changement s’applique immédiatement."
        />

        <div class="panel overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="min-w-56">Permission</th>
                        <th
                            v-for="role in roles"
                            :key="role.id"
                            class="text-center!"
                        >
                            {{ role.name }}
                        </th>
                    </tr>
                </thead>
                <tbody v-for="group in groups" :key="group.key">
                    <tr>
                        <th
                            :colspan="roles.length + 1"
                            class="bg-card! font-display text-sm! text-foreground!"
                        >
                            {{ group.name }}
                            <Badge
                                v-if="!group.enabled"
                                variant="secondary"
                                class="ml-2"
                                >Module désactivé</Badge
                            >
                        </th>
                    </tr>
                    <tr
                        v-for="permission in group.permissions"
                        :key="permission.name"
                    >
                        <td>
                            {{ permission.label }}
                            <span
                                class="block font-mono text-xs text-muted-foreground"
                                >{{ permission.name }}</span
                            >
                        </td>
                        <td
                            v-for="role in roles"
                            :key="role.id"
                            class="text-center"
                        >
                            <Checkbox
                                :model-value="
                                    role.permissions.includes(permission.name)
                                "
                                :aria-label="`${permission.label} — ${role.name}`"
                                :data-test="`permission-${role.id}-${permission.name}`"
                                @update:model-value="
                                    (value) =>
                                        toggle(
                                            role,
                                            permission.name,
                                            value === true,
                                        )
                                "
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
