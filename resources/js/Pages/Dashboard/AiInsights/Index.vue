<template>
    <DashboardLayout currentPage="ai-insights">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-white mb-2">AI Insights</h1>
                <p class="text-content-variant font-sans">AI-powered recommendations to optimize your AWS infrastructure and reduce costs.</p>
            </div>
            <button 
                @click="refreshAnalysis"
                :disabled="isRefreshing"
                class="bg-primary text-white px-6 py-2.5 rounded-sm font-mono text-[11px] uppercase tracking-wider font-bold flex items-center gap-3 hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(59,130,246,0.2)] disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span :class="['material-symbols-outlined text-sm', isRefreshing ? 'animate-spin' : '']">
                    {{ isRefreshing ? 'sync' : 'auto_awesome' }}
                </span>
                {{ isRefreshing ? 'Analyzing...' : 'Refresh Analysis' }}
            </button>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard label="Recommendations" :value="String(pendingCount)" sublabel="PENDING REVIEW" icon="auto_awesome" borderColor="primary" valueColor="white" />
            <StatCard label="Potential Savings" sublabel="IF ALL APPLIED" icon="savings" borderColor="tertiary">
                <span class="font-mono text-tertiary">${{ Math.round(potentialSavings) }}/mo</span>
            </StatCard>
            <StatCard label="Applied" :value="String(appliedCount)" sublabel="ALL TIME" icon="check_circle" borderColor="secondary" valueColor="white" />
            <StatCard label="Dismissed" :value="String(dismissedCount)" sublabel="SKIPPED" icon="block" borderColor="grey" />
        </div>

        <!-- Recommendation Cards -->
        <div class="space-y-6">
            <div v-for="rec in recommendations" :key="rec.id"
                 :class="[
                     'bg-surface/50 backdrop-blur-md rounded-xl border border-white/5 p-6 relative overflow-hidden transition-all group hover:bg-surface-elevated/40 shadow-sm',
                     rec.status !== 'pending' ? 'opacity-60 grayscale-[0.2]' : 'shadow-glass shadow-primary/5 hover:-translate-y-0.5'
                 ]">
                <div class="flex flex-col lg:flex-row gap-8 relative z-10">
                    <!-- Icon -->
                    <div :class="[
                        'w-12 h-12 flex items-center justify-center rounded-xl shrink-0 border border-white/10 shadow-glass group-hover:scale-110 transition-transform duration-300',
                        actionIconBg(rec.action_type)
                    ]">
                        <span :class="['material-symbols-outlined text-[24px]', actionIconColor(rec.action_type)]">{{ actionIcon(rec.action_type) }}</span>
                    </div>
 
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h3 :class="['font-display text-lg font-bold tracking-tight', rec.status === 'dismissed' ? 'line-through text-slate-500' : 'text-white group-hover:text-primary transition-colors']">{{ rec.title }}</h3>
                            <span :class="[
                                'inline-flex items-center px-2 py-0.5 rounded text-[9px] font-mono border uppercase tracking-[0.2em] font-bold',
                                statusBadge(rec.status)
                            ]">
                                {{ rec.status }}
                            </span>
                        </div>
                        <p class="text-slate-400 text-sm mb-4 leading-relaxed max-w-3xl">{{ rec.description }}</p>
                        <div class="flex flex-wrap items-center gap-6 text-[11px] font-mono text-slate-500">
                            <div class="flex items-center gap-2 px-2.5 py-1 bg-white/5 rounded-md border border-white/5">
                                <span class="material-symbols-outlined text-[14px] text-primary">data_object</span>
                                <span><span class="text-white opacity-60">RESOURCE</span>: {{ rec.resource_id }}</span>
                            </div>
                            <div class="flex items-center gap-2 px-2.5 py-1 bg-white/5 rounded-md border border-white/5">
                                <span class="material-symbols-outlined text-[14px] text-white/40">settings_applications</span>
                                <span><span class="text-white opacity-60">ACTION</span>: <span class="text-white">{{ rec.action_type }}</span></span>
                            </div>
                        </div>
 
                        <!-- Actions (only for pending) -->
                        <div v-if="rec.status === 'pending'" class="flex items-center gap-3 mt-6">
                            <button @click="applyRecommendation(rec.id)"
                                class="bg-primary/20 hover:bg-primary/30 text-primary border border-primary/30 px-5 py-1.5 rounded-lg font-mono text-[10px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow shadow-primary/10">
                                Apply Implementation
                            </button>
                            <button @click="dismissRecommendation(rec.id)"
                                class="bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white border border-white/10 px-5 py-1.5 rounded-lg font-mono text-[10px] uppercase tracking-widest transition-all active:scale-95">
                                Dismiss
                            </button>
                        </div>
                    </div>
 
                    <!-- Right side: Confidence + Savings -->
                    <div class="flex lg:flex-col gap-3 lg:gap-3 shrink-0 lg:items-end">
                        <div class="bg-canvas/60 backdrop-blur-md p-3 rounded-xl border border-white/10 min-w-[130px] shadow-glass group-hover:border-primary/20 transition-colors">
                            <div class="text-[8px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-1.5 opacity-70">Confidence</div>
                            <div class="text-xl font-display font-bold text-white tracking-tighter">{{ rec.confidence_score }}%</div>
                            <div class="w-full h-1 bg-white/5 mt-2 rounded-full overflow-hidden border border-white/5">
                                <div class="h-full bg-primary rounded-full shadow-[0_0_8px_rgba(59,130,246,0.5)] transition-all duration-1000" :style="{ width: rec.confidence_score + '%' }"></div>
                            </div>
                        </div>
                        <div class="bg-canvas/60 backdrop-blur-md p-3 rounded-xl border border-white/10 min-w-[130px] shadow-glass group-hover:border-tertiary/20 transition-colors">
                            <div class="text-[8px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-1.5 opacity-70">Savings</div>
                            <div class="text-xl font-display font-bold text-tertiary tracking-tighter">${{ rec.estimated_monthly_saving }}<span class="text-[10px] text-slate-500 ml-1 font-mono opacity-60">/mo</span></div>
                        </div>
                    </div>
                </div>
                <!-- Background decoration -->
                <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
                <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-primary/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-primary/10 transition-all"></div>
            </div>
            <div v-if="recommendations.length === 0" class="p-20 text-center bg-surface/30 border border-white/5 rounded-xl backdrop-blur-md">
                <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/5">
                    <span class="material-symbols-outlined text-4xl text-slate-600">auto_awesome</span>
                </div>
                <p class="text-slate-400 font-mono text-xs uppercase tracking-widest opacity-70">No AI insights available at this moment. Infrastructure analysis in progress.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';

