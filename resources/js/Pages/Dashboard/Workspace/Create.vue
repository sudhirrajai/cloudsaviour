<template>
    <DashboardLayout currentPage="workspaces">
        <div class="max-w-2xl mx-auto py-12">
            <header class="mb-10 text-center">
                <h1 class="text-4xl font-display font-bold text-white mb-3">Create New Workspace</h1>
                <p class="text-content-variant italic font-sans">A workspace is a separate environment for your AWS resources and team.</p>
            </header>

            <form @submit.prevent="submit" class="bg-surface border border-white/5 rounded-2xl p-8 shadow-ambient backdrop-blur-xl relative overflow-hidden group">
                <!-- Decorative background -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl group-hover:bg-primary/20 transition-all duration-700"></div>

                <div class="space-y-6 relative z-10">
                    <div>
                        <label for="name" class="block text-xs font-mono font-bold uppercase tracking-[0.2em] text-slate-500 mb-2">Workspace Name</label>
                        <input 
                            v-model="form.name"
                            type="text" 
                            id="name"
                            placeholder="e.g. Production Cluster, Dev Environment"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/20 transition-all font-sans"
                            :class="{ 'border-error/50': form.errors.name }"
                            required
                        >
                        <p v-if="form.errors.name" class="mt-2 text-xs text-error font-mono italic">{{ form.errors.name }}</p>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-4">
                        <Link href="/dashboard/servers" class="flex-1 px-6 py-3 rounded-lg border border-white/10 text-white font-mono text-[11px] uppercase tracking-widest text-center hover:bg-white/5 transition-all active:scale-95">
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="flex-[2] bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold flex items-center justify-center gap-3 active:scale-95 transition-all shadow-[0_0_20px_rgba(59,130,246,0.3)] disabled:opacity-50"
                        >
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            {{ form.processing ? 'Creating...' : 'Create Workspace' }}
                        </button>
                    </div>
                </div>
            </form>

            <section class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6 opacity-60">
                <div class="p-6 rounded-xl border border-white/5 bg-white/2">
                    <div class="w-8 h-8 rounded-lg bg-tertiary/20 flex items-center justify-center text-tertiary mb-4">
                        <span class="material-symbols-outlined text-sm">groups</span>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2">Collaborate</h3>
                    <p class="text-[11px] text-slate-400 leading-relaxed font-sans">Invite team members to your new workspace with granular role permissions.</p>
                </div>
                <div class="p-6 rounded-xl border border-white/5 bg-white/2">
                    <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center text-primary mb-4">
                        <span class="material-symbols-outlined text-sm">key</span>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2">Isolate Resources</h3>
                    <p class="text-[11px] text-slate-400 leading-relaxed font-sans">Each workspace maintains its own set of AWS credentials for maximum security.</p>
                </div>
            </section>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post('/dashboard/workspace', {
        onSuccess: () => {
            // Success toast is handled automatically or by backend redirect
        }
    });
};
</script>
