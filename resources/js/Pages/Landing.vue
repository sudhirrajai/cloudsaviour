<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useScroll } from '@vueuse/core';
import { 
    Cloud, 
    ShieldCheck, 
    Zap, 
    BarChart3, 
    Clock, 
    Search,
    ChevronRight,
    Github,
    Twitter,
    Globe,
    ArrowRight,
    Cpu,
    Database,
    ZapOff,
    CheckCircle2
} from 'lucide-vue-next';

// 3D Tilt Logic for Dashboard Preview
const dashboardRef = ref(null);
const { y: scrollY } = useScroll(window);

const features = [
    {
        title: 'Smart Scheduler',
        description: 'Automate EC2 and RDS instances with precision. Save up to 70% on idle costs.',
        icon: Clock,
        color: 'text-indigo-400',
        bg: 'bg-indigo-400/10'
    },
    {
        title: 'Cost Analytics',
        description: 'Real-time AWS Cost Explorer integration. Visualize your savings and spending trends.',
        icon: BarChart3,
        color: 'text-emerald-400',
        bg: 'bg-emerald-400/10'
    },
    {
        title: 'Idle Scanner',
        description: 'Automatically detect unattached EBS volumes or idle instances and take action.',
        icon: Search,
        color: 'text-blue-400',
        bg: 'bg-blue-400/10'
    },
    {
        title: 'Security Audits',
        description: 'Every automated action is logged with detailed metadata for full compliance.',
        icon: ShieldCheck,
        color: 'text-purple-400',
        bg: 'bg-purple-400/10'
    },
    {
        title: 'Instance Actions',
        description: 'Start, Stop, Terminate, or Delete resources directly from your unified dashboard.',
        icon: Zap,
        color: 'text-rose-400',
        bg: 'bg-rose-400/10'
    },
    {
        title: 'Multi-Workspace',
        description: 'Manage multiple AWS accounts and regions from a single professional interface.',
        icon: Cloud,
        color: 'text-indigo-400',
        bg: 'bg-indigo-400/10'
    }
];

const roadmap = [
    {
        phase: 'Phase 1',
        title: 'AWS Core Integration',
        status: 'Live',
        items: ['EC2/RDS Management', 'Smart Scheduling', 'Cost Dashboard']
    },
    {
        phase: 'Phase 2',
        title: 'Expansion & AI',
        status: 'In Progress',
        items: ['Lambda Support', 'Slack Notifications', 'AI Cost Insights']
    },
    {
        phase: 'Phase 3',
        title: 'Multi-Cloud',
        status: 'Planned',
        items: ['Azure & GCP', 'Mobile App', 'Auto-Scaling Ops']
    }
];

</script>

