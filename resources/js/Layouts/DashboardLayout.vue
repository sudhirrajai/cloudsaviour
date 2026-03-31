<template>
    <div class="min-h-screen bg-canvas text-content font-sans antialiased selection:bg-primary/30 selection:text-white">
        <!-- Mobile sidebar overlay -->
        <div v-if="sidebarOpen" class="fixed inset-0 bg-black/60 z-50 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 w-64 border-r border-white/5 bg-surface flex flex-col z-50 transition-transform duration-200 shadow-2xl',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-white/5 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center shadow-glow">
                        <span class="material-symbols-outlined text-white text-[20px]">bolt</span>
                    </div>
                    <span class="font-display font-bold text-xl tracking-tight text-white">CloudSaviour</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <!-- Platform Section -->
                <div class="px-3 py-1 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 opacity-70">Platform</div>
                <Link v-for="item in platformNav" :key="item.name"
                   :href="item.href"
                   :class="[
                       'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all',
                       currentPage === item.key
                           ? 'bg-primary/10 text-primary border border-primary/20 shadow-glow'
                           : 'text-content-variant hover:bg-white/5 hover:text-white'
                   ]"
                >
                    <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
                    {{ item.name }}
                </Link>

                <!-- Workspace Section -->
                <div class="px-3 py-1 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 mt-8 opacity-70">Workspace</div>
                <Link v-for="item in workspaceNav" :key="item.name"
                   :href="item.href"
                   :class="[
                       'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all',
                       currentPage === item.key
                           ? 'bg-primary/10 text-primary border border-primary/20 shadow-glow'
                           : 'text-content-variant hover:bg-white/5 hover:text-white'
                   ]"
                >
                    <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-white/5 bg-black/10">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center text-primary text-xs font-bold shadow-glow">
                        {{ $page.props.auth.user.initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-white truncate">{{ $page.props.auth.user.name }}</div>
                        <div class="text-[10px] font-mono text-slate-400 truncate opacity-70">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <button @click="logout" class="text-slate-400 hover:text-error transition-all p-1.5 hover:bg-error/10 rounded-md active:scale-90" title="Logout">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Topbar -->
            <header class="h-16 border-b border-white/5 bg-surface/60 backdrop-blur-xl sticky top-0 z-40 flex items-center justify-between px-6 shadow-sm">
                <div class="flex items-center gap-6">
                    <!-- Mobile menu button -->
                    <button class="lg:hidden text-content-variant hover:text-white p-1.5 hover:bg-white/5 rounded-lg transition-colors" @click="sidebarOpen = !sidebarOpen">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <!-- Workspace indicator -->
                    <div class="flex items-center gap-3 font-mono text-xs relative" v-if="$page.props.activeWorkspace">
                        <span class="text-slate-500 uppercase tracking-[0.2em] opacity-60 text-[9px] font-bold">Workspace</span>
                        <div @click.stop="toggleDropdown('workspace')" class="dropdown-trigger flex items-center gap-2.5 bg-white/5 px-3 py-1.5 rounded-lg border border-white/10 shadow-glass group cursor-pointer hover:bg-white/10 transition-all">
                            <span class="w-2 h-2 rounded-full bg-tertiary shadow-glow shadow-tertiary/40"></span>
                            <span class="text-white font-bold tracking-wide">{{ $page.props.activeWorkspace.name }}</span>
                            <span class="material-symbols-outlined text-[16px] text-slate-500 group-hover:text-white transition-colors">unfold_more</span>
                        </div>

                        <!-- Workspace Dropdown Menu -->
                        <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                            <div v-if="activeDropdown === 'workspace'" class="absolute top-full left-0 mt-3 w-64 bg-[#1a243e]/85 border border-white/20 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.6)] p-2 z-[60] backdrop-blur-2xl overflow-hidden">
                                <!-- Subtle glass shine -->
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-transparent pointer-events-none"></div>
                                
                                <div class="relative z-10">
                                    <div class="px-3 py-2 text-[10px] font-mono text-slate-400 uppercase tracking-widest border-b border-white/5 mb-2">Switch Workspace</div>
                                    <div class="space-y-1 max-h-64 overflow-y-auto pr-1">
                                        <button v-for="ws in $page.props.workspaces" :key="ws.id" 
                                            @click="switchWorkspace(ws.id)"
                                            :class="[
                                                'w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg transition-colors group',
                                                ws.id === $page.props.activeWorkspace.id ? 'bg-primary/20 text-primary' : 'text-content-variant hover:bg-white/5 hover:text-white'
                                            ]"
                                        >
                                            <span :class="['w-1.5 h-1.5 rounded-full', ws.id === $page.props.activeWorkspace.id ? 'bg-primary shadow-glow' : 'bg-slate-600']"></span>
                                            <span class="text-sm font-medium">{{ ws.name }}</span>
                                            <span v-if="ws.id === $page.props.activeWorkspace.id" class="material-symbols-outlined text-xs ml-auto">check</span>
                                        </button>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-white/5">
                                        <Link href="/dashboard/workspace/create" class="flex items-center gap-3 px-3 py-2 rounded-lg text-tertiary hover:bg-tertiary/10 transition-colors">
                                            <span class="material-symbols-outlined text-sm">add_circle</span>
                                            <span class="text-sm font-medium">New Workspace</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
                <div class="flex items-center gap-5">
                    <!-- Notifications Dropdown -->
                    <div class="relative">
                        <button @click.stop="toggleDropdown('notifications')" class="dropdown-trigger text-slate-400 hover:text-white transition-all relative p-2 hover:bg-white/5 rounded-full group">
                            <span class="material-symbols-outlined text-[22px]">notifications</span>
                            <span v-if="$page.props.notifications?.length > 0" class="absolute top-2.5 right-2.5 w-2 h-2 bg-error rounded-full ring-2 ring-surface group-hover:scale-125 transition-transform"></span>
                        </button>
                        
                        <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                            <div v-if="activeDropdown === 'notifications'" class="absolute top-full right-0 mt-3 w-80 bg-[#1a243e]/85 border border-white/20 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.6)] p-4 z-[60] backdrop-blur-2xl overflow-hidden">
                                <!-- Subtle glass shine -->
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-transparent pointer-events-none"></div>

                                <div class="relative z-10">
                                    <div class="flex justify-between items-center border-b border-white/5 pb-3 mb-3">
                                    <span class="text-xs font-mono font-bold uppercase tracking-widest text-slate-400">Notifications</span>
                                </div>
                                
                                <div v-if="!$page.props.notifications || $page.props.notifications.length === 0" class="py-8 text-center">
                                    <div class="w-12 h-12 rounded-full bg-white/5 mx-auto flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined text-slate-500 text-xl">notifications_off</span>
                                    </div>
                                    <p class="text-sm text-slate-400">No recent activity</p>
                                </div>

                                <div v-else class="space-y-3 max-h-96 overflow-y-auto no-scrollbar pr-1">
                                    <div v-for="notification in $page.props.notifications" :key="notification.id" class="flex gap-4 group cursor-pointer p-2 rounded-lg hover:bg-white/5 transition-colors">
                                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center shrink-0', notification.colorClass]">
                                            <span class="material-symbols-outlined text-sm">{{ notification.icon }}</span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-white font-medium mb-1 line-clamp-2">{{ notification.description }}</p>
                                            <p class="text-[10px] text-slate-500">{{ notification.time }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-white/5 text-center" v-if="$page.props.notifications?.length > 0">
                                    <Link href="/dashboard/activity" class="text-[10px] text-slate-400 hover:text-white transition-colors">View all activity</Link>
                                </div>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <div @click.stop="toggleDropdown('profile')" class="dropdown-trigger w-9 h-9 rounded-full bg-primary/10 border border-white/10 flex items-center justify-center text-white text-xs font-bold cursor-pointer hover:ring-2 hover:ring-primary/50 transition-all shadow-glow hover:scale-105 active:scale-95 transition-all">
                            {{ $page.props.auth.user.initials }}
                        </div>

                        <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                            <div v-if="activeDropdown === 'profile'" class="absolute top-full right-0 mt-3 w-56 bg-[#1a243e]/85 border border-white/20 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.6)] p-2 z-[60] backdrop-blur-2xl overflow-hidden">
                                <!-- Subtle glass shine -->
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-transparent pointer-events-none"></div>

                                <div class="relative z-10">
                                    <div class="px-3 py-2 border-b border-white/5 mb-2">
                                        <div class="text-sm font-bold text-white">{{ $page.props.auth.user.name }}</div>
                                        <div class="text-xs text-slate-500 truncate">{{ $page.props.auth.user.email }}</div>
                                    </div>
                                    <div class="space-y-1">
                                        <Link href="/dashboard/settings" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-content-variant hover:bg-white/5 hover:text-white transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">account_circle</span>
                                            My Profile
                                        </Link>
                                        <Link href="/dashboard/settings" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-content-variant hover:bg-white/5 hover:text-white transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">security</span>
                                            Security
                                        </Link>
                                        <Link href="/dashboard/cost" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-content-variant hover:bg-white/5 hover:text-white transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">payments</span>
                                            Billing
                                        </Link>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-white/5">
                                        <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-error hover:bg-error/10 transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">logout</span>
                                            Logout
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </transition>
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

        <!-- Notifications -->
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
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/UI/Toast.vue';

const props = defineProps({
    currentPage: {
        type: String,
        default: '',
    },
});

const page = usePage();
const sidebarOpen = ref(false);

// Dropdown State
const activeDropdown = ref(null);

const toggleDropdown = (name) => {
    activeDropdown.value = activeDropdown.value === name ? null : name;
};

const closeDropdowns = (e) => {
    if (!e.target.closest('.dropdown-trigger')) {
        activeDropdown.value = null;
    }
};

onMounted(() => {
    window.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdowns);
});

const switchWorkspace = (id) => {
    router.post('/dashboard/workspace/switch', { workspace_id: id }, {
        onSuccess: () => activeDropdown.value = null
    });
};

const clearFlash = (key) => {
    if (page.props.flash) {
        page.props.flash[key] = null;
    }
};

// Auto-clear flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        setTimeout(() => clearFlash('success'), 5000);
    }
    if (flash?.error) {
        setTimeout(() => clearFlash('error'), 8000);
    }
}, { deep: true, immediate: true });

const logout = () => {
    router.post('/logout');
};

const platformNav = [
    { name: 'Servers', key: 'servers', href: '/dashboard/servers', icon: 'dns' },
    { name: 'Cost', key: 'cost', href: '/dashboard/cost', icon: 'payments' },
    { name: 'Idle Scanner', key: 'idle', href: '/dashboard/idle', icon: 'search_insights' },
    { name: 'Schedules', key: 'schedules', href: '/dashboard/schedules', icon: 'schedule' },
    { name: 'AI Insights', key: 'ai-insights', href: '/dashboard/ai-insights', icon: 'auto_awesome' },
];

const workspaceNav = [
    { name: 'Members', key: 'members', href: '/dashboard/members', icon: 'groups' },
    { name: 'Activity', key: 'activity', href: '/dashboard/activity', icon: 'timeline' },
    { name: 'Settings', key: 'settings', href: '/dashboard/settings', icon: 'settings' },
];
</script>
