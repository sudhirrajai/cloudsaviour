<template>
    <Transition name="modal-fade">
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-canvas/80 backdrop-blur-md" @click="handleCancel"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-lg bg-surface-elevated/90 border border-white/10 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-xl animate-in fade-in zoom-in duration-300">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-white/5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-error/10 flex items-center justify-center border border-error/20">
                        <span class="material-symbols-outlined text-error text-2xl">warning</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-display font-bold text-white">{{ title }}</h3>
                        <p class="text-[10px] font-mono text-slate-500 uppercase tracking-widest opacity-70">Security Checkpoint</p>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-8 py-8">
                    <p class="text-slate-300 text-sm leading-relaxed mb-6">
                        {{ message }}
                    </p>

                    <div v-if="confirmWord" class="space-y-4">
                        <div class="p-4 bg-error/5 border border-error/10 rounded-lg">
                            <p class="text-[11px] font-mono text-error font-medium leading-relaxed">
                                WARNING: This action is permanent and cannot be undone. To proceed, please type <span class="text-white font-bold bg-error/20 px-1.5 py-0.5 rounded">{{ confirmWord }}</span> in the field below.
                            </p>
                        </div>
                        
                        <div class="relative">
                            <input 
                                v-model="userInput" 
                                type="text" 
                                class="w-full bg-surface border border-white/10 rounded-lg px-4 py-3 text-white font-mono text-sm focus:outline-none focus:border-primary/50 transition-all placeholder:text-slate-600"
                                :placeholder="`Type ${confirmWord} here`"
                                @keyup.enter="handleConfirm"
                            />
                            <div v-if="userInput === confirmWord" class="absolute right-3 top-1/2 -translate-y-1/2 transition-all">
                                <span class="material-symbols-outlined text-tertiary text-lg">check_circle</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 py-6 bg-surface-elevated/30 flex items-center justify-end gap-3 border-t border-white/5">
                    <button 
                        @click="handleCancel"
                        class="px-5 py-2.5 rounded-lg text-[11px] font-mono uppercase tracking-widest text-slate-400 hover:text-white hover:bg-white/5 transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="handleConfirm"
                        :disabled="!isConfirmed"
                        class="px-6 py-2.5 rounded-lg text-[11px] font-mono uppercase tracking-widest font-bold flex items-center gap-2 transition-all disabled:opacity-30 disabled:cursor-not-allowed group"
                        :class="confirmButtonClass"
                    >
                        <span>{{ confirmText }}</span>
                        <span v-if="loading" class="material-symbols-outlined text-sm animate-spin">sync</span>
                        <span v-else class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">keyboard_double_arrow_right</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    title: { type: String, default: 'Confirm Action' },
    message: { type: String, default: 'Are you sure you want to proceed with this permanent action?' },
    confirmText: { type: String, default: 'Confirm' },
    confirmWord: { type: String, default: '' }, // If provided, user must type this to confirm
    loading: Boolean,
    variant: { type: String, default: 'error' } // error, primary, warning
});

const emit = defineEmits(['confirm', 'cancel']);

const userInput = ref('');

const isConfirmed = computed(() => {
    if (!props.confirmWord) return true;
    return userInput.value === props.confirmWord;
});

const confirmButtonClass = computed(() => {
    const variants = {
        error: 'bg-error text-white shadow-[0_0_20px_rgba(239,68,68,0.3)] hover:brightness-110 active:scale-95',
        primary: 'bg-primary text-white shadow-[0_0_20px_rgba(59,130,246,0.3)] hover:brightness-110 active:scale-95',
        warning: 'bg-amber-500 text-white shadow-[0_0_20px_rgba(245,158,11,0.3)] hover:brightness-110 active:scale-95'
    };
    return variants[props.variant] || variants.error;
});

const handleConfirm = () => {
    if (isConfirmed.value && !props.loading) {
        emit('confirm');
    }
};

const handleCancel = () => {
    if (!props.loading) {
        emit('cancel');
    }
};

// Reset input when modal opens/closes
watch(() => props.show, (newVal) => {
    if (!newVal) {
        userInput.value = '';
    }
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>
