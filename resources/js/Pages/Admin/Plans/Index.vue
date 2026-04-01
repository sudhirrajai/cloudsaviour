<template>
    <AdminLayout current-page="plans" page-title="Subscription Plans">
        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">System Plans</h2>
                    <p class="text-content-variant text-sm mt-1">Manage subscription tiers and their features.</p>
                </div>
                <button @click="openCreateModal" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-semibold shadow-glow hover:bg-primary/90 transition-all">
                    <span class="material-symbols-outlined text-[20px]">add_task</span>
                    Create New Plan
                </button>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="plan in plans" :key="plan.id" 
                    class="bg-surface border border-white/5 rounded-2xl p-6 shadow-glass hover:border-primary/30 transition-all flex flex-col relative overflow-hidden group"
                >
                    <!-- Plan header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ plan.name }}</h3>
                            <div class="text-2xl font-bold text-primary mt-1">${{ plan.price }}<span class="text-sm font-normal text-content-variant">/mo</span></div>
                        </div>
                        <div :class="['px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider', plan.is_active ? 'bg-success/20 text-success' : 'bg-error/20 text-error']">
                            {{ plan.is_active ? 'Active' : 'Inactive' }}
                        </div>
                    </div>

                    <p class="text-sm text-content-variant mb-6 line-clamp-2 italic">{{ plan.description || 'No description provided.' }}</p>

                    <!-- Features -->
                    <div class="space-y-3 mb-8 flex-1">
                        <div v-for="(feature, index) in plan.features" :key="index" class="flex items-center gap-2 text-sm text-content">
                            <span class="material-symbols-outlined text-success text-[18px]">check_circle</span>
                            {{ feature }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-6 border-t border-white/5">
                        <button @click="openEditModal(plan)" class="flex-1 px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg text-sm font-medium transition-colors border border-white/10">
                            Edit
                        </button>
                        <button @click="toggleStatus(plan)" :title="plan.is_active ? 'Inactivate' : 'Activate'" :class="['p-2 rounded-lg transition-colors border', plan.is_active ? 'border-warning/20 text-warning hover:bg-warning/10' : 'border-success/20 text-success hover:bg-success/10']">
                            <span class="material-symbols-outlined text-[18px]">{{ plan.is_active ? 'block' : 'undo' }}</span>
                        </button>
                        <button @click="deletePlan(plan)" title="Delete Plan" class="p-2 rounded-lg border border-error/20 text-error hover:bg-error/10 transition-all">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="md">
            <div class="p-6 bg-surface border border-white/5 rounded-2xl shadow-2xl relative">
                <button @click="closeModal" class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <h3 class="text-xl font-bold text-white mb-6">{{ editingPlan ? 'Edit Plan' : 'Create New Plan' }}</h3>
                
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1.5 font-mono uppercase tracking-widest text-[10px]">Plan Name</label>
                        <input v-model="form.name" type="text" placeholder="e.g. Professional" 
                            class="w-full bg-canvas border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-primary/50 outline-none transition-all placeholder:text-slate-600 shadow-glass" required>
                    </div>

                    <div v-if="!editingPlan">
                        <label class="block text-sm font-medium text-slate-400 mb-1.5 font-mono uppercase tracking-widest text-[10px]">Slug (Unique ID)</label>
                        <input v-model="form.slug" type="text" placeholder="e.g. pro" 
                            class="w-full bg-canvas border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-primary/50 outline-none transition-all placeholder:text-slate-600 shadow-glass" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1.5 font-mono uppercase tracking-widest text-[10px]">Price (USD)</label>
                            <input v-model="form.price" type="number" step="0.01" 
                                class="w-full bg-canvas border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-primary/50 outline-none transition-all placeholder:text-slate-600 shadow-glass" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1.5 font-mono uppercase tracking-widest text-[10px]">Status</label>
                            <select v-model="form.is_active" class="w-full bg-canvas border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-primary/50 outline-none transition-all shadow-glass">
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1.5 font-mono uppercase tracking-widest text-[10px]">Description</label>
                        <textarea v-model="form.description" rows="2" 
                            class="w-full bg-canvas border border-white/10 rounded-xl px-4 py-2 text-white focus:border-primary/50 outline-none transition-all placeholder:text-slate-600 shadow-glass"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1.5 flex justify-between font-mono uppercase tracking-widest text-[10px]">
                            Features
                            <button type="button" @click="addFeature" class="text-primary hover:text-primary-dim text-[10px] font-bold">+ ARROW ADD</button>
                        </label>
                        <div class="space-y-2 max-h-40 overflow-y-auto pr-2 no-scrollbar">
                            <div v-for="(feature, index) in form.features" :key="index" class="flex gap-2">
                                <input v-model="form.features[index]" type="text" placeholder="Feature detail"
                                    class="flex-1 bg-canvas/50 border border-white/5 rounded-lg px-3 py-1.5 text-sm text-white focus:border-primary/30 outline-none transition-all shadow-sm">
                                <button type="button" @click="removeFeature(index)" class="text-error/60 hover:text-error transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">delete_forever</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="closeModal" class="flex-1 px-4 py-2.5 border border-white/10 text-white rounded-xl font-bold hover:bg-white/5 transition-all outline-none">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl font-bold shadow-glow hover:bg-primary/90 transition-all disabled:opacity-50 outline-none">
                            {{ editingPlan ? 'Save Changes' : 'Create Plan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    plans: Array
});

const showModal = ref(false);
const editingPlan = ref(null);

const form = useForm({
    name: '',
    slug: '',
    description: '',
    price: 0,
    features: [''],
    is_active: true
});

const openCreateModal = () => {
    editingPlan.value = null;
    form.reset();
    form.features = [''];
    showModal.value = true;
};

const openEditModal = (plan) => {
    editingPlan.value = plan;
    form.name = plan.name;
    form.slug = plan.slug;
    form.description = plan.description;
    form.price = plan.price;
    form.features = Array.isArray(plan.features) ? [...plan.features] : [''];
    form.is_active = plan.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const addFeature = () => {
    form.features.push('');
};

const removeFeature = (index) => {
    form.features.splice(index, 1);
};

const deletePlan = (plan) => {
    if (confirm(`Are you sure you want to PERMANENTLY delete the ${plan.name} plan? This cannot be undone.`)) {
        router.delete(`/admin/plans/${plan.id}`, { preserveScroll: true });
    }
};

const toggleStatus = (plan) => {
    router.put(`/admin/plans/${plan.id}`, {
        name: plan.name,
        price: plan.price,
        description: plan.description,
        features: plan.features,
        is_active: !plan.is_active
    }, {
        preserveScroll: true
    });
};

const submit = () => {
    if (editingPlan.value) {
        form.put(`/admin/plans/${editingPlan.value.id}`, {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    } else {
        form.post('/admin/plans', {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    }
};
</script>
