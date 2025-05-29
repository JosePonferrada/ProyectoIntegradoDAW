<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { UserCircle, LockKeyhole, Palette } from 'lucide-vue-next';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
        icon: UserCircle,
    },
    {
        title: 'Password',
        href: '/settings/password',
        icon: LockKeyhole,
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
        icon: Palette,
    },
];

const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-6 md:px-6 lg:px-8">
        <Heading 
            title="Settings" 
            description="Manage your profile and account settings" 
            class="mb-8 text-gray-800 dark:text-white" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
            <aside class="w-full lg:w-60 xl:w-72">
                <nav class="flex flex-col space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="[
                            'w-full justify-start text-left px-3 py-2.5 rounded-md transition-colors duration-150 ease-in-out group', // Añadido 'group' para hover en icono
                            // Estilos base para modo claro
                            'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
                            // Estilos para modo oscuro (override)
                            'dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
                            { 
                                // Estilo activo para modo claro
                                'bg-red-500 text-white shadow-md hover:bg-red-600 dark:bg-red-600/80 dark:hover:bg-red-600': currentPath === item.href,
                                // Asegurar que el hover no activo no use el color activo (especialmente para modo claro)
                                'hover:bg-gray-100 dark:hover:bg-gray-700/50': currentPath !== item.href 
                            }
                        ]"
                        as-child
                    >
                        <Link :href="item.href" class="flex items-center space-x-3">
                            <component 
                                :is="item.icon" 
                                :class="[
                                    'h-5 w-5 flex-shrink-0',
                                    // Color de icono en modo claro
                                    currentPath === item.href ? 'text-white dark:text-white' : 'text-gray-500 group-hover:text-gray-700',
                                    // Color de icono en modo oscuro
                                    currentPath === item.href ? 'dark:text-white' : 'dark:text-gray-400 dark:group-hover:text-gray-200'
                                ]" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden border-gray-200 dark:border-gray-700" />

            <div class="flex-1">
                <section class="space-y-8">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
