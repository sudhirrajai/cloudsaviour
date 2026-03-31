<template>
    <DashboardLayout currentPage="servers">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-white mb-2">Servers</h1>
                <div class="flex items-center gap-3">
                    <p class="text-content-variant font-sans">Manage and monitor your EC2 and RDS instances.</p>
                    <div class="px-2.5 py-0.5 bg-tertiary/10 border border-tertiary/20 rounded-full flex items-center gap-2 group transition-all">
                        <span class="flex h-1 w-1 rounded-full bg-tertiary animate-pulse shadow-[0_0_8px_#4edea3]"></span>
                        <span class="text-[8px] font-mono text-tertiary font-bold tracking-widest uppercase">Live Sync</span>
                    </div>
                </div>
                <div class="mt-1 flex items-center gap-2">
                    <p class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Last synced: {{ lastSyncedAt }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div v-if="syncing" class="flex items-center gap-2 px-3 py-1.5 bg-white/5 rounded-lg border border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse shadow-glow shadow-primary"></span>
                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Syncing AWS...</span>
                </div>
                <button @click="syncNow" :disabled="syncing" class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-md font-mono text-[11px] uppercase tracking-wider font-bold flex items-center gap-3 active:scale-95 transition-all shadow-[0_0_20px_rgba(59,130,246,0.3)] disabled:opacity-50">
                    <span :class="['material-symbols-outlined text-sm', syncing ? 'animate-spin' : '']">sync</span>
                    {{ syncing ? 'Syncing...' : 'Sync now' }}
                </button>
            </div>
        </header>

        <!-- Stats Grid ... -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard label="Total Instances" :value="String(ec2Instances.length + rdsInstances.length)" sublabel="EC2 + RDS" icon="dns" borderColor="primary" valueColor="white" />
            <StatCard label="Running" :value="String(runningCount)" sublabel="ACTIVE WORKLOADS" icon="play_circle" borderColor="tertiary" valueColor="tertiary" />
            <StatCard label="Stopped" :value="String(stoppedCount)" sublabel="AVAILABLE TO START" icon="stop_circle" borderColor="amber" valueColor="amber" />
            <StatCard label="Month-to-Date Cost" sublabel="ACTUAL AWS CHARGES" icon="payments" borderColor="secondary" valueColor="white">
                <span class="font-mono">${{ Number(monthlyCost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </StatCard>
        </div>

        <!-- EC2 Instances -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 overflow-hidden relative mb-10">
            <div class="bg-surface-elevated/80 px-6 py-4 flex items-center justify-between border-b border-white/5">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-[22px]">cloud</span>
                    <span class="text-sm font-semibold text-white tracking-wide">EC2 Instances</span>
                    <span class="bg-primary/20 text-primary text-[10px] font-mono px-2 py-0.5 rounded-full border border-primary/30">{{ ec2Instances.length }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead class="bg-canvas text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-normal">Instance</th>
                            <th class="px-6 py-4 font-normal">Type</th>
                            <th class="px-6 py-4 font-normal">State</th>
                            <th class="px-6 py-4 font-normal">Public IP</th>
                            <th class="px-6 py-4 font-normal">Region</th>
                            <th class="px-6 py-4 font-normal text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="instance in ec2Instances" :key="instance.id" class="group hover:bg-white/[0.03] transition-colors">
                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-white group-hover:text-primary transition-colors">{{ instance.name || 'Unnamed Instance' }}</div>
                                <div class="font-mono text-[10px] text-content-variant mt-1 opacity-70">{{ instance.instance_id }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-xs text-content-variant bg-white/5 px-2 py-1 rounded">{{ instance.instance_type }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono border uppercase tracking-wider',
                                    instance.state === 'running'
                                        ? 'bg-tertiary/10 text-tertiary border-tertiary/20'
                                        : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                ]">
                                    {{ instance.state }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-xs text-content-variant">{{ instance.public_ip || '—' }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-[10px] text-content-variant uppercase opacity-70">{{ instance.availability_zone }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="refreshInstance(instance, 'ec2')" 
                                        class="p-1.5 rounded-md hover:bg-white/10 text-content-variant hover:text-white transition-all active:scale-95" 
                                        title="Refresh Status">
                                        <span class="material-symbols-outlined text-[18px]">refresh</span>
                                    </button>
                                    <button v-if="instance.state === 'running'"
                                        @click="toggleInstance(instance, 'ec2', 'stop')"
                                        class="px-4 py-1.5 rounded-md bg-error/10 hover:bg-error/20 border border-error/20 text-error text-[10px] font-mono uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all cursor-pointer">
                                        Stop
                                    </button>
                                    <button v-else-if="instance.state === 'stopped'"
                                        @click="toggleInstance(instance, 'ec2', 'start')"
                                        class="px-4 py-1.5 rounded-md bg-tertiary/10 hover:bg-tertiary/20 border border-tertiary/20 text-tertiary text-[10px] font-mono uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all cursor-pointer">
                                        Start
                                    </button>
                                    <span v-else class="text-[10px] font-mono text-content-variant uppercase italic opacity-60">Processing...</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="ec2Instances.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-content-variant font-mono text-xs italic">No EC2 instances found. Click "Sync now" to fetch from AWS.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- RDS Instances -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 overflow-hidden relative">
            <div class="bg-surface-elevated/80 px-6 py-4 flex items-center justify-between border-b border-white/5">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-secondary text-[22px]">database</span>
                    <span class="text-sm font-semibold text-white tracking-wide">RDS Instances</span>
                    <span class="bg-secondary/20 text-secondary text-[10px] font-mono px-2 py-0.5 rounded-full border border-secondary/30">{{ rdsInstances.length }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead class="bg-canvas text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-normal">Database</th>
                            <th class="px-6 py-4 font-normal">Engine</th>
                            <th class="px-6 py-4 font-normal">Class</th>
                            <th class="px-6 py-4 font-normal">Status</th>
                            <th class="px-6 py-4 font-normal">Endpoint</th>
                            <th class="px-6 py-4 font-normal text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="db in rdsInstances" :key="db.id" class="group hover:bg-white/[0.03] transition-colors">
                            <td class="px-6 py-5">
                                <div class="text-sm font-semibold text-white group-hover:text-secondary transition-colors">{{ db.db_instance_id }}</div>
                                <div class="font-mono text-[10px] text-content-variant mt-1 opacity-70">{{ db.db_name || 'DB Name: —' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-xs text-content-variant bg-white/5 px-2 py-1 rounded truncate block max-w-[150px]">{{ db.db_engine }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-[10px] text-content-variant uppercase opacity-70">{{ db.instance_class }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono border uppercase tracking-wider',
                                    db.status === 'available'
                                        ? 'bg-tertiary/10 text-tertiary border-tertiary/20'
                                        : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                ]">
                                    {{ db.status }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-[10px] text-content-variant max-w-[180px] block truncate opacity-70" :title="db.endpoint">{{ db.endpoint || '—' }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="refreshInstance(db, 'rds')" 
                                        class="p-1.5 rounded-md hover:bg-white/10 text-content-variant hover:text-white transition-all active:scale-95" 
                                        title="Refresh Status">
                                        <span class="material-symbols-outlined text-[18px]">refresh</span>
                                    </button>
                                    <button v-if="db.status === 'available'"
                                        @click="toggleInstance(db, 'rds', 'stop')"
                                        class="px-4 py-1.5 rounded-md bg-error/10 hover:bg-error/20 border border-error/20 text-error text-[10px] font-mono uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all cursor-pointer">
                                        Stop
                                    </button>
                                    <button v-else-if="db.status === 'stopped'"
                                        @click="toggleInstance(db, 'rds', 'start')"
                                        class="px-4 py-1.5 rounded-md bg-tertiary/10 hover:bg-tertiary/20 border border-tertiary/20 text-tertiary text-[10px] font-mono uppercase tracking-wider hover:brightness-110 active:scale-95 transition-all cursor-pointer">
                                        Start
                                    </button>
                                    <span v-else class="text-[10px] font-mono text-content-variant uppercase italic opacity-60">Processing...</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="rdsInstances.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-content-variant font-mono text-xs italic">No RDS instances found. Click "Sync now" to fetch from AWS.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        
        <div class="fixed inset-0 pointer-events-none opacity-[0.03] z-[-1]" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePoll } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';

const props = defineProps({
    ec2Instances: {
        type: Array,
        required: true
    },
    rdsInstances: {
        type: Array,
        required: true
    },
    monthlyCost: {
        type: Number,
        default: 0
    },
    lastSyncedAt: {
        type: String,
        default: 'Never'
    },
    hasAwsCredentials: {
        type: Boolean,
        default: false
    }
});

// Enable automatic background refreshing every 10 seconds
usePoll(10000);

const syncing = ref(false);

onMounted(() => {
    // Auto-sync if it's never been synced, has credentials, and not already syncing
    if (props.hasAwsCredentials && props.lastSyncedAt === 'Never') {
        syncNow();
    }
});

const runningCount = computed(() => 
    props.ec2Instances.filter(i => i.state === 'running').length + 
    props.rdsInstances.filter(d => d.status === 'available').length
);
const stoppedCount = computed(() => 
    props.ec2Instances.filter(i => i.state === 'stopped').length + 
    props.rdsInstances.filter(d => d.status === 'stopped').length
);

const syncNow = () => {
    syncing.value = true;
    router.post('/dashboard/servers/sync', {}, {
        onFinish: () => syncing.value = false
    });
};

const refreshInstance = (instance, type) => {
    const id = type === 'ec2' ? instance.instance_id : instance.db_instance_id;
    router.post(`/dashboard/servers/${id}/refresh`, { type }, {
        preserveScroll: true
    });
};

const toggleInstance = (instance, type, action) => {
    const id = type === 'ec2' ? instance.instance_id : instance.db_instance_id;
    router.post(`/dashboard/servers/${id}/${action}`, {
        type: type,
        name: type === 'ec2' ? instance.name : instance.db_instance_id
    }, {
        preserveScroll: true
    });
};
</script>