<template>
    <Head title="CloudSaviour - Automate & Save" />

    <div class="min-h-screen bg-[#020617] text-slate-200 selection:bg-indigo-500/30 font-['Plus_Jakarta_Sans'] overflow-x-hidden">
        
        <!-- Animated Background Mesh -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] bg-indigo-600/10 blur-[140px] rounded-full animate-pulse"></div>
            <div class="absolute top-[10%] -right-[15%] w-[50%] h-[50%] bg-emerald-600/5 blur-[120px] rounded-full animate-pulse capitalize" style="animation-delay: 2s"></div>
            <div class="absolute bottom-[-10%] left-[10%] w-[60%] h-[60%] bg-blue-600/5 blur-[140px] rounded-full animate-pulse" style="animation-delay: 4s"></div>
        </div>

        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-[100] transition-all duration-300 bg-slate-950/20 backdrop-blur-xl border-b border-white/5">
            <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(79,70,229,0.4)]">
                        <Cloud class="w-6 h-6 text-white" />
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight text-white">
                        Cloud<span class="text-indigo-400">Saviour</span>
                    </span>
                </div>

                <div class="hidden md:flex items-center gap-10">
                    <nav class="flex items-center gap-8 text-sm font-semibold text-slate-400">
                        <a href="#features" class="hover:text-white transition-colors">Features</a>
                        <a href="#roadmap" class="hover:text-white transition-colors">Roadmap</a>
                        <Link :href="route('login')" class="hover:text-white transition-colors">Sign In</Link>
                    </nav>
                    <Link :href="route('register')" class="px-6 py-2.5 bg-white text-black rounded-full font-bold text-sm hover:scale-105 active:scale-95 transition-all shadow-xl">
                        Get Started
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-16 z-10 md:pt-40 md:pb-20">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <div 
                    v-motion
                    :initial="{ opacity: 0, y: 20 }"
                    :enter="{ opacity: 1, y: 0, transition: { duration: 800 } }"
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-[10px] md:text-xs font-bold mb-6 md:mb-8 shadow-[0_0_20px_rgba(99,102,241,0.1)]"
                >
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-indigo-500"></span>
                    </span>
                    BETA ACCESS NOW OPEN
                </div>

                <h1 
                    v-motion
                    :initial="{ opacity: 0, y: 30 }"
                    :enter="{ opacity: 1, y: 0, transition: { delay: 100, duration: 800 } }"
                    class="text-4xl md:text-6xl font-[800] tracking-tight text-white mb-6 md:mb-8 leading-[1.05]"
                >
                    Automate Cloud.<br/>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-emerald-400 to-blue-400">
                        Minimize Waste.
                    </span>
                </h1>

                <p 
                    v-motion
                    :initial="{ opacity: 0, y: 20 }"
                    :enter="{ opacity: 1, y: 0, transition: { delay: 200, duration: 800 } }"
                    class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto mb-10 md:mb-12 font-medium leading-relaxed"
                >
                    The professional command center for AWS cost optimization. CloudSaviour by 
                    <span class="text-white font-bold underline decoration-indigo-500 underline-offset-4">vmcore.in</span> 
                    automates your infrastructure management so you can focus on building.
                </p>

                <div 
                    v-motion
                    :initial="{ opacity: 0, scale: 0.9 }"
                    :enter="{ opacity: 1, scale: 1, transition: { delay: 300, duration: 800 } }"
                    class="flex flex-col sm:flex-row items-center justify-center gap-4 md:gap-6"
                >
                    <Link :href="route('register')" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl font-extrabold text-lg hover:shadow-[0_0_50px_-10px_rgba(79,70,229,0.6)] hover:-translate-y-1 transition-all group flex items-center justify-center gap-3">
                        Start Free Trial
                        <ArrowRight class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                    </Link>
                    <a href="https://vmcore.in" class="w-full sm:w-auto px-8 py-4 bg-slate-900/50 backdrop-blur-xl border border-white/5 text-white rounded-2xl font-extrabold text-lg hover:bg-slate-900 transition-all text-center">
                        View Roadmap
                    </a>
                </div>

                <!-- 3D Dashboard Mockup -->
                <div 
                    ref="dashboardRef"
                    v-motion
                    :initial="{ opacity: 0, rotateX: 20, scale: 0.8, y: 100 }"
                    :enter="{ opacity: 1, rotateX: 0, scale: 1, y: 0, transition: { delay: 400, duration: 1200, ease: 'easeOut' } }"
                    class="mt-20 perspective-2000 hidden md:block"
                >
                    <div 
                        class="relative transition-transform duration-700 ease-out preserve-3d group"
                        :style="{ transform: `rotateX(${Math.max(0, 15 - scrollY / 50)}deg) translateY(${Math.min(0, scrollY / 10)}px)` }"
                    >
                        <div class="absolute -inset-2 bg-gradient-to-r from-indigo-600/30 via-emerald-600/20 to-blue-600/30 blur-3xl rounded-[2.5rem] group-hover:opacity-100 opacity-50 transition-opacity"></div>
                        <div class="relative bg-slate-950 border border-white/10 rounded-[2rem] overflow-hidden shadow-2xl aspect-video">
                            <img 
                                src="/images/dashboard-preview.png" 
                                alt="CloudSaviour Dashboard" 
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="features" class="py-20 md:py-32 relative z-10">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16 md:mb-24">
                    <span class="text-indigo-400 font-black tracking-widest text-[10px] md:text-xs uppercase mb-4 block">Engineered for Efficiency</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-6 tracking-tight">Standard Performance.</h2>
                    <p class="text-slate-400 text-base md:text-lg max-w-2xl mx-auto font-medium">Control your instances, monitor costs, and automate everything with our deep-cloud integration.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    <div v-for="(feature, idx) in features" :key="feature.title" 
                        v-motion-slide-visible-bottom
                        :delay="idx * 50"
                        class="group p-8 md:p-10 bg-slate-900/30 backdrop-blur-xl border border-white/5 rounded-[2rem] md:rounded-[2.5rem] hover:bg-slate-900/50 hover:border-indigo-500/30 transition-all duration-500"
                    >
                        <div :class="[feature.bg, 'w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-2xl flex items-center justify-center mb-6 md:mb-8 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-6']">
                            <component :is="feature.icon" :class="['w-6 h-6 md:w-8 md:h-8', feature.color]" />
                        </div>
                        <h3 class="text-lg md:text-xl font-extrabold text-white mb-3 md:mb-4 tracking-tight group-hover:text-indigo-300 transition-colors">{{ feature.title }}</h3>
                        <p class="text-slate-500 text-sm md:text-base font-medium leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats / Benefit Section -->
        <section class="py-20 md:py-32 border-y border-white/5 bg-slate-950/40 relative z-10 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-16 text-center">
                    <div v-motion-slide-visible-bottom>
                        <div class="text-5xl md:text-6xl font-black text-white mb-4 tracking-tighter">70%</div>
                        <div class="text-indigo-400 font-bold uppercase tracking-widest text-[10px] md:text-xs">Average Savings</div>
                    </div>
                    <div v-motion-slide-visible-bottom :delay="100">
                        <div class="text-5xl md:text-6xl font-black text-white mb-4 tracking-tighter">0M</div>
                        <div class="text-emerald-400 font-bold uppercase tracking-widest text-[10px] md:text-xs">Wasted Resources</div>
                    </div>
                    <div v-motion-slide-visible-bottom :delay="200">
                        <div class="text-5xl md:text-6xl font-black text-white mb-4 tracking-tighter">100%</div>
                        <div class="text-blue-400 font-bold uppercase tracking-widest text-[10px] md:text-xs">Automated Compliance</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Roadmap Header -->
        <section id="roadmap" class="py-20 md:py-40 relative z-10">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col lg:flex-row gap-12 md:gap-20 items-center">
                    <div class="flex-1">
                        <h2 class="text-3xl md:text-4xl font-black text-white mb-6 md:mb-8 leading-tight tracking-tighter">The future of cloud governance.</h2>
                        <p class="text-slate-400 text-base md:text-lg font-medium mb-10 md:mb-12">We're not just stopping at EC2. Our vision is to be the unified control plane for your entire multi-cloud estate.</p>
                        
                        <div class="space-y-6">
                            <div v-for="step in roadmap" :key="step.phase" 
                                class="p-6 bg-slate-900/40 border border-white/5 rounded-2xl flex items-center gap-6 group hover:border-white/10 transition-colors"
                            >
                                <div class="w-12 h-12 rounded-xl bg-indigo-600/10 flex items-center justify-center font-bold text-indigo-400 shrink-0">
                                    {{ step.phase.split(' ')[1] }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-lg font-bold text-white">{{ step.title }}</h4>
                                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full bg-slate-800 text-slate-500">
                                            {{ step.status }}
                                        </span>
                                    </div>
                                    <p class="text-slate-500 text-sm font-medium mt-1">{{ step.items.join(' • ') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 relative">
                        <div class="aspect-square bg-indigo-600/20 rounded-[3rem] blur-3xl absolute inset-0"></div>
                        <div class="relative bg-slate-900 border border-white/10 rounded-[4rem] p-12 aspect-square flex items-center justify-center overflow-hidden">
                            <Layers class="w-48 h-48 text-indigo-500/20 absolute rotate-12 -top-10 -right-10" />
                            <div class="text-center relative z-10">
                                <CheckCircle2 class="w-24 h-24 text-emerald-500 mx-auto mb-8 shadow-[0_0_40px_rgba(16,185,129,0.3)]" />
                                <h3 class="text-3xl font-black text-white mb-4">Phase 1 Live</h3>
                                <p class="text-slate-400 font-medium max-w-xs mx-auto">Start optimizing your AWS footprint today with our core automation tools.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-20 md:py-40 relative z-10">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <div class="p-10 md:p-20 bg-gradient-to-br from-indigo-900/20 via-slate-900/40 to-slate-950 border border-white/10 rounded-[2.5rem] md:rounded-[4rem] relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(79,70,229,0.1),transparent)]"></div>
                    <h2 class="text-3xl md:text-5xl font-black text-white mb-8 md:mb-10 tracking-tight leading-tight">Ready to stop burning your cloud budget?</h2>
                    <Link :href="route('register')" class="inline-flex items-center gap-3 md:gap-4 px-8 py-4 md:px-12 md:py-6 bg-white text-black rounded-[1.5rem] md:rounded-[2rem] font-black text-xl md:text-2xl hover:scale-105 active:scale-95 transition-all shadow-2xl">
                        Get Beta Access
                        <ArrowRight class="w-6 h-6 md:w-8 md:h-8" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="pt-20 pb-12 border-t border-white/5 relative z-10 bg-slate-950/50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-start gap-12 mb-20">
                    <div class="max-w-md">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                                <Cloud class="w-5 h-5 text-white" />
                            </div>
                            <span class="text-xl font-black text-white tracking-tight">CloudSaviour</span>
                        </div>
                        <p class="text-slate-500 text-lg font-medium leading-relaxed mb-8">
                            A premium enterprise-grade cloud automation platform by 
                            <a href="https://vmcore.in" class="text-white font-bold hover:underline">vmcore.in</a>. 
                            Built for developers who value performance and efficiency.
                        </p>
                        <div class="flex gap-4">
                            <a v-for="icon in [Github, Twitter, Globe]" :key="icon" href="#" class="w-12 h-12 rounded-xl bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:border-white/10 transition-all">
                                <component :is="icon" class="w-5 h-5" />
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-16">
                        <div>
                            <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs">Product</h4>
                            <ul class="space-y-4 text-slate-500 font-bold text-sm">
                                <li><a href="#features" class="hover:text-indigo-400 transition-colors">Features</a></li>
                                <li><a href="#roadmap" class="hover:text-indigo-400 transition-colors">Roadmap</a></li>
                                <li><Link :href="route('login')" class="hover:text-indigo-400 transition-colors">Sign In</Link></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs">Resources</h4>
                            <ul class="space-y-4 text-slate-500 font-bold text-sm">
                                <li><a href="https://vmcore.in" class="hover:text-indigo-400 transition-colors">vmcore.in</a></li>
                                <li><a href="#" class="hover:text-indigo-400 transition-colors">API Docs</a></li>
                                <li><a href="#" class="hover:text-indigo-400 transition-colors">Support</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
                    <p class="text-slate-600 font-bold text-sm">
                        &copy; 2026 CloudSaviour. Product of vmcore.in
                    </p>
                    <div class="flex items-center gap-8 text-slate-600 font-bold text-sm">
                        <a href="#" class="hover:text-white transition-colors">Security</a>
                        <a href="#" class="hover:text-white transition-colors">Privacy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.perspective-2000 {
    perspective: 2000px;
}
.preserve-3d {
    transform-style: preserve-3d;
}
</style>
