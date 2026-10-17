<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
    BookOpen,
    Building2,
    FolderGit2,
    HeartPulse,
    LayoutGrid,
    Pill,
    Users,
} from '@lucide/vue'
import AppLogo from '@/components/AppLogo.vue'
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import {
    clinical,
    dashboard,
    documentation,
    hospital,
    patients,
    staff,
} from '@/routes'
import type { NavItem } from '@/types'

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Staff',
        href: staff(),
        icon: Users,
    },
    {
        title: 'Hospital',
        href: hospital(),
        icon: Building2,
    },
    {
        title: 'Patients',
        href: patients(),
        icon: HeartPulse,
    },
    {
        title: 'Clinical',
        href: clinical(),
        icon: Pill,
    },
]

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/sureshhemal/dbms_1_las_palmas_project',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: documentation(),
        icon: BookOpen,
    },
]
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
