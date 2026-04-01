<template>
    <AdminLayout currentPage="users" pageTitle="User Management">
        <div class="space-y-6">
            <!-- Header/Filters -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative max-w-sm w-full group">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-primary transition-colors">search</span>
                    <input 
                        type="text" 
                        placeholder="Search by name or email..." 
                        v-model="search"
                        class="w-full bg-surface border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-glass"
                    />
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest bg-white/5 px-3 py-1.5 rounded-lg border border-white/5">
                        Total Users: {{ users.total }}
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-surface border border-white/5 rounded-2xl shadow-ambient overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-black/20 text-[10px] uppercase tracking-widest text-slate-500 font-mono">
                            <tr>
                                <th class="px-6 py-5">User Profile</th>
                                <th class="px-6 py-5">Role</th>
                                <th class="px-6 py-5">Workspaces</th>
                                <th class="px-6 py-5">Joined Date</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-xs font-bold text-white shadow-glow border border-white/10">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-white group-hover:text-primary transition-colors">{{ user.name }}</div>
                                            <div class="text-[10px] font-mono text-slate-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span v-if="user.is_admin" class="px-2 py-1 bg-tertiary/10 text-tertiary text-[10px] font-bold rounded-full border border-tertiary/20 uppercase tracking-tighter">Super Admin</span>
                                        <span v-else class="px-2 py-1 bg-slate-500/10 text-slate-400 text-[10px] font-bold rounded-full border border-white/5 uppercase tracking-tighter">User</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 ml-0.5">
                                        <div :class="['w-2 h-2 rounded-full', user.is_active ? 'bg-success shadow-glow shadow-success/40' : 'bg-error']"></div>
                                        <span :class="['text-[9px] font-bold uppercase tracking-widest', user.is_active ? 'text-success' : 'text-error']">{{ user.is_active ? 'Active' : 'Suspended' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-mono font-bold text-white">{{ user.workspaces_count }}</span>
                                        <span class="text-[9px] text-slate-500 uppercase font-bold tracking-widest">Active Units</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400 font-mono">{{ user.created_at }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 px-2">
                                        <!-- Toggle Admin -->
                                        <button 
                                            @click="toggleAdmin(user)"
                                            :title="user.is_admin ? 'Revoke Admin' : 'Grant Admin'"
                                            class="p-2 rounded-lg bg-surface-elevated border border-white/5 text-slate-400 hover:text-white hover:bg-white/10 transition-all"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">{{ user.is_admin ? 'admin_panel_settings' : 'person_add' }}</span>
                                        </button>
                                        
                                        <!-- Toggle Active -->
                                        <button 
                                            @click="toggleActive(user)"
                                            :title="user.is_active ? 'Deactivate User' : 'Activate User'"
                                            :class="['p-2 rounded-lg border transition-all', user.is_active ? 'border-warning/20 text-warning hover:bg-warning/10' : 'border-success/20 text-success hover:bg-success/10']"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">{{ user.is_active ? 'block' : 'check_circle' }}</span>
                                        </button>

                                        <!-- Terminate User -->
                                        <button 
                                            @click="terminateUser(user)"
                                            title="Terminate User"
                                            class="p-2 rounded-lg border border-error/20 text-error hover:bg-error/10 transition-all"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">person_remove</span>
                                        </button>

                                        <Link 
                                            :href="`/admin/users/${user.id}`"
                                            class="p-2 rounded-lg bg-surface-elevated border border-white/5 text-slate-400 hover:text-primary"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="users.data.length === 0" class="py-20 text-center">
                    <div class="w-16 h-16 rounded-full bg-white/5 border border-white/5 mx-auto flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-slate-500 text-3xl">search_off</span>
                    </div>
                    <p class="text-slate-400 font-medium">No users found matching your search.</p>
                </div>

                <!-- Pagination -->
                <div class="p-6 border-t border-white/5 bg-black/10 flex items-center justify-between">
                    <div class="text-xs text-slate-500 font-mono">
                        Showing {{ users.from }} to {{ users.to }} of {{ users.total }}
                    </div>
                    <div class="flex items-center gap-2">
                        <Link 
                            v-for="link in users.links" 
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 text-xs font-bold rounded-lg transition-all border',
                                link.active ? 'bg-primary text-white border-primary shadow-glow shadow-primary/40' : 'bg-surface border-white/5 text-slate-400 hover:bg-white/5',
                                !link.url ? 'opacity-30 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { watchDebounced } from '@vueuse/core';

const props = defineProps({
    users: Object,
    filters: Object
});

const search = ref(props.filters.search);

watchDebounced(search, (value) => {
    router.get('/admin/users', { search: value }, { preserveState: true, replace: true });
}, { debounce: 500 });

const toggleAdmin = (user) => {
    if (confirm(`Are you sure you want to ${user.is_admin ? 'revoke' : 'grant'} admin access for ${user.name}?`)) {
        router.post(`/admin/users/${user.id}/toggle-admin`, {}, { preserveScroll: true });
    }
};

const toggleActive = (user) => {
    const action = user.is_active ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} ${user.name}?`)) {
        router.post(`/admin/users/${user.id}/toggle-active`, {}, { preserveScroll: true });
    }
};

const terminateUser = (user) => {
    if (confirm(`CRITICAL: Are you sure you want to TERMINATE ${user.name}? This will suspend their account and all their owned workspaces immediately.`)) {
        router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
    }
};
</script>
