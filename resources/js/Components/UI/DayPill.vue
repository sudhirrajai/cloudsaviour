<template>
    <span :class="[
        'inline-flex items-center justify-center w-8 h-6 rounded text-[9px] font-mono font-medium transition-colors',
        active
            ? 'bg-primary/10 text-primary border border-primary/30'
            : 'text-content-variant border border-border-ghost'
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
