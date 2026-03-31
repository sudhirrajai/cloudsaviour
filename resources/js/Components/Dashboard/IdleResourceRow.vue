<template>
    <tr class="hover:bg-slate-800/20 transition-colors group">
        <td class="px-6 py-6 align-top">
            <div class="w-10 h-10 flex items-center justify-center rounded" :class="meta.iconBgClass">
                <span class="material-symbols-outlined" :class="meta.iconColorClass">{{ meta.icon }}</span>
            </div>
        </td>
        <td class="px-6 py-6 align-top">
            <div class="text-sm font-semibold text-white mb-1" :class="{'line-through text-on-surface-variant opacity-50': resource.is_ignored}">
                {{ typeLabel }}
            </div>
            <div class="font-mono text-[11px] text-content-variant">
                {{ resource.resource_id }} <span v-if="resource.resource_name">({{ resource.resource_name }})</span>
            </div>
        </td>
        <td class="px-6 py-6 align-top">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono border" :class="meta.badgeClass">
                {{ meta.severity }}
            </span>
        </td>
        <td class="px-6 py-6 align-top">
            <div class="font-mono text-sm text-tertiary font-bold">
                {{ formatCurrency(resource.estimated_monthly_cost) }}<span class="text-[10px] font-normal">/mo</span>
            </div>
        </td>
        <td class="px-6 py-6 align-top text-right space-x-2">
            <template v-if="!resource.is_ignored">
                <button @click="$emit('resolve', resource.id)" class="px-3 py-1.5 rounded border border-error/50 text-error text-[10px] font-mono uppercase hover:bg-error/10 transition-colors">
                    {{ meta.actionLabel }}
                </button>
                <button @click="$emit('ignore', resource.id)" class="px-3 py-1.5 rounded bg-surface text-content-variant text-[10px] font-mono uppercase hover:text-white transition-colors border border-border-ghost">
                    Ignore
                </button>
            </template>
            <template v-else>
                <span class="px-3 py-1.5 text-content-variant text-[10px] font-mono uppercase">Ignored</span>
            </template>
        </td>
    </tr>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    resource: {
        type: Object,
        required: true,
    }
});

defineEmits(['ignore', 'resolve']);

const meta = computed(() => {
    // Map resource_type to different visual indicators
    const mapping = {
        'ebs_volume': { 
            icon: 'storage', 
            iconBgClass: 'bg-amber-500/10', 
            iconColorClass: 'text-amber-500',
            severity: 'MEDIUM',
            badgeClass: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            actionLabel: 'Delete'
        },
        'elastic_ip': { 
            icon: 'public', 
            iconBgClass: 'bg-amber-500/10', 
            iconColorClass: 'text-amber-500',
            severity: 'HIGH',
            badgeClass: 'bg-error/10 text-error border-error/20',
            actionLabel: 'Release'
        },
        'nat_gateway': { 
            icon: 'router', 
            iconBgClass: 'bg-error/10', 
            iconColorClass: 'text-error',
            severity: 'CRITICAL',
            badgeClass: 'bg-error/10 text-error border-error/20',
            actionLabel: 'Terminate'
        },
        'snapshot': { 
            icon: 'camera', 
            iconBgClass: 'bg-primary/10', 
            iconColorClass: 'text-primary',
            severity: 'LOW',
            badgeClass: 'bg-surface text-content-variant border-border-ghost',
            actionLabel: 'Delete'
        },
        'load_balancer': { 
            icon: 'share_windows', 
            iconBgClass: 'bg-primary/10', 
            iconColorClass: 'text-primary',
            severity: 'HIGH',
            badgeClass: 'bg-primary/10 text-primary border-primary/20',
            actionLabel: 'Delete'
        },
    };
    return mapping[props.resource.resource_type] || { 
        icon: 'help', 
        iconBgClass: 'bg-slate-500/10', 
        iconColorClass: 'text-slate-500',
        severity: 'UNKNOWN',
        badgeClass: 'bg-surface text-content-variant border-border-ghost',
        actionLabel: 'Resolve'
    };
});

const typeLabel = computed(() => {
    const labels = {
        'ebs_volume': 'Unattached EBS Volume',
        'elastic_ip': 'Unassociated Elastic IP',
        'nat_gateway': 'Idle NAT Gateway',
        'snapshot': 'Stale Snapshot',
        'load_balancer': 'Idle Load Balancer',
    };
    return labels[props.resource.resource_type] || 'Unknown Resource';
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};
</script>
