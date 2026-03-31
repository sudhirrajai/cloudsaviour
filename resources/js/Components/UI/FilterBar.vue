<template>
    <div class="bg-[rgba(20,27,46,1)] px-6 py-4 flex flex-wrap items-center gap-4 border-b border-slate-800/50">
        <span class="text-[11px] font-mono text-slate-500 uppercase tracking-widest mr-2">Filter by:</span>
        <button
            v-for="filter in filters"
            :key="filter.value"
            :class="[
                'px-4 py-1.5 rounded-full text-[10px] font-mono border transition-colors',
                modelValue === filter.value
                    ? 'bg-blue-500/10 text-blue-400 border-blue-500/30'
                    : 'hover:bg-slate-800/50 text-content-variant border-slate-700/30'
            ]"
            @click="$emit('update:modelValue', filter.value)"
        >
            {{ filter.label }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    filters: { type: Array, required: true },
    modelValue: { type: String, default: 'all' },
});
defineEmits(['update:modelValue']);
</script>
