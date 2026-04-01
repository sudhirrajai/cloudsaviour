<template>
    <DashboardLayout currentPage="settings">
        <!-- Page Header -->
        <header class="mb-10">
            <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-white mb-2">Settings</h1>
            <p class="text-content-variant font-sans">Configure your workspace, AWS credentials, and notification preferences.</p>
        </header>

        <!-- AWS Credentials Section -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 p-8 mb-10 relative overflow-hidden group">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-primary/20 flex items-center justify-center rounded-2xl border border-primary/30 shadow-glow shadow-primary/10 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-primary text-[24px]">key</span>
                </div>
                <div>
                    <h2 class="text-xl font-display font-bold text-white tracking-tight">AWS Infrastructure Connection</h2>
                    <div class="flex items-center gap-2 mt-2">
                        <div v-if="workspace.has_aws_credentials" class="flex items-center gap-1.5 px-2.5 py-1 bg-tertiary/10 border border-tertiary/20 rounded-full shadow-glow shadow-tertiary/5">
                            <span class="material-symbols-outlined text-[14px] text-tertiary font-bold">check_circle</span>
                            <span class="text-[9px] font-mono uppercase tracking-[0.2em] font-bold text-tertiary">Connected</span>
                        </div>
                        <div v-else class="flex items-center gap-1.5 px-2.5 py-1 bg-error/10 border border-error/20 rounded-full shadow-glow shadow-error/5 pulse-subtle">
                            <span class="material-symbols-outlined text-[14px] text-error">warning_amber</span>
                            <span class="text-[9px] font-mono uppercase tracking-[0.2em] font-bold text-error">Configuration Required</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <form @submit.prevent="saveAws">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Primary AWS Region</label>
                        <div class="relative">
                            <select v-model="awsForm.aws_region" class="w-full bg-white/5 text-white text-sm font-mono border border-white/10 rounded-lg px-4 py-3 focus:outline-none focus:border-primary/50 appearance-none cursor-pointer hover:bg-white/[0.08] transition-all">
                                <option value="">Select Target Region</option>
                                <option value="ap-south-1">ap-south-1 (Mumbai)</option>
                                <option value="us-east-1">us-east-1 (N. Virginia)</option>
                                <option value="us-west-2">us-west-2 (Oregon)</option>
                                <option value="eu-west-1">eu-west-1 (Ireland)</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">AWS Account ID</label>
                        <input type="text" v-model="awsForm.aws_account_id" placeholder="123456789012" class="w-full bg-white/5 text-white text-sm font-mono border border-white/10 rounded-lg px-4 py-3 focus:outline-none focus:border-primary/50 placeholder-slate-600 hover:bg-white/[0.08] transition-all" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">IAM Access Key ID</label>
                        <div class="relative group/field">
                            <input :type="showAccessKey ? 'text' : 'password'" v-model="awsForm.aws_access_key" placeholder="AKIA..." class="w-full bg-white/5 text-white text-sm font-mono border border-white/10 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:border-primary/50 hover:bg-white/[0.08] transition-all" />
                            <button type="button" @click="showAccessKey = !showAccessKey" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[18px]">{{ showAccessKey ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">IAM Secret Access Key</label>
                        <div class="relative group/field">
                            <input :type="showSecretKey ? 'text' : 'password'" v-model="awsForm.aws_secret_key" placeholder="••••••••••••••••••••" class="w-full bg-white/5 text-white text-sm font-mono border border-white/10 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:border-primary/50 hover:bg-white/[0.08] transition-all" />
                            <button type="button" @click="showSecretKey = !showSecretKey" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[18px]">{{ showSecretKey ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4 border-t border-white/5 pt-8">
                    <button type="button" @click="testConnection" :disabled="testing || !workspace.has_aws_credentials && !awsForm.isDirty" class="bg-white/5 hover:bg-white/10 text-white border border-white/10 px-6 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm" :class="{'animate-spin': testing}">{{ testing ? 'sync' : 'database' }}</span>
                        {{ testing ? 'Verifying...' : 'Test Connection' }}
                    </button>
                    <button type="submit" :disabled="awsForm.processing" class="bg-primary hover:brightness-110 text-white px-8 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow shadow-primary/20 disabled:opacity-50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">save</span>
                        {{ awsForm.processing ? 'Syncing...' : 'Update Credentials' }}
                    </button>
                </div>
            </form>
            <!-- Background element -->
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>
 
        <!-- Workspace Settings Section -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 p-8 mb-10 relative overflow-hidden group">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-secondary/20 flex items-center justify-center rounded-2xl border border-secondary/30 shadow-glow shadow-secondary/10 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-secondary text-[24px]">settings</span>
                </div>
                <h2 class="text-xl font-display font-bold text-white tracking-tight">Workspace Identity</h2>
            </div>
            
            <form @submit.prevent="updateWorkspace">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Display Name</label>
                        <input type="text" v-model="workspaceForm.name" class="w-full bg-white/5 text-white text-sm border border-white/10 rounded-lg px-4 py-3 focus:outline-none focus:border-secondary/50 hover:bg-white/[0.08] transition-all" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Unique Workspace Identifier (Slug)</label>
                        <div class="relative">
                            <input type="text" :value="workspace.slug" class="w-full bg-white/5 text-slate-500 text-sm font-mono border border-white/10 rounded-lg px-4 py-3 cursor-not-allowed opacity-60" readonly />
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-600 text-[18px]">lock</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Current Subscription Plan</label>
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-4 py-2 rounded-lg text-[10px] font-mono bg-secondary/20 text-secondary border border-secondary/30 font-bold tracking-widest shadow-glow shadow-secondary/10 uppercase">{{ workspace.plan }} Plan</span>
                            <a href="#" class="text-primary text-[11px] font-mono hover:text-white transition-colors uppercase tracking-widest font-bold border-b border-primary/30 pb-0.5">Upgrade Workspace →</a>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Creation Timestamp</label>
                        <div class="flex items-center gap-2 text-sm font-mono text-slate-500">
                            <span class="material-symbols-outlined text-xs">calendar_today</span>
                            {{ workspace.created_at }}
                        </div>
                    </div>
                </div>
                <div class="border-t border-white/5 pt-8">
                    <button type="submit" :disabled="workspaceForm.processing" class="bg-secondary hover:brightness-110 text-white px-8 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow shadow-secondary/20 disabled:opacity-50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">published_with_changes</span>
                        {{ workspaceForm.processing ? 'Updating...' : 'Save Workspace Changes' }}
                    </button>
                </div>
            </form>
        </section>
 
        <!-- Notifications Section -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 p-8 mb-10 relative overflow-hidden group">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-tertiary/20 flex items-center justify-center rounded-2xl border border-tertiary/30 shadow-glow shadow-tertiary/10 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-tertiary text-[24px]">notifications</span>
                </div>
                <h2 class="text-xl font-display font-bold text-white tracking-tight">Intelligence & Alerts</h2>
            </div>
            <div class="space-y-2 mb-8">
                <div v-for="(pref, index) in notificationPrefs" :key="index" class="flex items-center justify-between p-4 rounded-xl hover:bg-white/[0.03] border border-transparent hover:border-white/5 transition-all">
                    <span class="text-sm text-slate-300 font-medium">{{ pref.label }}</span>
                    <Toggle v-model="pref.enabled" />
                </div>
                <div class="flex items-center justify-between p-4 rounded-xl border border-white/5 bg-white/[0.02]">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-slate-500 font-medium">Slack Integration Hub</span>
                            <span class="bg-white/10 text-slate-400 text-[8px] font-mono px-2 py-0.5 rounded-full border border-white/10 tracking-[0.2em] font-bold">LEGACY / COMING SOON</span>
                        </div>
                    </div>
                    <div class="relative w-72">
                        <input type="text" placeholder="https://hooks.slack.com/..." disabled class="w-full bg-white/5 text-slate-600 text-xs font-mono border border-white/10 rounded-lg px-4 py-2 opacity-50 cursor-not-allowed" />
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-700 text-[16px]">link_off</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/5 pt-8">
                <button @click="saveNotifications" class="bg-tertiary hover:brightness-110 text-canvas px-8 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow shadow-tertiary/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm font-bold">check_circle</span>
                    Apply Intelligence Rules
                </button>
            </div>
        </section>
 
        <!-- Danger Zone -->
        <section class="bg-error/5 backdrop-blur-sm rounded-lg border border-error/20 p-8 relative overflow-hidden group">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-error/20 flex items-center justify-center rounded-2xl border border-error/30 shadow-glow shadow-error/10 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-error text-[24px]">gpp_maybe</span>
                </div>
                <h2 class="text-xl font-display font-bold text-error tracking-tight">System Termination</h2>
            </div>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <p class="text-sm text-error/60 max-w-2xl leading-relaxed">Permanently purge this entire workspace ecosystem. This will immediately terminate all scheduled automation, wipe activity audit trails, and disconnect secure AWS bridge tokens. This operation is <span class="font-bold text-error underline underline-offset-4">irreversible</span>.</p>
                <button @click="deleteWorkspace" class="bg-error hover:brightness-110 text-white px-8 py-3 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow shadow-error/20 flex items-center gap-3 shrink-0">
                    <span class="material-symbols-outlined text-[18px]">delete_forever</span>
                    Full System Termination
                </button>
            </div>
            <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-transparent via-error/20 to-transparent"></div>
        </section>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Toggle from '@/Components/UI/Toggle.vue';

