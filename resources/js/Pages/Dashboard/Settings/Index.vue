<template>
    <DashboardLayout currentPage="settings">
        <!-- Page Header -->
        <header class="mb-10">
            <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-slate-900 mb-2">Settings</h1>
            <p class="text-slate-600 font-sans">Configure your workspace, AWS credentials, and notification preferences.</p>
        </header>

        <!-- AWS Credentials Section -->
        <section class="bg-white rounded-lg border border-slate-900 p-8 mb-10 relative overflow-hidden group shadow-ambient">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-white flex items-center justify-center rounded-2xl border border-slate-900 shadow-sm group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-primary text-[24px]">key</span>
                </div>
                <div>
                    <h2 class="text-xl font-display font-bold text-slate-900 tracking-tight">AWS Infrastructure Connection</h2>
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

            <div class="mb-8 p-6 bg-slate-50 rounded-xl border border-slate-900 relative group/code">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 tracking-widest uppercase font-mono">Required IAM Policy</h3>
                    <div class="flex gap-2">
                        <button 
                            type="button"
                            @click="copyPolicy" 
                            class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-600 hover:text-primary hover:border-primary transition-all flex items-center gap-2 text-[10px] font-mono uppercase tracking-widest shadow-sm"
                            :class="{ 'text-primary border-primary bg-primary/5': copied }"
                        >
                            <span class="material-symbols-outlined text-[14px]">{{ copied ? 'check' : 'content_copy' }}</span>
                            {{ copied ? 'Copied' : 'Copy' }}
                        </button>
                        <button 
                            type="button"
                            @click="downloadPolicy" 
                            class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-600 hover:text-primary hover:border-primary transition-all flex items-center gap-2 text-[10px] font-mono uppercase tracking-widest shadow-sm"
                        >
                            <span class="material-symbols-outlined text-[14px]">download</span>
                            Download
                        </button>
                    </div>
                </div>
                <p class="text-xs text-slate-600 mb-4 font-sans max-w-2xl">Create an IAM policy with this JSON and attach it to the user you generate credentials for. This gives CloudSaviour read-only access to help you manage resources.</p>
                <div class="bg-slate-900 rounded-lg p-3">
                    <pre class="text-[11px] font-mono text-slate-300 overflow-y-auto max-h-36 no-scrollbar"><code>{{ iamPolicy }}</code></pre>
                </div>
            </div>
            
            <form @submit.prevent="saveAws">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Primary AWS Region</label>
                        <div class="relative">
                            <select v-model="awsForm.aws_region" class="w-full bg-white text-slate-900 text-sm font-mono border border-slate-900 rounded-lg px-4 py-3 focus:outline-none focus:border-primary appearance-none cursor-pointer hover:bg-slate-50 transition-all">
                                <option value="" disabled class="bg-white text-slate-900">Select Target Region</option>
                                <option v-for="region in awsRegions" :key="region.value" :value="region.value" class="bg-white text-slate-900">
                                    {{ region.label }} ({{ region.value }})
                                </option>
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">AWS Account ID</label>
                        <input type="text" v-model="awsForm.aws_account_id" placeholder="123456789012" class="w-full bg-white text-slate-900 text-sm font-mono border border-slate-900 rounded-lg px-4 py-3 focus:outline-none focus:border-primary placeholder-slate-400 hover:bg-slate-50 transition-all" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">IAM Access Key ID</label>
                        <div class="relative group/field">
                            <input :type="showAccessKey ? 'text' : 'password'" v-model="awsForm.aws_access_key" placeholder="AKIA..." class="w-full bg-white text-slate-900 text-sm font-mono border border-slate-900 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:border-primary hover:bg-slate-50 transition-all" />
                            <button type="button" @click="showAccessKey = !showAccessKey" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">{{ showAccessKey ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">IAM Secret Access Key</label>
                        <div class="relative group/field">
                            <input :type="showSecretKey ? 'text' : 'password'" v-model="awsForm.aws_secret_key" placeholder="••••••••••••••••••••" class="w-full bg-white text-slate-900 text-sm font-mono border border-slate-900 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:border-primary hover:bg-slate-50 transition-all" />
                            <button type="button" @click="showSecretKey = !showSecretKey" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">{{ showSecretKey ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4 border-t border-slate-200 pt-8">
                    <button type="button" @click="testConnection" :disabled="testing || !workspace.has_aws_credentials && !awsForm.isDirty" class="bg-white hover:bg-slate-50 text-slate-900 border border-slate-900 px-6 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-sm" :class="{'animate-spin': testing}">{{ testing ? 'sync' : 'database' }}</span>
                        {{ testing ? 'Verifying...' : 'Test Connection' }}
                    </button>
                    <button type="submit" :disabled="awsForm.processing" class="bg-primary hover:bg-primary/90 text-white px-8 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow disabled:opacity-50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">save</span>
                        {{ awsForm.processing ? 'Syncing...' : 'Update Credentials' }}
                    </button>
                </div>
            </form>
            <!-- Background element -->
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>
 
        <!-- Workspace Settings Section -->
        <section class="bg-white rounded-lg border border-slate-900 p-8 mb-10 relative overflow-hidden group shadow-ambient">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-white flex items-center justify-center rounded-2xl border border-slate-900 shadow-sm group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-secondary text-[24px]">settings</span>
                </div>
                <h2 class="text-xl font-display font-bold text-slate-900 tracking-tight">Workspace Identity</h2>
            </div>
            
            <form @submit.prevent="updateWorkspace">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Display Name</label>
                        <input type="text" v-model="workspaceForm.name" class="w-full bg-white text-slate-900 text-sm border border-slate-900 rounded-lg px-4 py-3 focus:outline-none focus:border-secondary hover:bg-slate-50 transition-all" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Unique Workspace Identifier (Slug)</label>
                        <div class="relative">
                            <input type="text" :value="workspace.slug" class="w-full bg-slate-50 text-slate-500 text-sm font-mono border border-slate-300 rounded-lg px-4 py-3 cursor-not-allowed" readonly />
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">lock</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] mb-2 font-bold opacity-70">Current Subscription Plan</label>
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-4 py-2 rounded-lg text-[10px] font-mono bg-white text-secondary border border-slate-900 font-bold tracking-widest shadow-sm uppercase">{{ workspace.plan }} Plan</span>
                            <a href="#" class="text-primary text-[11px] font-mono hover:text-slate-900 transition-colors uppercase tracking-widest font-bold border-b border-primary/30 pb-0.5">Upgrade Workspace →</a>
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
                <div class="border-t border-slate-200 pt-8">
                    <button type="submit" :disabled="workspaceForm.processing" class="bg-secondary hover:bg-secondary/90 text-white px-8 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow disabled:opacity-50 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">published_with_changes</span>
                        {{ workspaceForm.processing ? 'Updating...' : 'Save Workspace Changes' }}
                    </button>
                </div>
            </form>
        </section>
 
        <!-- Notifications Section -->
        <section class="bg-white rounded-lg border border-slate-900 p-8 mb-10 relative overflow-hidden group shadow-ambient">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-white flex items-center justify-center rounded-2xl border border-slate-900 shadow-sm group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-tertiary text-[24px]">notifications</span>
                </div>
                <h2 class="text-xl font-display font-bold text-slate-900 tracking-tight">Intelligence & Alerts</h2>
            </div>
            <div class="space-y-2 mb-8">
                <div v-for="(pref, index) in notificationPrefs" :key="index" class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-all">
                    <span class="text-sm text-slate-600 font-bold uppercase tracking-widest">{{ pref.label }}</span>
                    <Toggle v-model="pref.enabled" />
                </div>
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-slate-50">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-slate-500 font-medium">Slack Integration Hub</span>
                            <span class="bg-white text-slate-400 text-[8px] font-mono px-2 py-0.5 rounded-full border border-slate-200 tracking-[0.2em] font-bold">LEGACY / COMING SOON</span>
                        </div>
                    </div>
                    <div class="relative w-72">
                        <input type="text" placeholder="https://hooks.slack.com/..." disabled class="w-full bg-white text-slate-400 text-xs font-mono border border-slate-200 rounded-lg px-4 py-2 cursor-not-allowed opacity-50" />
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 text-[16px]">link_off</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-200 pt-8">
                <button @click="saveNotifications" class="bg-tertiary hover:bg-tertiary/90 text-white px-8 py-2.5 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm font-bold">check_circle</span>
                    Apply Intelligence Rules
                </button>
            </div>
        </section>
 
        <!-- Danger Zone -->
        <section class="bg-error/5 rounded-lg border border-slate-900 p-8 relative overflow-hidden group shadow-ambient">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-white flex items-center justify-center rounded-2xl border border-error shadow-sm group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-error text-[24px]">gpp_maybe</span>
                </div>
                <h2 class="text-xl font-display font-bold text-error tracking-tight">System Termination</h2>
            </div>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <p class="text-sm text-slate-600 max-w-2xl leading-relaxed">Permanently purge this entire workspace ecosystem. This will immediately terminate all scheduled automation, wipe activity audit trails, and disconnect secure AWS bridge tokens. This operation is <span class="font-bold text-error underline underline-offset-4">irreversible</span>.</p>
                <button @click="deleteWorkspace" class="bg-error hover:bg-error/90 text-white px-8 py-3 rounded-lg font-mono text-[11px] uppercase tracking-widest font-bold transition-all active:scale-95 shadow-glow flex items-center gap-3 shrink-0">
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

