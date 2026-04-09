<template>
    <div :class="[
        'bg-white p-6 rounded-xl border border-slate-900 hover:scale-[1.02] transition-all duration-300 shadow-sm group active:scale-[0.98]',
        borderClass
    ]">
        <div class="flex justify-between items-start mb-6">
            <span class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] opacity-70 group-hover:opacity-100 transition-opacity">{{ label }}</span>
            <div :class="['w-9 h-9 rounded-md flex items-center justify-center bg-slate-50 group-hover:scale-110 transition-transform shadow-sm border border-slate-200']">
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
    primary: { border: 'border-l-4 border-l-primary', icon: 'text-primary', value: 'text-slate-900' },
    secondary: { border: 'border-l-4 border-l-secondary', icon: 'text-secondary', value: 'text-slate-900' },
    tertiary: { border: 'border-l-4 border-l-tertiary', icon: 'text-tertiary', value: 'text-slate-900' },
    error: { border: 'border-l-4 border-l-error', icon: 'text-error', value: 'text-slate-900' },
    amber: { border: 'border-l-4 border-l-amber-500', icon: 'text-amber-500', value: 'text-slate-900' },
    white: { border: 'border-l-4 border-l-primary', icon: 'text-primary', value: 'text-slate-900' },
    grey: { border: 'border-l-4 border-l-slate-400', icon: 'text-slate-500', value: 'text-slate-900' },
};

const colorSet = colorMap[props.borderColor] || colorMap.primary;
const borderClass = colorSet.border;
const iconColorClass = colorSet.icon;
const valueColorClass = colorSet.value;
</script>
