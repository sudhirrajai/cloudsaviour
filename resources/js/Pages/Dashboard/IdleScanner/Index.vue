<template>
    <DashboardLayout currentPage="idle">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-white mb-2">Idle Scanner</h1>
                    <p class="text-content-variant font-sans">Detecting unutilized infrastructure and automated cost leakages.</p>
                </div>
                <div class="px-3 py-1 bg-tertiary/10 border border-tertiary/20 rounded-full flex items-center gap-2 group transition-all mt-1 lg:mt-3">
                    <span class="flex h-1.5 w-1.5 rounded-full bg-tertiary animate-pulse shadow-[0_0_8px_#4edea3]"></span>
                    <span class="text-[9px] font-mono text-tertiary font-bold tracking-widest uppercase">Live Sync</span>
                </div>
            </div>
            <button @click="scanNow" :disabled="scanning" class="bg-primary text-white px-6 py-2.5 rounded-sm font-mono text-[11px] uppercase tracking-wider font-bold flex items-center gap-3 hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(59,130,246,0.2)] disabled:opacity-50">
                <span class="material-symbols-outlined text-sm">{{ scanning ? 'sync_alt' : 'sync' }}</span>
                {{ scanning ? 'Scanning...' : 'Scan now' }}
            </button>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard label="Issues Found" :value="String(stats.total)" sublabel="ACTIVE LEAKS" icon="warning" borderColor="error" valueColor="error" />
            <StatCard label="Potential Savings" sublabel="PER MONTHLY CYCLE" icon="trending_down" borderColor="tertiary" valueColor="tertiary">
                <span class="font-mono">${{ Math.round(stats.totalSavings) }}</span>
            </StatCard>
            <StatCard label="Ignored" :value="String(stats.ignored)" sublabel="FALSE POSITIVES" icon="visibility_off" borderColor="primary" valueColor="white" />
            <StatCard label="Scanner Status" value="ACTIVE" sublabel="LIVE REFRESH ENABLED" icon="sensors" borderColor="secondary" valueColor="white" />
        </div>

        <!-- Issues Section -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 overflow-hidden relative">
            <!-- Filter Bar -->
            <div class="bg-surface-elevated/80 px-6 py-4 flex flex-wrap items-center gap-4 border-b border-white/5">
                <span class="text-[11px] font-mono text-slate-500 uppercase tracking-widest mr-2 opacity-70">Filter by</span>
                <button v-for="filter in ['All', 'EBS', 'Elastic IP', 'NAT', 'Snapshots']" :key="filter"
                    class="px-4 py-1.5 rounded-full text-[10px] font-mono border transition-all active:scale-95"
                    :class="activeFilter === filter ? 'bg-primary/20 text-primary border-primary/30 shadow-glow' : 'hover:bg-white/5 text-slate-400 border-white/5'"
                    @click="activeFilter = filter"
                >
                    {{ filter }}
                </button>
            </div>
            
            <!-- Table Body -->
            <div class="overflow-x-auto relative z-10 w-full">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead class="bg-canvas text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-normal">Resource Type</th>
                            <th class="px-6 py-4 font-normal">Identity & Description</th>
                            <th class="px-6 py-4 font-normal">Severity</th>
                            <th class="px-6 py-4 font-normal text-slate-400">Leak Rate</th>
                            <th class="px-6 py-4 font-normal text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <IdleResourceRow 
                            v-for="resource in filteredResources" 
                            :key="resource.id" 
                            :resource="resource" 
                            @ignore="handleIgnore"
                            @resolve="openDeleteModal"
                        />
                        <tr v-if="filteredResources.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-content-variant font-mono text-xs italic">
                                No idle resources detected {{(activeFilter !== 'All' ? 'for ' + activeFilter : 'in this workspace')}}.
                                <span class="block mt-1 text-[10px] opacity-70 uppercase tracking-widest">Everything is fully optimized.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>

        <!-- Bottom Section: AI Insight Glass Component -->
        <div class="mt-12 p-10 rounded-xl bg-gradient-to-br from-primary/10 via-surface-elevated/20 to-secondary/10 backdrop-blur-xl border border-white/5 relative overflow-hidden group shadow-2xl" v-if="stats.total > 0">
            <div class="absolute top-0 right-0 w-96 h-96 bg-primary/10 rounded-full blur-[120px] -mr-48 -mt-48 transition-all group-hover:bg-primary/20"></div>
            <div class="flex flex-col md:flex-row gap-10 items-center relative z-10">
                <div class="w-16 h-16 bg-primary/20 backdrop-blur-md flex items-center justify-center rounded-2xl shrink-0 border border-primary/30 shadow-glow group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-primary text-[32px]">auto_awesome</span>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="font-display text-2xl font-bold text-white mb-3 tracking-tight">Infrastructure Optimization Opportunity</h3>
                    <p class="text-slate-400 text-base max-w-2xl mb-6 leading-relaxed">Our scanner has detected <span class="text-white font-bold">{{ stats.total }}</span> idle resources. Resolving these could recover approximately <span class="text-tertiary font-bold tracking-tight">${{ Math.round(stats.totalSavings) }}/mo</span> in wasted spend without impacting production services.</p>
                    <Link href="/dashboard/ai-insights" class="bg-white/5 hover:bg-white/10 text-white border border-white/10 px-6 py-3 rounded-lg font-mono text-[11px] uppercase tracking-[0.2em] inline-flex items-center gap-3 transition-all active:scale-95 group/btn">
                        View AI Recommendations
                        <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                    </Link>
                </div>
                <div class="bg-surface/60 backdrop-blur-md p-6 rounded-2xl border border-white/10 min-w-[240px] shadow-glass relative group-hover:-translate-y-1 transition-transform">
                    <div class="text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 opacity-70">Impact Score</div>
                    <div class="text-5xl font-display font-bold text-tertiary tracking-tighter mb-4">{{ (stats.totalSavings / 50).toFixed(1) }}<span class="text-sm text-slate-500 ml-2 font-mono opacity-60">/ 10</span></div>
                    <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden flex border border-white/5 shadow-inner">
                        <div class="h-full bg-tertiary shadow-[0_0_10px_rgba(78,222,163,0.5)] transition-all duration-1000" :style="{ width: Math.min(stats.totalSavings / 5, 100) + '%' }"></div>
                    </div>
                    <div class="mt-4 text-[9px] font-mono text-slate-500 uppercase tracking-widest text-center">High Recovery Potential</div>
                </div>
            </div>
        </div>

        <!-- Deletion Confirmation Modal -->
        <ConfirmationModal 
            :show="showDeleteModal"
            title="Authorize Resource Deletion"
            :message="`You are about to permanently delete the ${selectedResource?.resource_type?.replace('_', ' ')}: ${selectedResource?.resource_id}. This will IMMEDIATELY terminate the resource in your AWS account.`"
            confirmText="Terminate Resource"
            confirmWord="DELETE"
            :loading="isDeleting"
            @confirm="handleResolve"
            @cancel="showDeleteModal = false"
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePoll } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import IdleResourceRow from '@/Components/Dashboard/IdleResourceRow.vue';
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';

