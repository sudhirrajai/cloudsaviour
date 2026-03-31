<template>
    <DashboardLayout currentPage="activity">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-white mb-2">Activity Log</h1>
                <p class="text-content-variant font-sans">Complete audit trail of all actions performed in this workspace.</p>
            </div>
            <div class="flex items-center gap-2">
                <button v-for="filter in actorFilters" :key="filter.value"
                    :class="[
                        'px-4 py-1.5 rounded-full text-[10px] font-mono border transition-colors',
                        activeFilter === filter.value
                            ? 'bg-blue-500/10 text-blue-400 border-blue-500/30'
                            : 'hover:bg-slate-800/50 text-content-variant border-slate-700/30'
                    ]"
                    @click="applyFilter(filter.value)"
                >
                    {{ filter.label }}
                </button>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard label="Total Actions" :value="String(logs.total)" sublabel="LOGGED EVENTS" icon="timeline" borderColor="primary" valueColor="white" />
            <StatCard label="Manual" :value="String(logs.data.filter(l => l.actor_type === 'user').length)" sublabel="USER OPERATIONS" icon="person" borderColor="secondary" valueColor="white" />
            <StatCard label="System" :value="String(logs.data.filter(l => l.actor_type === 'system').length)" sublabel="AUTOMATED" icon="smart_toy" borderColor="tertiary" valueColor="white" />
            <StatCard label="Scheduled" :value="String(logs.data.filter(l => l.actor_type === 'schedule').length)" sublabel="CRON TRIGGERED" icon="schedule" borderColor="amber" valueColor="white" />
        </div>

        <!-- Activity Table -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1000px]">
                    <thead class="bg-canvas text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-normal">Time</th>
                            <th class="px-6 py-4 font-normal">Actor</th>
                            <th class="px-6 py-4 font-normal">Action</th>
                            <th class="px-6 py-4 font-normal">Resource</th>
                            <th class="px-6 py-4 font-normal text-right">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="log in logs.data" :key="log.id" class="group hover:bg-white/[0.03] transition-colors">
                            <td class="px-6 py-5">
                                <span class="font-mono text-xs text-white whitespace-nowrap">{{ log.time }}</span>
                                <div class="text-[9px] text-slate-500 font-mono mt-1 uppercase tracking-tighter opacity-70">{{ log.date }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div v-if="log.actor_type === 'user'" :class="['w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white bg-primary/20 border border-primary/30 shadow-glow shadow-primary/20']">
                                        {{ log.actor_initials }}
                                    </div>
                                    <div v-else :class="['w-9 h-9 rounded-full flex items-center justify-center border border-white/10 shadow-glass', actorIconBg(log.actor_type)]">
                                        <span :class="['material-symbols-outlined text-[18px]', actorIconColor(log.actor_type)]">{{ actorIcon(log.actor_type) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-white group-hover:text-primary transition-colors">{{ log.actor_name }}</div>
                                        <span :class="['inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-mono uppercase tracking-widest mt-1 border', actorBadge(log.actor_type)]">
                                            {{ log.actor_type }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono border uppercase tracking-wider',
                                    actionBadge(log.action)
                                ]">
                                    {{ log.action }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-sm font-medium text-white">{{ log.resource_type }}</div>
                                <div class="font-mono text-[10px] text-slate-500 mt-1 opacity-70">{{ log.resource_id || log.resource_name }}</div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span class="font-mono text-xs text-slate-500 opacity-80">{{ log.ip_address || '—' }}</span>
                            </td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-content-variant font-mono text-xs italic">No activity logged yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="px-6 py-5 border-t border-white/5 bg-surface-elevated/40 flex justify-center gap-3">
                <button v-for="link in logs.links" :key="link.label" 
                    @click="router.get(link.url)"
                    :disabled="!link.url"
                    v-html="link.label"
                    :class="[
                        'px-4 py-1.5 rounded-md text-[10px] font-mono border transition-all active:scale-95 shadow-sm',
                        link.active ? 'bg-primary text-white border-primary shadow-glow' : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-white',
                        !link.url ? 'opacity-30 cursor-not-allowed mx-1' : 'cursor-pointer'
                    ]"
                ></button>
            </div>

            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';

const props = defineProps({
    logs: {
        type: Object,
        required: true
    }
});

const page = usePage();
const activeFilter = computed(() => {
    if (typeof window !== 'undefined') {
        return new URLSearchParams(window.location.search).get('actor_type') || 'all';
    }
    return 'all';
});

const actorFilters = [
    { label: 'All', value: 'all' },
    { label: 'User', value: 'user' },
    { label: 'System', value: 'system' },
    { label: 'AI', value: 'ai' },
    { label: 'Schedule', value: 'schedule' },
];

const applyFilter = (value) => {
    router.get('/dashboard/activity', { actor_type: value }, {
        preserveState: true,
        preserveScroll: true
    });
};

const actionBadge = (action) => ({
    started: 'bg-tertiary/10 text-tertiary border-tertiary/20',
    stopped: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    deleted: 'bg-error/10 text-error border-error/20',
    created: 'bg-primary/10 text-primary border-primary/20',
    applied: 'bg-secondary/10 text-secondary border-secondary/20',
    sync: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    detected: 'bg-surface-elevated text-content-variant border-border-ghost',
}[action] || 'bg-surface-elevated text-content-variant border-border-ghost');

const actorIcon = (type) => ({ system: 'smart_toy', ai: 'auto_awesome', schedule: 'schedule' }[type] || 'person');
const actorIconBg = (type) => ({ system: 'bg-tertiary/20', ai: 'bg-primary/20', schedule: 'bg-amber-500/20' }[type] || 'bg-slate-600/20');
const actorIconColor = (type) => ({ system: 'text-tertiary', ai: 'text-primary', schedule: 'text-amber-500' }[type] || 'text-slate-400');
const actorBadge = (type) => ({
    user: 'bg-secondary/10 text-secondary',
    system: 'bg-tertiary/10 text-tertiary',
    ai: 'bg-primary/10 text-primary',
    schedule: 'bg-amber-500/10 text-amber-400',
}[type] || '');
</script>

