<template>
    <div :class="[
        'bg-surface-elevated/40 backdrop-blur-sm p-6 rounded-lg border border-white/5 hover:bg-surface-elevated/60 transition-all shadow-sm group active:scale-[0.98]',
        borderClass
    ]">
        <div class="flex justify-between items-start mb-6">
            <span class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] opacity-70 group-hover:opacity-100 transition-opacity">{{ label }}</span>
            <div :class="['w-9 h-9 rounded-md flex items-center justify-center bg-white/5 group-hover:scale-110 transition-transform shadow-glass border border-white/5']">
                <span :class="['material-symbols-outlined text-[20px]', iconColorClass]">{{ icon }}</span>
            </div>
        </div>
        <div :class="['text-4xl font-display font-bold tracking-tight mb-2', valueColorClass]">
            <slot>{{ value }}</slot>
        </div>
        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-widest opacity-60">{{ sublabel }}</div>
    </div>
</template>

<script setup>
const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], default: '' },
    sublabel: { type: String, default: '' },
    icon: { type: String, default: 'info' },
    borderColor: { type: String, default: 'primary' },
    valueColor: { type: String, default: 'white' },
});

const colorMap = {
    primary: { border: 'border-primary', icon: 'text-primary', value: 'text-primary' },
    secondary: { border: 'border-secondary', icon: 'text-secondary', value: 'text-secondary' },
    tertiary: { border: 'border-tertiary', icon: 'text-tertiary', value: 'text-tertiary' },
    error: { border: 'border-error', icon: 'text-error', value: 'text-error' },
    amber: { border: 'border-amber-500', icon: 'text-amber-500', value: 'text-amber-400' },
    white: { border: 'border-primary', icon: 'text-primary', value: 'text-white' },
    grey: { border: 'border-slate-500', icon: 'text-slate-500', value: 'text-content-variant' },
};

const colorSet = colorMap[props.borderColor] || colorMap.primary;
const borderClass = colorSet.border;
const iconColorClass = colorSet.icon;
const valueColorClass = props.valueColor === 'white' ? 'text-white' : colorSet.value;
</script>
