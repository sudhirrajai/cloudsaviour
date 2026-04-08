<template>
    <DashboardLayout currentPage="idle">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-slate-900 mb-2">Idle Scanner</h1>
                    <p class="text-content-variant font-sans">Detecting unutilized infrastructure and automated cost leakages.</p>
                </div>
                <div class="px-3 py-1 bg-tertiary/10 border border-tertiary/20 rounded-full flex items-center gap-2 group transition-all mt-1 lg:mt-3">
                    <span class="flex h-1.5 w-1.5 rounded-full bg-tertiary animate-pulse shadow-[0_0_8px_#4edea3]"></span>
                    <span class="text-[9px] font-mono text-tertiary font-bold tracking-widest uppercase">Live Sync</span>
                </div>
            </div>

            <!-- Potential Savings Summary Section -->
            <div v-if="stats.totalSavings === 0" class="px-5 py-3 bg-white border border-slate-900 rounded-xl flex items-center gap-4 shadow-ambient transition-all">
                <div class="w-8 h-8 rounded-lg bg-tertiary/10 flex items-center justify-center border border-tertiary/20">
                    <span class="material-symbols-outlined text-tertiary text-sm">trending_down</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest font-bold">Potential Savings</span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span class="text-slate-900 font-mono font-bold text-sm">$0</span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest font-bold">No idle resources detected.</span>
                </div>
            </div>

            <button @click="scanNow" :disabled="scanning" class="bg-slate-900 hover:bg-slate-800 hover:shadow-[0_0_20px_rgba(37,99,235,0.15)] text-white px-6 py-2.5 rounded-lg border border-slate-900 font-mono text-[11px] uppercase tracking-widest font-black flex items-center gap-3 active:scale-95 transition-all duration-300 shadow-md disabled:opacity-50">
                <span class="material-symbols-outlined text-[18px]">{{ scanning ? 'sync_alt' : 'sync' }}</span>
                {{ scanning ? 'Scanning...' : 'Scan now' }}
            </button>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard label="Issues Found" :value="String(stats.total)" sublabel="ACTIVE LEAKS" icon="warning" borderColor="error" />
            <StatCard label="Potential Savings" sublabel="PER MONTHLY CYCLE" icon="trending_down" borderColor="tertiary" />
            <StatCard label="Ignored" :value="String(stats.ignored)" sublabel="FALSE POSITIVES" icon="visibility_off" borderColor="grey" />
            <StatCard label="Scanner Status" value="ACTIVE" sublabel="LIVE REFRESH ENABLED" icon="sensors" borderColor="secondary" />
        </div>

        <!-- Issues Section -->
        <section class="bg-white rounded-lg border border-slate-900 overflow-hidden relative shadow-ambient mb-10">
            <!-- Filter Bar -->
            <div class="bg-slate-50 px-6 py-4 flex flex-wrap items-center gap-4 border-b border-slate-900">
                <span class="text-[11px] font-mono text-slate-500 uppercase tracking-widest mr-2 opacity-70">Filter by</span>
                <button v-for="filter in ['All', 'EBS', 'Elastic IP', 'NAT', 'Snapshots']" :key="filter"
                    class="px-4 py-1.5 rounded-full text-[10px] font-mono border transition-all active:scale-95 font-black uppercase tracking-widest"
                    :class="activeFilter === filter ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-white hover:bg-slate-50 text-slate-500 border-slate-300'"
                    @click="activeFilter = filter"
                >
                    {{ filter }}
                </button>
            </div>
            
            <!-- Table Body -->
            <div class="overflow-x-auto relative z-10 w-full">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead class="bg-slate-50 text-[10px] font-mono text-slate-900 uppercase tracking-[0.2em] border-b border-slate-900">
                        <tr>
                            <th class="px-6 py-4 font-normal">Resource Type</th>
                            <th class="px-6 py-4 font-normal">Identity & Description</th>
                            <th class="px-6 py-4 font-normal">Severity</th>
                            <th class="px-6 py-4 font-normal">Leak Rate</th>
                            <th class="px-6 py-4 font-normal text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
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

        <!-- Bottom Section: AI Insight Section -->
        <div class="mt-12 p-10 rounded-xl bg-slate-50 border border-slate-900 relative overflow-hidden group shadow-ambient" v-if="stats.total > 0">
            <div class="flex flex-col md:flex-row gap-10 items-center relative z-10">
                <div class="w-16 h-16 bg-white border border-slate-900 flex items-center justify-center rounded-2xl shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-primary text-[32px]">auto_awesome</span>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="font-display text-2xl font-bold text-slate-900 mb-3 tracking-tight">Infrastructure Optimization Opportunity</h3>
                    <p class="text-slate-600 text-base max-w-2xl mb-6 leading-relaxed">Our scanner has detected <span class="text-slate-900 font-bold">{{ stats.total }}</span> idle resources. Resolving these could recover approximately <span class="text-tertiary font-bold tracking-tight">${{ Math.round(stats.totalSavings) }}/mo</span> in wasted spend without impacting production services.</p>
                    <Link href="/dashboard/ai-insights" class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-mono text-[11px] uppercase tracking-[0.2em] inline-flex items-center gap-3 transition-all active:scale-95 group/btn shadow-glow">
                        View AI Recommendations
                        <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                    </Link>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-900 min-w-[240px] shadow-sm relative group-hover:-translate-y-1 transition-transform">
                    <div class="text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 opacity-70">Impact Score</div>
                    <div class="text-5xl font-display font-bold text-tertiary tracking-tighter mb-4">{{ (stats.totalSavings / 50).toFixed(1) }}<span class="text-sm text-slate-500 ml-2 font-mono opacity-60">/ 10</span></div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                        <div class="h-full bg-tertiary transition-all duration-1000" :style="{ width: Math.min(stats.totalSavings / 5, 100) + '%' }"></div>
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

