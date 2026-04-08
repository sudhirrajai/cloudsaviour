<template>
    <TransitionGroup 
        tag="div" 
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
        class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none"
    >
        <div v-if="success" key="success" class="pointer-events-auto w-full bg-white border border-slate-900 rounded-xl p-4 shadow-2xl flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-white text-[18px]">check_circle</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-mono font-bold text-slate-900 uppercase tracking-wider mb-0.5">Operation Success</p>
                <p class="text-xs text-slate-600 leading-relaxed font-medium">{{ success }}</p>
            </div>
            <button @click="$emit('clear-success')" class="text-slate-400 hover:text-slate-900 transition-colors">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>

        <div v-if="error" key="error" class="pointer-events-auto w-full bg-white border border-slate-900 rounded-xl p-4 shadow-2xl flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-white text-[18px]">error</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-mono font-bold text-slate-900 uppercase tracking-wider mb-0.5">System Alert</p>
                <p class="text-xs text-slate-600 leading-relaxed font-medium">{{ error }}</p>
            </div>
            <button @click="$emit('clear-error')" class="text-slate-400 hover:text-slate-900 transition-colors">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
    </TransitionGroup>
</template>

<script setup>
defineProps({
    success: String,
    error: String
});

defineEmits(['clear-success', 'clear-error']);
</script>
