<template>
    <AdminLayout currentPage="users" :pageTitle="`User: ${user.name}`">
        <div class="space-y-8">
            <div class="flex items-center justify-between">
                <Link href="/admin/users" class="flex items-center gap-2 text-slate-500 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span class="text-xs font-bold uppercase tracking-widest">Back to Users</span>
                </Link>
                <button 
                    @click="toggleAdmin"
                    :class="[
                        'px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-glow',
                        user.is_admin ? 'bg-error/10 text-error border border-error/20' : 'bg-primary/10 text-primary border border-primary/20'
                    ]"
                >
                    {{ user.is_admin ? 'Revoke Admin' : 'Make Admin' }}
                </button>
            </div>

            <!-- User Profile Header -->
            <div class="bg-surface border border-white/5 rounded-3xl p-8 shadow-ambient relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
                
                <div class="flex flex-col md:flex-row gap-8 items-start relative z-10">
                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-3xl font-bold text-white shadow-glow border border-white/10">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 space-y-4">
                        <div>
                            <h2 class="text-3xl font-display font-bold text-white tracking-tight">{{ user.name }}</h2>
                            <p class="text-slate-500 font-mono">{{ user.email }}</p>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            <div class="bg-white/5 px-4 py-2 rounded-2xl border border-white/5">
                                <span class="text-[10px] text-slate-500 uppercase font-mono block mb-0.5">Role</span>
                                <span class="text-sm font-bold text-white">{{ user.is_admin ? 'Super Admin' : 'Regular User' }}</span>
                            </div>
                            <div class="bg-white/5 px-4 py-2 rounded-2xl border border-white/5">
                                <span class="text-[10px] text-slate-500 uppercase font-mono block mb-0.5">Joined</span>
                                <span class="text-sm font-bold text-white">{{ user.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- User Workspaces -->
                <div class="lg:col-span-2 space-y-6">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-tertiary">hub</span>
                        Associated Workspaces
                    </h3>
                    
                    <div v-for="ws in user.workspaces" :key="ws.id" 
                        class="bg-surface border border-white/5 p-6 rounded-2xl shadow-glass hover:border-white/10 transition-all flex items-center justify-between group">
                        <div class="space-y-1">
                            <div class="text-sm font-bold text-white group-hover:text-primary transition-colors">{{ ws.name }}</div>
                            <div class="flex items-center gap-4">
                                <span class="text-[10px] font-mono text-slate-500 uppercase">Plan: {{ ws.plan }}</span>
                                <span class="text-[10px] font-mono text-slate-500 uppercase">Servers: {{ ws.ec2_instances_count }}</span>
                                <span class="text-[10px] font-mono text-slate-500 uppercase">RDS: {{ ws.rds_instances_count }}</span>
                            </div>
                        </div>
                        <Link :href="`/admin/workspaces`" class="p-2 rounded-lg bg-white/5 text-slate-500 hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-sm">settings</span>
                        </Link>
                    </div>

                    <div v-if="user.workspaces.length === 0" class="py-12 bg-white/5 rounded-3xl border border-dashed border-white/10 text-center">
                        <p class="text-slate-500 text-sm">No workspaces associated with this user.</p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">history</span>
                        Recent Activity
                    </h3>
                    
                    <div class="bg-surface border border-white/5 rounded-2xl shadow-glass overflow-hidden divide-y divide-white/5">
                        <div v-for="log in user.activity_logs" :key="log.id" class="p-4 hover:bg-white/5 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-secondary/10 flex items-center justify-center text-secondary">
                                    <span class="material-symbols-outlined text-[14px]">bolt</span>
                                </div>
                                <div>
                                    <p class="text-xs text-white font-medium leading-tight mb-1">{{ log.action }} on {{ log.resource_type }}</p>
                                    <p class="text-[10px] text-slate-500">{{ formatDate(log.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="user.activity_logs.length === 0" class="p-8 text-center text-xs text-slate-500">
                            No recent activity found.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object
});

const toggleAdmin = () => {
    if (confirm(`Toggle admin status for ${props.user.name}?`)) {
        router.post(`/admin/users/${props.user.id}/toggle-admin`, {}, {
            preserveScroll: true
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
