<template>
    <AdminLayout currentPage="dashboard" pageTitle="Platform Overview">
        <div class="space-y-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in statsCards" :key="stat.label" 
                    class="bg-white border border-slate-900 p-6 rounded-2xl shadow-ambient hover:scale-[1.02] transition-all group overflow-hidden relative">
                    <!-- Decorative Glow -->
                    <div :class="['absolute -right-4 -top-4 w-24 h-24 rounded-full blur-3xl opacity-[0.03] transition-opacity', stat.glowColor]"></div>
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shadow-lg', stat.iconBg]">
                            <span class="material-symbols-outlined text-white text-[24px]">{{ stat.icon }}</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-mono text-slate-500 uppercase tracking-widest">{{ stat.label }}</p>
                            <h3 class="text-2xl font-display font-bold text-slate-900">{{ stat.value }}</h3>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-2">
                        <span :class="['text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1', stat.trend > 0 ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-400']">
                            <span class="material-symbols-outlined text-[14px]">{{ stat.trend > 0 ? 'trending_up' : 'horizontal_rule' }}</span>
                            {{ stat.trend > 0 ? '+' : '' }}{{ stat.trend }}%
                        </span>
                        <span class="text-[10px] text-slate-500 font-medium">vs last month</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Registrations -->
                <div class="lg:col-span-2 bg-white border border-slate-900 rounded-2xl shadow-ambient overflow-hidden">
                    <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">person_add</span>
                            Recent Registrations
                        </h3>
                        <Link href="/admin/users" class="text-xs font-bold text-primary hover:underline">View all users</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 font-mono">
                                <tr>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">Joined</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="user in recentUsers" :key="user.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-[10px] font-bold text-primary border border-primary/20">
                                                {{ user.initials || user.name.charAt(0) }}
                                            </div>
                                            <span class="text-sm font-black text-slate-900">{{ user.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-400">{{ user.email }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-400 font-mono">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="w-2 h-2 rounded-full bg-success inline-block shadow-glow shadow-success/40"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Subscription Breakdown -->
                <div class="bg-white border border-slate-900 rounded-2xl shadow-ambient overflow-hidden">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-slate-600">pie_chart</span>
                            Plan Distribution
                        </h3>
                    </div>
                    <div class="p-8 space-y-6">
                        <div v-for="plan in subscriptionBreakdown" :key="plan.plan" class="space-y-2">
                            <div class="flex justify-between items-end">
                                <span class="text-sm font-bold text-slate-900 capitalize">{{ plan.plan }}</span>
                                <span class="text-xs font-mono text-slate-400">{{ plan.count }} workspaces</span>
                            </div>
                            <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                <div :class="['h-full transition-all duration-1000', getPlanColor(plan.plan)]" 
                                    :style="{ width: getPlanPercentage(plan.count) + '%' }"></div>
                            </div>
                        </div>
                        
                        <!-- Mini Legend -->
                        <div class="mt-10 pt-6 border-t border-slate-200 grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-mono">Top Plan</span>
                                <span class="text-sm font-bold text-slate-900">{{ topPlan }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-mono">Conversion</span>
                                <span class="text-sm font-bold text-slate-900">{{ conversionRate }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: Object,
    recentUsers: Array,
    subscriptionBreakdown: Array
});

const statsCards = computed(() => [
    { 
        label: 'Total Users', 
        value: props.stats.total_users.toLocaleString(), 
        icon: 'groups', 
        iconBg: 'bg-slate-900 shadow-slate-900/20', 
        glowColor: 'bg-slate-900',
        trend: props.stats.growth_rate
    },
    { 
        label: 'Workspaces', 
        value: props.stats.total_workspaces.toLocaleString(), 
        icon: 'domain', 
        iconBg: 'bg-slate-700 shadow-slate-700/20', 
        glowColor: 'bg-slate-700',
        trend: 12
    },
    { 
        label: 'Active Subs', 
        value: props.stats.active_subscriptions.toLocaleString(), 
        icon: 'star', 
        iconBg: 'bg-slate-800 shadow-slate-800/20', 
        glowColor: 'bg-slate-800',
        trend: 8
    },
    { 
        label: 'Total Revenue', 
        value: '$' + (props.stats.total_platform_cost || 0).toLocaleString(), 
        icon: 'payments', 
        iconBg: 'bg-slate-600 shadow-slate-600/20', 
        glowColor: 'bg-slate-600',
        trend: 15
    }
]);

const getPlanColor = (plan) => {
    const colors = {
        free: 'bg-slate-400',
        pro: 'bg-slate-900',
        enterprise: 'bg-slate-700'
    };
    return colors[plan.toLowerCase()] || 'bg-slate-400';
};

const getPlanPercentage = (count) => {
    const total = props.subscriptionBreakdown.reduce((sum, p) => sum + p.count, 0);
    return total > 0 ? (count / total) * 100 : 0;
};

const topPlan = computed(() => {
    if (!props.subscriptionBreakdown.length) return 'None';
    return [...props.subscriptionBreakdown].sort((a, b) => b.count - a.count)[0].plan;
});

const conversionRate = computed(() => {
    const total = props.stats.total_workspaces;
    const active = props.stats.active_subscriptions;
    return total > 0 ? ((active / total) * 100).toFixed(1) : 0;
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};
</script>
