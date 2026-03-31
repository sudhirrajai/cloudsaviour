<template>
    <button
        :type="type"
        :class="[
            'inline-flex items-center justify-center px-4 py-2 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-canvas disabled:opacity-50 disabled:cursor-not-allowed',
            variantClasses[variant]
        ]"
    >
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'tertiary', 'danger'].includes(value),
    },
});

const variantClasses = {
    primary: 'bg-primary text-white hover:bg-blue-600 focus:ring-primary shadow-glow',
    secondary: 'bg-transparent border border-border-ghost text-content hover:bg-surface-elevated hover:border-border-active focus:ring-border-active',
    tertiary: 'bg-transparent text-primary-dim hover:text-primary focus:ring-primary',
    danger: 'bg-error text-white hover:bg-red-600 focus:ring-error',
};
</script>
