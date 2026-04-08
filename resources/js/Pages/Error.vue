<script setup>
import { computed } from 'vue';
import { Link, Head } from '@inertiajs/vue3';

const props = defineProps({
    status: Number
});

const title = computed(() => {
    return {
        404: 'SYSTEM LOST',
        403: 'ACCESS DENIED',
        500: 'CORE FAILURE',
        503: 'MAINTENANCE MODE'
    }[props.status] || 'UNKNOWN ERROR';
});

const description = computed(() => {
    return {
        404: "The endpoint you're trying to reach does not exist in our vector maps.",
        403: "Your current credentials do not have the required clearance level.",
        500: "An internal surge has occurred. Our team is already stabilizing the core.",
        503: "We are currently retrofitting the system. Please stand by."
    }[props.status] || 'An unexpected error occurred.';
});
</script>

<template>
    <Head :title="title" />
    
    <div class="min-h-screen bg-canvas flex items-center justify-center p-6 font-sans antialiased overflow-hidden relative">
        <!-- Background Decorative Elements -->
        <div class="absolute -top-1/4 -left-1/4 w-1/2 h-1/2 bg-[#3b82f6]/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute -bottom-1/4 -right-1/4 w-1/2 h-1/2 bg-[#8b5cf6]/10 rounded-full blur-[120px] pointer-events-none"></div>
        
        <!-- Scanline Effect -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-50 bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] bg-[length:100%_4px,3px_100%]"></div>

        <div class="max-w-xl w-full text-center relative z-10 space-y-12">
            <!-- Glitch Status Code -->
            <div class="relative inline-block group">
                <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full scale-110 group-hover:bg-primary/30 transition-all duration-700"></div>
                <h1 class="text-[140px] md:text-[220px] font-black leading-none tracking-tighter text-content select-none relative drop-shadow-[0_0_20px_rgba(0,0,0,0.05)]">
                    {{ status }}
                </h1>
            </div>

            <!-- Error Details -->
            <div class="space-y-4">
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                    <span class="text-[10px] font-mono text-red-500 uppercase tracking-widest">Diagnostic Protocol {{ status }}</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-bold text-content tracking-tight uppercase">{{ title }}</h2>
                <p class="text-content-variant text-sm md:text-lg max-w-sm mx-auto leading-relaxed font-light italic opacity-80">
                    "{{ description }}"
                </p>
            </div>

            <!-- Action -->
            <div class="pt-8">
                <Link href="/" 
                    class="inline-flex items-center gap-3 bg-primary text-white px-10 py-4 rounded-xl font-bold transition-all hover:bg-primary-dim hover:scale-105 active:scale-95 group shadow-glow"
                >
                    <span class="material-symbols-outlined text-xl transition-transform group-hover:-translate-x-1">rocket_launch</span>
                    RE-INITIALIZE
                </Link>
            </div>

            <!-- Footer Meta -->
            <div class="pt-16 flex flex-col items-center gap-4">
                <div class="flex gap-2">
                    <div v-for="i in 12" :key="i" class="w-1 h-3 bg-slate-800 rounded-sm" :class="{'bg-[#3b82f6]/40 animate-pulse': i < 5}"></div>
                </div>
                <span class="text-[9px] font-mono text-slate-600 uppercase tracking-[0.4em]">Secure Node: CLOUD_SAVIOUR_V2</span>
            </div>
        </div>

        <!-- Floating Particles Logic (Simplified for performance) -->
        <div class="fixed inset-0 pointer-events-none opacity-20">
            <div v-for="i in 20" :key="i" 
                class="absolute animate-float-slow"
                :style="{ 
                    left: `${Math.random() * 100}%`, 
                    top: `${Math.random() * 100}%`,
                    animationDelay: `${Math.random() * 5}s`,
                    fontSize: `${Math.random() * 20 + 5}px`
                }"
            >
                <span class="text-slate-900/10 font-mono">{{ Math.random() > 0.5 ? '0' : '1' }}</span>
            </div>
        </div>
    </div>
</template>

<style>
@keyframes float-slow {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -50px) rotate(10deg); }
    66% { transform: translate(-30px, 50px) rotate(-10deg); }
}

.animate-float-slow {
    animation: float-slow 15s ease-in-out infinite;
}

body {
    margin: 0;
    padding: 0;
}
</style>