const props = defineProps({
    recommendations: {
        type: Array,
        required: true
    }
});

const isRefreshing = ref(false);

const pendingCount = computed(() => props.recommendations.filter(r => r.status === 'pending').length);
const appliedCount = computed(() => props.recommendations.filter(r => r.status === 'applied').length);
const dismissedCount = computed(() => props.recommendations.filter(r => r.status === 'dismissed').length);
const potentialSavings = computed(() => props.recommendations.filter(r => r.status === 'pending').reduce((sum, r) => sum + (parseFloat(r.estimated_monthly_saving) || 0), 0));

const actionIcon = (type) => ({ resize: 'straighten', delete: 'delete', schedule: 'schedule', lifecycle: 'autorenew' }[type] || 'auto_awesome');
const actionIconBg = (type) => ({ resize: 'bg-primary/10', delete: 'bg-error/10', schedule: 'bg-secondary/10', lifecycle: 'bg-amber-500/10' }[type] || 'bg-primary/10');
const actionIconColor = (type) => ({ resize: 'text-primary', delete: 'text-error', schedule: 'text-secondary', lifecycle: 'text-amber-500' }[type] || 'text-primary');
const statusBadge = (status) => ({ pending: 'bg-primary/10 text-primary border-primary/20', applied: 'bg-tertiary/10 text-tertiary border-tertiary/20', dismissed: 'bg-surface-elevated text-content-variant border-border-ghost' }[status]);

const refreshAnalysis = () => {
    isRefreshing.value = true;
    router.post('/dashboard/ai-insights/refresh', {}, {
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

const applyRecommendation = (id) => {
    router.post(`/dashboard/ai-insights/${id}/apply`, {}, {
        preserveScroll: true
    });
};

const dismissRecommendation = (id) => {
    router.post(`/dashboard/ai-insights/${id}/dismiss`, {}, {
        preserveScroll: true
    });
};
</script>

