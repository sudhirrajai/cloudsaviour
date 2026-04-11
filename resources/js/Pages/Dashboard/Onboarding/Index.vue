<template>
    <DashboardLayout currentPage="onboarding">
        <div class="max-w-4xl mx-auto py-12">
            <!-- Header -->
            <header class="mb-12 text-center relative z-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white border border-slate-900 text-primary mb-6 shadow-sm">
                    <span class="material-symbols-outlined text-[32px]">rocket_launch</span>
                </div>
                <h1 class="text-5xl font-display font-bold text-slate-900 mb-4 tracking-tight">Welcome to CloudSaviour</h1>
                <p class="text-slate-600 italic font-sans text-lg max-w-2xl mx-auto">
                    Let's connect your AWS account. We require access to monitor your resources, retrieve cost data, and execute the resource optimizations and automation schedules you define.
                </p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 relative z-10">
                
                <!-- Step 1: IAM Policy -->
                <div class="bg-white border border-slate-900 rounded-2xl p-8 shadow-ambient relative overflow-hidden group flex flex-col h-full">
                    <div class="relative z-10 flex-1">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-primary text-white border border-slate-900 flex items-center justify-center font-bold text-sm">1</div>
                            <h2 class="text-xl font-bold text-slate-900">Create IAM Policy</h2>
                        </div>
                        
                        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                            Log into your AWS Console, create a new IAM Policy, and paste the JSON below. Then, create an IAM User, attach this policy, and generate Access Keys.
                        </p>

                        <div class="relative bg-slate-900 rounded-xl border border-slate-900 p-4 group/code">
                            <button 
                                @click="copyPolicy" 
                                class="absolute top-3 right-3 p-2 rounded-lg bg-white border border-slate-300 text-slate-600 hover:text-primary hover:border-primary transition-all opacity-0 group-hover/code:opacity-100 focus:opacity-100 flex items-center gap-2 text-xs font-mono uppercase tracking-widest shadow-sm"
                                :class="{ 'text-primary border-primary bg-primary/5': copied }"
                            >
                                <span class="material-symbols-outlined text-[16px]">{{ copied ? 'check' : 'content_copy' }}</span>
                                {{ copied ? 'Copied!' : 'Copy JSON' }}
                            </button>
                            <pre class="text-[11px] font-mono text-slate-300 overflow-y-auto max-h-64 no-scrollbar"><code>{{ iamPolicy }}</code></pre>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Input Credentials -->
                <div class="bg-white border border-slate-900 rounded-2xl p-8 shadow-ambient relative overflow-hidden group flex flex-col h-full">
                    <div class="relative z-10 flex-1 flex flex-col">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-tertiary text-white border border-slate-900 flex items-center justify-center font-bold text-sm">2</div>
                            <h2 class="text-xl font-bold text-slate-900">Connect Account</h2>
                        </div>

                        <form @submit.prevent="submit" class="flex-1 flex flex-col space-y-5">
                            <div>
                                <label for="aws_access_key" class="block text-xs font-mono font-bold uppercase tracking-[0.2em] text-slate-600 mb-2">Access Key ID</label>
                                <input 
                                    v-model="form.aws_access_key"
                                    type="text" 
                                    id="aws_access_key"
                                    placeholder="AKIAIOSFODNN7EXAMPLE"
                                    class="w-full bg-white border border-slate-900 rounded-lg px-4 py-3 text-slate-900 focus:outline-none focus:border-tertiary transition-all font-mono text-sm placeholder-slate-400 shadow-sm"
                                    :class="{ 'border-error': form.errors.aws_access_key }"
                                >
                                <p v-if="form.errors.aws_access_key" class="mt-1 text-xs text-error font-mono font-bold">{{ form.errors.aws_access_key }}</p>
                            </div>

                            <div>
                                <label for="aws_secret_key" class="block text-xs font-mono font-bold uppercase tracking-[0.2em] text-slate-600 mb-2">Secret Access Key</label>
                                <input 
                                    v-model="form.aws_secret_key"
                                    type="password" 
                                    id="aws_secret_key"
                                    placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                                    class="w-full bg-white border border-slate-900 rounded-lg px-4 py-3 text-slate-900 focus:outline-none focus:border-tertiary transition-all font-mono text-sm placeholder-slate-400 shadow-sm"
                                    :class="{ 'border-error': form.errors.aws_secret_key }"
                                >
                                <p v-if="form.errors.aws_secret_key" class="mt-1 text-xs text-error font-mono font-bold">{{ form.errors.aws_secret_key }}</p>
                            </div>

                            <div>
                                <label for="aws_region" class="block text-xs font-mono font-bold uppercase tracking-[0.2em] text-slate-600 mb-2">Default Region</label>
                                <select 
                                    v-model="form.aws_region"
                                    id="aws_region"
                                    class="w-full bg-white border border-slate-900 rounded-lg px-4 py-3 text-slate-900 focus:outline-none focus:border-tertiary transition-all font-sans text-sm appearance-none cursor-pointer hover:bg-slate-50 shadow-sm"
                                    :class="{ 'border-error': form.errors.aws_region }"
                                >
                                    <option value="" disabled class="bg-white text-slate-900">Select a default region...</option>
                                    <option v-for="region in awsRegions" :key="region.value" :value="region.value" class="bg-white text-slate-900">
                                        {{ region.label }} ({{ region.value }})
                                    </option>
                                </select>
                                <p v-if="form.errors.aws_region" class="mt-1 text-xs text-error font-mono font-bold">{{ form.errors.aws_region }}</p>
                            </div>

                            <div class="pt-6 mt-auto flex flex-col gap-3">
                                <button 
                                    type="submit" 
                                    :disabled="form.processing || isFormEmpty"
                                    class="w-full bg-tertiary hover:bg-tertiary/90 text-white px-6 py-4 rounded-xl font-mono text-sm uppercase tracking-widest font-bold flex items-center justify-center gap-3 active:scale-95 transition-all shadow-glow disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="material-symbols-outlined text-[20px]">cloud_done</span>
                                    {{ form.processing ? 'Saving...' : 'Complete Setup' }}
                                </button>
                                
                                <button 
                                    type="button"
                                    @click="skipSetup"
                                    :disabled="form.processing"
                                    class="w-full px-6 py-3 rounded-xl border border-slate-300 text-slate-500 hover:text-slate-900 font-mono text-[11px] uppercase tracking-widest text-center hover:bg-slate-50 transition-all active:scale-95 flex items-center justify-center gap-2"
                                >
                                    Skip for now
                                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            
            <!-- Safe Mode Notice -->
            <div class="mt-12 text-center max-w-xl mx-auto flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-900 shadow-sm">
                <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">shield</span>
                <p class="text-xs text-slate-600 text-left font-sans leading-relaxed">
                    <strong class="text-slate-900 font-bold uppercase tracking-widest text-[10px]">Secure by default.</strong> Your credentials are encrypted at rest using AES-256 cipher. CloudSaviour only uses the requested policy to read configurations and cannot modify or delete your resources (except when applying Idle Resource recommendations).
                </p>
            </div>
            
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    hasCredentials: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    aws_access_key: '',
    aws_secret_key: '',
    aws_region: '',
});

const isFormEmpty = computed(() => {
    return !form.aws_access_key.trim() || !form.aws_secret_key.trim() || !form.aws_region;
});

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

const submit = () => {
    form.post('/dashboard/onboarding', {
        preserveScroll: true,
    });
};

const skipSetup = () => {
    router.post('/dashboard/onboarding', { skip: true });
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
        "elasticloadbalancing:DescribeLoadBalancers",
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
</script>
