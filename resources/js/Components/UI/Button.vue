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
    primary: 'bg-slate-900 text-white font-black border border-slate-900 uppercase tracking-widest hover:bg-slate-800 hover:shadow-[0_0_20px_rgba(37,99,235,0.15)] focus:ring-slate-900 shadow-md hover:scale-[1.02] transition-all duration-300',
    secondary: 'bg-white border border-slate-900 text-slate-900 font-black uppercase tracking-widest hover:bg-slate-50 focus:ring-slate-900 shadow-sm hover:scale-[1.02] transition-all duration-300',
    tertiary: 'bg-transparent text-slate-600 font-bold uppercase tracking-widest hover:text-slate-900 hover:bg-slate-50 rounded px-2 border border-transparent hover:border-slate-900 transition-all duration-300',
    danger: 'bg-red-600 text-white font-black border border-slate-900 uppercase tracking-widest hover:bg-red-700 focus:ring-red-600 shadow-md hover:scale-[1.02] transition-all duration-300',
};
</script>
