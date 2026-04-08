<template>
    <span :class="[
        'inline-flex items-center justify-center w-8 h-6 rounded text-[9px] font-mono font-bold transition-all',
        active
            ? 'bg-slate-900 text-white border-transparent shadow-sm'
            : 'text-slate-500 border border-slate-300 bg-slate-50'
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
