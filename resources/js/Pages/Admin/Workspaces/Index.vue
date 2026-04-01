<template>
    <AdminLayout currentPage="workspaces" pageTitle="Workspaces & Subscriptions">
        <div class="space-y-6">
            <!-- Header Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="info in summaryInfo" :key="info.label" class="bg-surface border border-white/5 p-5 rounded-2xl shadow-glass">
                    <div class="flex items-center gap-3">
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shadow-lg', info.color]">
                            <span class="material-symbols-outlined text-white text-[20px]">{{ info.icon }}</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-mono text-slate-500 uppercase tracking-widest">{{ info.label }}</p>
                            <h3 class="text-xl font-display font-bold text-white">{{ info.value }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Search -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative max-w-sm w-full group">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-primary transition-colors">search</span>
                    <input 
                        type="text" 
                        placeholder="Search by workspace name or slug..." 
                        v-model="search"
                        class="w-full bg-surface border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-glass"
                    />
                </div>
            </div>

            <!-- Workspaces Table -->
            <div class="bg-surface border border-white/5 rounded-2xl shadow-ambient overflow-hidden font-sans">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-black/20 text-[10px] uppercase tracking-widest text-slate-500 font-mono">
                            <tr>
                                <th class="px-6 py-5">Workspace</th>
                                <th class="px-6 py-5">Owner</th>
                                <th class="px-6 py-5">Subscription</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="workspace in workspaces.data" :key="workspace.id" class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                                            <span class="material-symbols-outlined">domain</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-white group-hover:text-primary transition-colors">{{ workspace.name }}</div>
                                            <div class="text-[10px] font-mono text-slate-500 italic">/{{ workspace.slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-white">{{ workspace.owner_name }}</div>
                                        <div class="text-[10px] font-mono text-slate-500 lowercase">{{ workspace.owner_email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-bold rounded-lg border border-primary/20 uppercase tracking-widest">
                                            {{ workspace.plan_name }}
                                        </div>
                                        <button @click="openUpgradeModal(workspace)" class="text-slate-500 hover:text-primary transition-colors hover:scale-110 active:scale-95">
                                            <span class="material-symbols-outlined text-[18px]">upgrade</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-2 py-1 text-[10px] font-bold rounded-full border uppercase tracking-tighter',
                                        workspace.is_active ? 'bg-success/10 text-success border-success/20' : 'bg-error/10 text-error border-error/20'
                                    ]">
                                        {{ workspace.is_active ? 'Active' : 'Suspended' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            @click="toggleStatus(workspace)"
                                            :title="workspace.is_active ? 'Deactivate Workspace' : 'Activate Workspace'"
                                            class="p-2 rounded-lg bg-surface-elevated border border-white/5 text-slate-400 hover:text-white hover:bg-white/10 transition-all active:scale-95"
                                        >
                                            <span class="material-symbols-outlined text-[18px]">{{ workspace.is_active ? 'block' : 'undo' }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="workspaces.data.length === 0" class="py-20 text-center">
                    <span class="material-symbols-outlined text-slate-500 text-4xl mb-2">search_off</span>
                    <p class="text-slate-400">No workspaces found matching your search.</p>
                </div>

                <!-- Pagination -->
                <div class="p-6 border-t border-white/5 bg-black/10 flex items-center justify-between">
                    <div class="text-xs text-slate-500 font-mono">Showing {{ workspaces.from }} to {{ workspaces.to }} of {{ workspaces.total }}</div>
                    <div class="flex items-center gap-2">
                        <Link v-for="link in workspaces.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                          :class="['px-3 py-1.5 text-xs font-bold rounded-lg transition-all border', link.active ? 'bg-primary text-white border-primary shadow-glow shadow-primary/30' : 'bg-surface border-white/5 text-slate-400 hover:bg-white/5', !link.url ? 'opacity-30 cursor-not-allowed' : '']" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Upgrade Plan Modal -->
        <Modal :show="showUpgradeModal" @close="closeUpgradeModal" max-width="md">
            <div class="p-6 bg-surface border border-white/5 rounded-2xl shadow-2xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-3xl">workspace_premium</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Update Subscription</h3>
                        <p class="text-xs text-content-variant italic">{{ selectedWorkspace?.name }}</p>
                    </div>
                </div>

                <div class="space-y-3 mb-8">
                    <div v-for="plan in plans" :key="plan.id" 
                        @click="upgradeForm.plan_id = plan.id"
                        :class="[
                            'p-4 rounded-xl border cursor-pointer transition-all flex items-center justify-between group',
                            upgradeForm.plan_id === plan.id 
                                ? 'bg-primary/10 border-primary shadow-glow shadow-primary/20' 
                                : 'bg-canvas border-white/5 hover:border-white/20'
                        ]"
                    >
                        <div class="flex items-center gap-4">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center border transition-all', upgradeForm.plan_id === plan.id ? 'bg-primary border-primary text-white' : 'bg-white/5 border-white/10 text-slate-500']">
                                <span class="material-symbols-outlined text-[18px]">{{ upgradeForm.plan_id === plan.id ? 'check' : 'circle' }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">{{ plan.name }}</div>
                                <div class="text-[11px] text-content-variant">${{ plan.price }}/month</div>
                            </div>
                        </div>
                        <div v-if="selectedWorkspace?.plan_id === plan.id" class="text-[9px] font-bold text-slate-500 uppercase tracking-widest border border-white/10 px-2 py-0.5 rounded">Current</div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button @click="closeUpgradeModal" class="flex-1 px-4 py-2.5 border border-white/10 text-white rounded-xl font-bold hover:bg-white/5 transition-all outline-none">Cancel</button>
                    <button @click="submitUpgrade" :disabled="upgradeForm.processing || upgradeForm.plan_id === selectedWorkspace?.plan_id" 
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl font-bold shadow-glow hover:bg-primary/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed outline-none"
                    >
                        {{ upgradeForm.processing ? 'Updating...' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/UI/Modal.vue';
import { watchDebounced } from '@vueuse/core';

const props = defineProps({
    workspaces: Object,
    plans: Array,
    filters: Object
});

const search = ref(props.filters.search);
const showUpgradeModal = ref(false);
const selectedWorkspace = ref(null);

const upgradeForm = useForm({
    plan_id: null
});

watchDebounced(search, (value) => {
    router.get('/admin/workspaces', { search: value }, { preserveState: true, replace: true });
}, { debounce: 500 });

const openUpgradeModal = (workspace) => {
    selectedWorkspace.value = workspace;
    upgradeForm.plan_id = workspace.plan_id;
    showUpgradeModal.value = true;
};

const closeUpgradeModal = () => {
    showUpgradeModal.value = false;
    selectedWorkspace.value = null;
};

const submitUpgrade = () => {
    upgradeForm.put(`/admin/workspaces/${selectedWorkspace.value.id}/plan`, {
        onSuccess: () => closeUpgradeModal(),
        preserveScroll: true
    });
};

const toggleStatus = (workspace) => {
    const action = workspace.is_active ? 'deactivated' : 'activated';
    if (confirm(`Are you sure you want to ${workspace.is_active ? 'suspend' : 'activate'} ${workspace.name}?`)) {
        router.post(`/admin/workspaces/${workspace.id}/toggle-active`, {}, {
            preserveScroll: true
        });
    }
};

const summaryInfo = computed(() => [
    { label: 'Total Units', value: props.workspaces.total, icon: 'hub', color: 'bg-primary shadow-primary/20' },
    { label: 'Revenue Units', value: props.workspaces.data.filter(w => w.plan_name !== 'Free' && w.is_active).length, icon: 'workspace_premium', color: 'bg-tertiary shadow-tertiary/20' },
    { label: 'Suspended', value: props.workspaces.data.filter(w => !w.is_active).length, icon: 'shield_lock', color: 'bg-error shadow-error/20' },
]);
</script>
