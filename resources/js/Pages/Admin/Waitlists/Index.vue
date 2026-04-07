<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    waitlists: Object,
    fakeCount: Number,
    launchDate: String,
    daysLeft: Number
});

const form = useForm({
    fake_counter: props.fakeCount,
    days_left: props.daysLeft
});

const submitConfig = () => {
    form.post('/admin/waitlists/config', {
        preserveScroll: true,
    });
};

const createAccount = (id) => {
    if (confirm('Are you sure you want to create an account for this user and send them the login credentials?')) {
        router.post(`/admin/waitlists/${id}/account`);
    }
};
</script>

<template>
    <AdminLayout pageTitle="Waitlist Management" currentPage="waitlist">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Days Countdown -->
                <div class="group bg-surface border border-white/5 rounded-2xl p-8 shadow-glow hover:shadow-primary/10 hover:border-primary/20 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-all"></div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shadow-glow shadow-primary/20">
                            <span class="material-symbols-outlined text-2xl">hourglass_top</span>
                        </div>
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest bg-white/5 px-2 py-1 rounded">Launch Status</span>
                    </div>
                    <div class="text-4xl font-display font-bold text-white mb-2 tabular-nums">{{ daysLeft }} Days</div>
                    <div class="text-sm font-medium text-slate-400 flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs">event</span>
                        {{ launchDate || 'Not Set' }}
                    </div>
                </div>

                <!-- Real Conversions -->
                <div class="group bg-surface border border-white/5 rounded-2xl p-8 shadow-glow hover:shadow-secondary/10 hover:border-secondary/20 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-secondary/5 rounded-full blur-2xl group-hover:bg-secondary/10 transition-all"></div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-xl bg-secondary/10 border border-secondary/20 flex items-center justify-center text-secondary shadow-glow shadow-secondary/20">
                            <span class="material-symbols-outlined text-2xl">group</span>
                        </div>
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest bg-white/5 px-2 py-1 rounded">Organic Growth</span>
                    </div>
                    <div class="text-4xl font-display font-bold text-white mb-2 tabular-nums">{{ waitlists.total }}</div>
                    <div class="text-sm font-medium text-slate-400 flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs">verified</span>
                        Verified Subscriptions
                    </div>
                </div>

                <!-- Fake Counter -->
                <div class="group bg-surface border border-white/5 rounded-2xl p-8 shadow-glow hover:shadow-tertiary/10 hover:border-tertiary/20 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-tertiary/5 rounded-full blur-2xl group-hover:bg-tertiary/10 transition-all"></div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-12 h-12 rounded-xl bg-tertiary/10 border border-tertiary/20 flex items-center justify-center text-tertiary shadow-glow shadow-tertiary/20">
                            <span class="material-symbols-outlined text-2xl">add_chart</span>
                        </div>
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest bg-white/5 px-2 py-1 rounded">Social Proof</span>
                    </div>
                    <div class="text-4xl font-display font-bold text-white mb-2 tabular-nums">{{ fakeCount }}</div>
                    <div class="text-sm font-medium text-slate-400 flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs">auto_graph</span>
                        Display Buffer Active
                    </div>
                </div>
            </div>

            <!-- Settings & Configuration -->
            <div class="bg-surface border border-white/5 rounded-2xl overflow-hidden shadow-2xl relative">
                <div class="px-8 py-6 border-b border-white/5 bg-white/5 backdrop-blur-md flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">settings_applications</span>
                    <h2 class="font-display font-bold text-lg text-white tracking-tight">Deployment Configuration</h2>
                </div>
                <form @submit.prevent="submitConfig" class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-slate-300 uppercase tracking-tighter">Social Proof Offset</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-500">add</span>
                                <input v-model="form.fake_counter" type="number" 
                                    class="w-full bg-black/40 border border-white/10 rounded-xl pl-12 pr-4 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all placeholder:text-slate-700 shadow-inner">
                            </div>
                            <p class="text-[11px] font-mono text-slate-500 leading-relaxed uppercase tracking-widest opacity-60">Number of simulated users added to real count on the public landing page</p>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-slate-300 uppercase tracking-tighter">Countdown Adjustment</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-500">schedule</span>
                                <input v-model="form.days_left" type="number" 
                                    class="w-full bg-black/40 border border-white/10 rounded-xl pl-12 pr-4 py-3.5 text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition-all placeholder:text-slate-700 shadow-inner">
                            </div>
                            <p class="text-[11px] font-mono text-slate-500 leading-relaxed uppercase tracking-widest opacity-60">Reset countdown to specific days from today. Current: {{ daysLeft }} days</p>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" 
                            class="bg-primary hover:bg-primary-hover text-white px-8 py-3.5 rounded-xl font-bold transition-all shadow-glow shadow-primary/20 flex items-center gap-3 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed group"
                            :disabled="form.processing">
                            <span class="material-symbols-outlined group-hover:rotate-12 transition-transform">rocket_launch</span>
                            Apply Configuration
                        </button>
                    </div>
                </form>
            </div>

            <!-- Waitlist Registry Table -->
            <div class="bg-surface border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
                <div class="px-8 py-6 border-b border-white/5 bg-white/5 backdrop-blur-md flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-secondary">database</span>
                        <h2 class="font-display font-bold text-lg text-white tracking-tight">Subscriber Registry</h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-[11px] font-mono text-slate-500 border border-white/5 bg-black/20 px-3 py-1 rounded-full uppercase tracking-widest tabular-nums">Shard: Waitlist_01</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fixed">
                        <thead>
                            <tr class="bg-black/20 border-b border-white/5">
                                <th class="w-2/5 px-8 py-5 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em]">Credential Endpoint</th>
                                <th class="w-1/5 px-8 py-5 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em]">Registration Node</th>
                                <th class="w-1/5 px-8 py-5 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em]">Vector State</th>
                                <th class="w-1/5 px-8 py-5 text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="item in waitlists.data" :key="item.id" class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 font-mono text-xs">
                                            @
                                        </div>
                                        <div class="font-medium text-white group-hover:text-primary transition-colors truncate">{{ item.email }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 tabular-nums">
                                    <div class="text-sm text-slate-400 capitalize">{{ new Date(item.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</div>
                                    <div class="text-[10px] text-slate-600 font-mono mt-0.5">{{ new Date(item.created_at).toLocaleTimeString() }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div v-if="item.is_account_created" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-[10px] font-bold uppercase tracking-tighter border border-green-500/20 shadow-glow shadow-green-500/5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 shadow-glow shadow-green-400/50"></span>
                                        Onboarded
                                    </div>
                                    <div v-else class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 text-[10px] font-bold uppercase tracking-tighter border border-amber-500/20 shadow-glow shadow-amber-500/5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                        Queueing
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <button 
                                        @click="createAccount(item.id)"
                                        v-if="!item.is_account_created"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-primary/20 hover:text-primary hover:border-primary/40 border border-white/10 rounded-xl text-xs font-bold text-white transition-all active:scale-95 group/btn shadow-sm"
                                    >
                                        <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-0.5 transition-transform">person_add</span>
                                        Onboard User
                                    </button>
                                    <div v-else class="text-[10px] text-slate-600 font-mono italic">Account provisioned</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Placeholder -->
                <div v-if="waitlists.links && waitlists.links.length > 3" class="px-8 py-5 bg-black/10 flex items-center justify-center border-t border-white/5">
                    <div class="flex items-center gap-2">
                        <Link v-for="(link, i) in waitlists.links" :key="i"
                            :href="link.url || '#'"
                            class="px-3 py-1.5 rounded-lg text-xs font-mono transition-all border"
                            :class="[
                                link.active ? 'bg-primary text-white border-primary shadow-glow shadow-primary/20' : 'bg-white/5 text-slate-400 border-white/5 hover:bg-white/10',
                                !link.url ? 'opacity-30 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.shadow-glow {
    filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.05));
}

@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in {
    animation: fade-in-up 0.5s ease-out forwards;
}
</style>
