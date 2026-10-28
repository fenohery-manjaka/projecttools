<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';

// Built on the server from the enabled modules and the user's permissions.
const page = usePage();
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar" class="border-r-border">
        <SidebarHeader class="px-3 pt-[18px] pb-0">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="h-auto gap-2.5 px-2 py-0.5 hover:bg-transparent"
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="gap-3.5 px-3 pt-3.5">
            <NavMain :groups="page.props.navigation" />
        </SidebarContent>

        <SidebarFooter class="mx-3 mb-3 border-t border-border px-0 pt-3">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
