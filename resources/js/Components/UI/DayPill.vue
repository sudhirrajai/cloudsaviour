<template>
    <span :class="[
        'inline-flex items-center justify-center w-8 h-6 rounded text-[9px] font-mono font-bold transition-all',
        active
            ? 'bg-primary text-white border-transparent shadow-[0_0_12px_rgba(59,130,246,0.5)] scale-105'
            : 'text-slate-600 border border-white/5 bg-white/5'
    ]">
        {{ label }}
    </span>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    active: { type: Boolean, default: false },
});
</script>
