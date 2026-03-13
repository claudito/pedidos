<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ClipboardList,
    LayoutGrid,
    Package,
    Shield,
    ShieldCheck,
    Truck,
    UserCog,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
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
import type { NavItem } from '@/types';

type SharedAuth = {
    permissions?: string[];
};

const page = usePage<{ auth: SharedAuth }>();

const permissions = computed(() => page.props.auth?.permissions ?? []);

const mainNavItems = computed<NavItem[]>(() => {
    const items: Array<NavItem & { permission?: string }> = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Roles',
            href: '/seguridad/roles',
            icon: Shield,
            permission: 'roles',
        },
        {
            title: 'Permisos',
            href: '/seguridad/permisos',
            icon: ShieldCheck,
            permission: 'permisos',
        },
        {
            title: 'Usuarios',
            href: '/seguridad/usuarios',
            icon: UserCog,
            permission: 'usuarios',
        },
        {
            title: 'Clientes',
            href: '/clientes',
            icon: Users,
            permission: 'clientes',
        },
        {
            title: 'Productos',
            href: '/productos',
            icon: Package,
            permission: 'productos',
        },
        {
            title: 'Pedidos',
            href: '/pedidos',
            icon: ClipboardList,
            permission: 'pedidos',
        },
        {
            title: 'Seguimientos',
            href: '/seguimientos',
            icon: Truck,
            permission: 'seguimientos',
        },
    ];

    return items.filter((item) => !item.permission || permissions.value.includes(item.permission));
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
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
