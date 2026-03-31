<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :class="[
            'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:ring-offset-2 focus:ring-offset-canvas',
            modelValue ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]' : 'bg-slate-700'
        ]"
        @click="$emit('update:modelValue', !modelValue)"
    >
        <span
            :class="[
                'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out',
                modelValue ? 'translate-x-4' : 'translate-x-0'
            ]"
        ></span>
    </button>
</template>

<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
});
defineEmits(['update:modelValue']);
</script>
