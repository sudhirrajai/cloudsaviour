<template>
    <div
        :class="[
            'relative inline-block rounded-full',
            sizeClasses[size],
            colorClasses[status]
        ]"
    >
        <!-- Inner core -->
        <span
            :class="[
                'absolute inset-0 rounded-full',
                colorClasses[status]
            ]"
        ></span>
        <!-- Outer glow -->
        <span
            v-if="glow"
            :class="[
                'absolute -inset-1 rounded-full opacity-40',
                glowClasses[status],
                pulse ? 'animate-pulse' : ''
            ]"
        ></span>
    </div>
</template>

<script setup>
const props = defineProps({
    status: {
        type: String,
        default: 'active',
        validator: (value) => ['active', 'error', 'warning', 'inactive'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    glow: {
        type: Boolean,
        default: true
    },
    pulse: {
        type: Boolean,
        default: false
    }
});

const sizeClasses = {
    sm: 'w-1.5 h-1.5',
    md: 'w-2.5 h-2.5',
    lg: 'w-3.5 h-3.5',
};

const colorClasses = {
    active: 'bg-tertiary',
    error: 'bg-error',
    warning: 'bg-amber-400',
    inactive: 'bg-content-variant',
};

const glowClasses = {
    active: 'bg-tertiary',
    error: 'bg-error',
    warning: 'bg-amber-400',
    inactive: 'bg-transparent',
};
</script>
