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
    default: 'bg-slate-100 text-slate-700 border border-slate-300',
    success: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    warning: 'bg-amber-50 text-amber-700 border border-amber-200',
    danger: 'bg-rose-50 text-rose-700 border border-rose-200',
    info: 'bg-sky-50 text-sky-700 border border-sky-200',
};
</script>
