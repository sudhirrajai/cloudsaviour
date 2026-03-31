<template>
    <div :class="[
        'inline-flex items-center rounded-sm px-2 py-0.5 text-[0.6875rem] font-mono font-medium tracking-wide',
        variantClasses[variant]
    ]">
        <slot />
    </div>
</template>

<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'success', 'warning', 'danger', 'info'].includes(value),
    },
});

const variantClasses = {
    default: 'bg-surface-elevated text-content-variant border border-border-ghost',
    success: 'bg-tertiary/10 text-tertiary border border-tertiary/20',
    warning: 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
    danger: 'bg-error/10 text-error border border-error/20',
    info: 'bg-primary/10 text-primary-dim border border-primary/20',
};
</script>