const copied = ref(false);

const copyPolicy = async () => {
    try {
        await navigator.clipboard.writeText(iamPolicy);
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    } catch (err) {
        console.error('Failed to copy', err);
    }
};

const downloadPolicy = () => {
    const blob = new Blob([iamPolicy], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'cloudsaviour-iam-policy.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

const awsRegions = [
    { label: 'US East (N. Virginia)', value: 'us-east-1' },
    { label: 'US East (Ohio)', value: 'us-east-2' },
    { label: 'US West (N. California)', value: 'us-west-1' },
    { label: 'US West (Oregon)', value: 'us-west-2' },
    { label: 'Asia Pacific (Mumbai)', value: 'ap-south-1' },
    { label: 'Asia Pacific (Singapore)', value: 'ap-southeast-1' },
    { label: 'Asia Pacific (Sydney)', value: 'ap-southeast-2' },
    { label: 'Asia Pacific (Tokyo)', value: 'ap-northeast-1' },
    { label: 'Canada (Central)', value: 'ca-central-1' },
    { label: 'Europe (Frankfurt)', value: 'eu-central-1' },
    { label: 'Europe (Ireland)', value: 'eu-west-1' },
    { label: 'Europe (London)', value: 'eu-west-2' },
    { label: 'Europe (Paris)', value: 'eu-west-3' },
    { label: 'South America (São Paulo)', value: 'sa-east-1' },
];

const iamPolicy = `{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "EC2Management",
      "Effect": "Allow",
      "Action": [
        "ec2:DescribeInstances",
        "ec2:DescribeInstanceStatus",
        "ec2:StartInstances",
        "ec2:StopInstances",
        "ec2:TerminateInstances",
        "ec2:DescribeVolumes",
        "ec2:DescribeSnapshots",
        "ec2:DeleteSnapshot",
        "ec2:DeleteVolume",
        "ec2:DescribeAddresses",
        "ec2:ReleaseAddress",
        "ec2:DescribeNatGateways",
        "ec2:DeleteNatGateway",
        "ec2:DescribeLoadBalancers",
        "ec2:DescribeRegions",
        "ec2:DescribeTags"
      ],
      "Resource": "*"
    },
    {
      "Sid": "RDSManagement",
      "Effect": "Allow",
      "Action": [
        "rds:DescribeDBInstances",
        "rds:StartDBInstance",
        "rds:StopDBInstance",
        "rds:DeleteDBInstance",
        "rds:ListTagsForResource"
      ],
      "Resource": "*"
    },
    {
      "Sid": "CloudWatchRead",
      "Effect": "Allow",
      "Action": [
        "cloudwatch:GetMetricStatistics",
        "cloudwatch:GetMetricData",
        "cloudwatch:ListMetrics",
        "cloudwatch:DescribeAlarms",
        "logs:DescribeLogGroups",
        "logs:PutRetentionPolicy",
        "logs:DeleteLogGroup"
      ],
      "Resource": "*"
    },
    {
      "Sid": "CostExplorer",
      "Effect": "Allow",
      "Action": [
        "ce:GetCostAndUsage",
        "ce:GetCostForecast",
        "ce:GetDimensionValues"
      ],
      "Resource": "*"
    },
    {
      "Sid": "S3Analysis",
      "Effect": "Allow",
      "Action": [
        "s3:ListAllMyBuckets",
        "s3:GetBucketLocation",
        "s3:ListBucket",
        "s3:GetBucketTagging",
        "s3:PutLifecycleConfiguration",
        "s3:GetLifecycleConfiguration"
      ],
      "Resource": "*"
    }
  ]
}`;

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

