<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import ModuleController from '@/actions/App/Modules/Core/Http/Controllers/Settings/ModuleController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/settings/modules';

type AppModule = {
    key: string;
    name: string;
    description: string;
    required: boolean;
    enabled: boolean;
    dependencies: string[];
    dependents: string[];
};

defineProps<{
    modules: AppModule[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Modules', href: index() }],
    },
});

const page = usePage();

function setEnabled(module: AppModule, enabled: boolean) {
    router.patch(
        ModuleController.update.url(module.key),
        { enabled },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Modules" />

    <h1 class="sr-only">Modules</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Modules"
            description="Un module désactivé disparaît du menu et ses pages ne répondent plus, pour tout le monde."
        />

        <InputError :message="page.props.errors.enabled" />

        <ul class="divide-y rounded-lg border">
            <li
                v-for="module in modules"
                :key="module.key"
                class="flex flex-wrap items-start justify-between gap-4 p-4"
            >
                <div class="space-y-1">
                    <p class="font-medium">
                        {{ module.name }}
                        <Badge
                            v-if="module.required"
                            variant="outline"
                            class="ml-2"
                            >Toujours actif</Badge
                        >
                        <Badge v-else-if="module.enabled" class="ml-2"
                            >Activé</Badge
                        >
                        <Badge v-else variant="secondary" class="ml-2"
                            >Désactivé</Badge
                        >
                    </p>
                    <p
                        v-if="module.description"
                        class="text-sm text-muted-foreground"
                    >
                        {{ module.description }}
                    </p>
                    <p
                        v-if="module.dependencies.length"
                        class="text-sm text-muted-foreground"
                    >
                        Requiert : {{ module.dependencies.join(', ') }}
                    </p>
                    <p
                        v-if="module.dependents.length"
                        class="text-sm text-muted-foreground"
                    >
                        Requis par : {{ module.dependents.join(', ') }}
                    </p>
                </div>
                <Button
                    v-if="!module.required"
                    :variant="module.enabled ? 'outline' : 'default'"
                    size="sm"
                    :data-test="`module-toggle-${module.key}`"
                    @click="setEnabled(module, !module.enabled)"
                >
                    {{ module.enabled ? 'Désactiver' : 'Activer' }}
                </Button>
            </li>
        </ul>
    </div>
</template>