const props = defineProps({
    workspace: {
        type: Object,
        required: true
    }
});

const showAccessKey = ref(false);
const showSecretKey = ref(false);
const testing = ref(false);

const awsForm = useForm({
    aws_access_key: '',
    aws_secret_key: '',
    aws_region: props.workspace.aws_region || '',
    aws_account_id: props.workspace.aws_account_id || '',
});

const workspaceForm = useForm({
    name: props.workspace.name,
});

const notificationPrefs = ref([
    { label: 'Email notifications for server status changes', enabled: true },
    { label: 'Email notifications for idle resource detection', enabled: true },
    { label: 'Email notifications for scheduled action failures', enabled: false },
    { label: 'Weekly cost summary report', enabled: true },
]);

const saveAws = () => {
    awsForm.put('/dashboard/settings/aws', {
        preserveScroll: true,
        onSuccess: () => {
            awsForm.aws_access_key = '';
            awsForm.aws_secret_key = '';
        }
    });
};

const testConnection = () => {
    testing.value = true;
    router.post('/dashboard/settings/test-connection', {}, {
        preserveScroll: true,
        onFinish: () => testing.value = false
    });
};

const updateWorkspace = () => {
    workspaceForm.put('/dashboard/settings/workspace', {
        preserveScroll: true,
    });
};

const saveNotifications = () => {
    router.put('/dashboard/settings/notifications', {
        prefs: notificationPrefs.value
    }, {
        preserveScroll: true
    });
};

const deleteWorkspace = () => {
    if (confirm('Are you sure you want to delete this workspace? This action cannot be undone.')) {
        router.delete('/dashboard/settings/workspace');
    }
};
</script>

