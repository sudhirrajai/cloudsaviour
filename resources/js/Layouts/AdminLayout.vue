<template>
    <div class="min-h-screen bg-canvas text-content font-sans antialiased selection:bg-primary/30 selection:text-white transition-colors duration-300">
        <!-- Mobile sidebar overlay -->
        <div v-if="sidebarOpen" class="fixed inset-0 bg-black/60 z-50 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 w-64 border-r border-border-ghost bg-surface flex flex-col z-50 transition-transform duration-200 shadow-2xl',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-border-ghost shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-tertiary flex items-center justify-center shadow-glow shadow-tertiary/20">
                        <span class="material-symbols-outlined text-white text-[20px]">admin_panel_settings</span>
                    </div>
                    <span class="font-display font-bold text-xl tracking-tight text-content">Admin Central</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <div class="px-3 py-1 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 opacity-70">Platform Management</div>
                
                <Link v-for="item in adminNav" :key="item.name"
                   :href="item.href"
                   :class="[
                       'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all',
                       currentPage === item.key
                           ? 'bg-primary/10 text-primary border border-primary/20 shadow-glow'
                           : 'text-content-variant hover:bg-slate-100 hover:text-primary'
                   ]"
                >
                    <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
                    {{ item.name }}
                </Link>

                <div class="px-3 py-1 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 mt-8 opacity-70">Quick Links</div>
                <Link href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg text-content-variant hover:bg-slate-100 hover:text-primary transition-all">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    User Dashboard
                </Link>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-border-ghost bg-slate-50">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center text-primary text-xs font-bold shadow-glow">
                        {{ $page.props.auth.user.initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-content truncate">{{ $page.props.auth.user.name }}</div>
                        <div class="text-[10px] font-mono text-slate-400 truncate opacity-70">Super Admin</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Topbar -->
            <header class="h-16 border-b border-border-ghost bg-surface/60 backdrop-blur-xl sticky top-0 z-40 flex items-center justify-between px-6 shadow-sm">
                <div class="flex items-center gap-6">
                    <button class="lg:hidden text-content-variant hover:text-content p-1.5 hover:bg-slate-100 rounded-lg transition-colors" @click="sidebarOpen = !sidebarOpen">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-lg font-bold text-content tracking-tight">{{ pageTitle }}</h1>
                </div>
                
                <div class="flex items-center gap-5">
                    <!-- Profile Dropdown (Simplified) -->
                    <div class="dropdown-trigger w-9 h-9 rounded-full bg-primary/10 border border-border-ghost flex items-center justify-center text-content text-xs font-bold cursor-pointer shadow-glow">
                        {{ $page.props.auth.user.initials }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-6 md:p-10 max-w-7xl mx-auto w-full">
                <slot />
            </div>
        </main>

        <!-- Background grid overlay -->
        <div class="fixed inset-0 pointer-events-none z-[-1] opacity-40" style="background-image: linear-gradient(to right, rgba(59, 130, 246, 0.03) 1px, transparent 1px), linear-gradient(to bottom, rgba(59, 130, 246, 0.03) 1px, transparent 1px); background-size: 40px 40px;"></div>

        <!-- Toast Notifications -->
        <Toast 
            v-if="$page.props.flash"
            :success="$page.props.flash.success" 
            :error="$page.props.flash.error"
            @clear-success="clearFlash('success')"
            @clear-error="clearFlash('error')"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/UI/Toast.vue';

const props = defineProps({
    currentPage: String,
    pageTitle: {
        type: String,
        default: 'Admin Panel'
    }
});

const page = usePage();
const sidebarOpen = ref(false);

const clearFlash = (key) => {
    if (page.props.flash) {
        page.props.flash[key] = null;
    }
};

watch(() => page.props.flash, (flash) => {
    if (flash?.success) setTimeout(() => clearFlash('success'), 5000);
    if (flash?.error) setTimeout(() => clearFlash('error'), 8000);
}, { deep: true, immediate: true });

const adminNav = [
    { name: 'Dashboard', key: 'dashboard', href: '/admin', icon: 'monitoring' },
    { name: 'User Management', key: 'users', href: '/admin/users', icon: 'group' },
    { name: 'Workspaces', key: 'workspaces', href: '/admin/workspaces', icon: 'domain' },
    { name: 'Subscription Plans', key: 'plans', href: '/admin/plans', icon: 'subscriptions' },
    { name: 'Waitlist', key: 'waitlist', href: '/admin/waitlists', icon: 'hourglass_top' },
];
</script>

<style>
/* Global light theme overrides for specific components if needed */
.light-theme .text-white {
    color: var(--color-content) !important;
}

.light-theme .border-white\/5 {
    border-color: rgba(0, 0, 0, 0.05) !important;
}

.light-theme .bg-surface\/60 {
    background-color: rgba(255, 255, 255, 0.6) !important;
}
</style>
