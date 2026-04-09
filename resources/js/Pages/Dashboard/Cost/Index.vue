<template>
    <DashboardLayout currentPage="cost">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-slate-900 mb-2">Cost Analytics</h1>
                <p class="text-content-variant font-sans">Monitor and optimize your AWS spending across all services.</p>
            </div>
            <div class="flex items-center gap-2">
                <button v-for="period in periods" :key="period.value"
                    :class="[
                        'px-4 py-1.5 rounded-full text-[10px] font-mono border transition-all font-black uppercase tracking-widest',
                        activePeriod === period.value
                            ? 'bg-slate-900 text-white border-slate-900 shadow-md'
                            : 'bg-white hover:bg-slate-50 text-slate-500 border-slate-300'
                    ]"
                    @click="activePeriod = period.value"
                >
                    {{ period.label }}
                </button>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard :label="activePeriod === 'last_30_days' ? 'Total Spend' : (activePeriod === 'last_month' ? 'Last Month' : 'This Month')"  :sublabel="currentMonth" icon="attach_money" borderColor="slate-900" valueColor="slate-900">
                <span class="font-mono">${{ computedData.total.toLocaleString() }}</span>
            </StatCard>
            <StatCard label="Forecast" sublabel="MONTH-END ESTIMATE" icon="trending_up" borderColor="slate-600">
                <span class="font-mono text-slate-600">${{ (computedData.total * 1.1).toLocaleString(undefined, {maximumFractionDigits: 0}) }}</span>
            </StatCard>
            <StatCard label="Daily Average" sublabel="BASED ON 30 DAYS" icon="analytics" borderColor="slate-400">
                <span class="font-mono text-slate-500">${{ (computedData.total / 30).toLocaleString(undefined, {maximumFractionDigits: 2}) }}</span>
            </StatCard>
            <StatCard label="Top Service" :sublabel="topServiceLabel" icon="cloud" borderColor="slate-900">
                <span class="text-slate-900">{{ topServiceName }}</span>
            </StatCard>
        </div>

        <!-- Cost Chart -->
        <section class="bg-white rounded-lg border border-slate-900 p-8 mb-10 relative overflow-hidden shadow-ambient transition-all">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <h2 class="text-xl font-display font-bold text-slate-900">Service Cost Share</h2>
                <div class="flex flex-wrap items-center gap-4">
                    <span v-for="service in computedData.services" :key="service.name" class="flex items-center gap-2 px-2.5 py-1 bg-slate-50 rounded-full border border-slate-200">
                        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: getServiceColor(service.name) }"></span>
                        <span class="text-[10px] font-mono text-slate-600 uppercase tracking-wider">{{ service.name }}</span>
                    </span>
                </div>
            </div>
            <!-- Progress Bar Style Chart -->
            <div class="flex h-14 w-full bg-slate-100 rounded-lg overflow-hidden mb-8 border border-slate-200">
                <div v-for="service in computedData.services" :key="service.name" 
                    class="h-full transition-all duration-700 ease-out hover:brightness-110 cursor-pointer"
                    :style="{ width: service.pct + '%', backgroundColor: getServiceColor(service.name) }"
                    :title="`${service.name}: ${service.pct}%`"
                ></div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <div v-for="service in computedData.services.slice(0, 10)" :key="service.name" class="p-4 bg-white rounded-lg border border-slate-200 hover:border-slate-900 transition-all flex flex-col justify-between overflow-hidden shadow-sm">
                    <div class="text-[9px] font-mono text-slate-500 uppercase tracking-widest mb-2 opacity-70 line-clamp-2 h-6" :title="service.name">{{ service.name }}</div>
                    <div class="text-lg font-mono font-bold text-slate-900">${{ service.amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                </div>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#0f172a 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>

        <!-- Service Breakdown Table -->
        <section class="bg-white rounded-lg border border-slate-900 overflow-hidden relative shadow-ambient text-slate-900">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-900">
                <span class="text-sm font-semibold text-slate-900 tracking-wide">Service Breakdown</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead class="bg-slate-50 text-[10px] font-mono text-slate-900 uppercase tracking-[0.2em] border-b border-slate-900">
                        <tr>
                            <th class="px-6 py-4 font-normal">Service</th>
                            <th class="px-6 py-4 font-normal">{{ activePeriod === 'this_month' ? 'This Month' : (activePeriod === 'last_month' ? 'Last Month' : 'Spend') }}</th>
                            <th class="px-6 py-4 font-normal" v-if="activePeriod === 'this_month'">Last Month</th>
                            <th class="px-6 py-4 font-normal" v-if="activePeriod === 'this_month'">Change</th>
                            <th class="px-6 py-4 font-normal text-right">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="service in computedData.services" :key="service.name" class="group hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-3 h-3 rounded-sm shadow-sm" :style="{ backgroundColor: getServiceColor(service.name) }"></div>
                                    <span class="text-sm font-semibold text-slate-900 group-hover:text-slate-900 transition-colors">{{ service.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-sm text-slate-900">${{ service.amount.toFixed(2) }}</span>
                            </td>
                            <td class="px-6 py-5" v-if="activePeriod === 'this_month'">
                                <span class="font-mono text-sm text-content-variant opacity-70">${{ service.previous.toFixed(2) }}</span>
                            </td>
                            <td class="px-6 py-5" v-if="activePeriod === 'this_month'">
                                <div :class="[
                                    'font-mono text-xs flex items-center gap-1.5 px-2 py-1 rounded w-fit',
                                    service.change > 0 ? 'bg-slate-900/10 text-slate-900 font-bold' : service.change < 0 ? 'bg-slate-500/10 text-slate-500' : 'bg-white/5 text-content-variant'
                                ]">
                                    <span v-if="service.change > 0" class="material-symbols-outlined text-[14px]">trending_up</span>
                                    <span v-else-if="service.change < 0" class="material-symbols-outlined text-[14px]">trending_down</span>
                                    {{ service.change > 0 ? '+' : '' }}${{ Math.abs(service.change).toFixed(2) }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-4">
                                    <div class="w-24 h-1.5 bg-white/5 rounded-full overflow-hidden border border-white/5 shadow-inner">
                                        <div class="h-full rounded-full transition-all duration-1000" :style="{ width: service.pct + '%', backgroundColor: getServiceColor(service.name) }"></div>
                                    </div>
                                    <span class="font-mono text-[11px] text-content-variant w-8 text-right font-bold">{{ service.pct }}%</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="computedData.services.length === 0">
                            <td :colspan="activePeriod === 'this_month' ? 5 : 3" class="px-6 py-12 text-center text-content-variant font-mono text-xs italic">No cost data available for this month.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#0f172a 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';

const props = defineProps({
    services: {
        type: Array,
        required: true
    },
    totalThisMonth: {
        type: Number,
        required: true
    },
    history: {
        type: Array,
        default: () => []
    }
});

const activePeriod = ref('this_month');
const periods = [
    { label: 'This Month', value: 'this_month' },
    { label: 'Last Month', value: 'last_month' },
    { label: 'Last 30 Days', value: 'last_30_days' },
];

const computedData = computed(() => {
    let total = 0;
    let servicesList = [];

    if (activePeriod.value === 'this_month') {
        total = props.totalThisMonth;
        servicesList = props.services.map(s => ({
            name: s.name,
            amount: s.thisMonth,
            previous: s.lastMonth,
            change: s.change,
            pct: s.pct
        }));
    } else if (activePeriod.value === 'last_month') {
        total = props.services.reduce((sum, s) => sum + s.lastMonth, 0);
        servicesList = props.services.map(s => ({
            name: s.name,
            amount: s.lastMonth,
            previous: 0,
            change: 0,
            pct: total > 0 ? Number(((s.lastMonth / total) * 100).toFixed(1)) : 0
        }));
    } else if (activePeriod.value === 'last_30_days') {
        let last30Services = {};
        props.history.forEach(day => {
            total += day.total;
            for (const [name, amt] of Object.entries(day.services)) {
                if (!last30Services[name]) last30Services[name] = 0;
                last30Services[name] += amt;
            }
        });
        
        for (const [name, amount] of Object.entries(last30Services)) {
            servicesList.push({
                name,
                amount,
                previous: 0,
                change: 0,
                pct: total > 0 ? Number(((amount / total) * 100).toFixed(1)) : 0
            });
        }
    }

    servicesList.sort((a, b) => b.amount - a.amount);
    return { total, services: servicesList };
});

const currentMonth = computed(() => {
    if (activePeriod.value === 'last_month') {
        const d = new Date();
        d.setMonth(d.getMonth() - 1);
        return d.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();
    } else if (activePeriod.value === 'last_30_days') {
        return 'TRAILING 30 DAYS';
    }
    return new Date().toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();
});

const topServiceName = computed(() => {
    if (computedData.value.services.length === 0) return 'None';
    return computedData.value.services[0].name;
});

const topServiceLabel = computed(() => {
    if (computedData.value.services.length === 0) return '$0 (0%)';
    const top = computedData.value.services[0];
    return `$${top.amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${top.pct}%)`;
});

const getServiceColor = (name) => {
    const colors = {
        'Amazon Elastic Compute Cloud - Compute': '#0f172a',
        'Amazon Relational Database Service': '#334155',
        'Amazon Simple Storage Service': '#475569',
        'AmazonCloudWatch': '#64748b',
        'AWS Lambda': '#94a3b8',
        'Elastic Load Balancing': '#cbd5e1',
        'Tax': '#e2e8f0',
        'AWS Backup': '#1e293b',
        'Amazon Route 53': '#3d4763',
    };
    
    // Fallback logic for shorthand names or partial matches
    if (colors[name]) return colors[name];
    if (name.includes('Compute')) return colors['Amazon Elastic Compute Cloud - Compute'];
    if (name.includes('Database')) return colors['Amazon Relational Database Service'];
    
    return '#94a3b8';
};
</script>