const props = defineProps({
    idleResources: {
        type: Array,
        required: true
    },
    stats: {
        type: Object,
        required: true
    }
});

// Enable automatic background refreshing every 10 seconds
usePoll(10000);

const scanning = ref(false);
const isDeleting = ref(false);
const activeFilter = ref('All');
const showDeleteModal = ref(false);
const selectedResource = ref(null);

const filteredResources = computed(() => {
    if (activeFilter.value === 'All') return props.idleResources;
    const typeMap = {
        'EBS': 'ebs_volume',
        'Elastic IP': 'elastic_ip',
        'NAT': 'nat_gateway',
        'Snapshots': 'snapshot'
    };
    return props.idleResources.filter(r => r.resource_type === typeMap[activeFilter.value]);
});

const scanNow = () => {
    scanning.value = true;
    router.post('/dashboard/idle/scan', {}, {
        onFinish: () => scanning.value = false
    });
};

const handleIgnore = (id) => {
    router.post(`/dashboard/idle/${id}/ignore`, {}, {
        preserveScroll: true
    });
};

const openDeleteModal = (id) => {
    selectedResource.value = props.idleResources.find(r => r.id === id);
    showDeleteModal.value = true;
};

const handleResolve = () => {
    if (!selectedResource.value) return;
    
    isDeleting.value = true;
    router.post(`/dashboard/idle/${selectedResource.value.id}/resolve`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
        onFinish: () => {
            isDeleting.value = false;
            selectedResource.value = null;
        }
    });
};
</script>

