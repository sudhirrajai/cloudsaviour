<template>
    <div class="min-h-screen bg-canvas flex items-center justify-center p-6 antialiased selection:bg-primary/30 selection:text-white">
        <!-- Background grid -->
        <div class="fixed inset-0 pointer-events-none z-0" style="background-image: linear-gradient(to right, rgba(15, 23, 42, 0.02) 1px, transparent 1px), linear-gradient(to bottom, rgba(15, 23, 42, 0.02) 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Logo -->
            <div class="text-center mb-10">
                <h1 class="font-display font-black text-4xl tracking-tighter text-slate-900 mb-2">CloudSaviour</h1>
                <p class="text-slate-500 font-mono text-[10px] uppercase tracking-[0.2em] font-bold">Sign in to your account</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-xl border border-slate-900 p-8 relative overflow-hidden shadow-lg hover:scale-[1.01] transition-transform duration-300">
                <form @submit.prevent="submit">
                    <div class="space-y-5">
                        <!-- Email -->
                        <div>
                            <label class="block text-[10px] font-mono text-content-variant uppercase tracking-widest mb-2">Email</label>
                            <input v-model="form.email" type="email" autofocus
                                class="w-full bg-white text-slate-900 text-sm font-mono border border-slate-900 rounded-sm px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900/20 placeholder-slate-400 transition-colors"
                                placeholder="you@example.com" />
                            <p v-if="form.errors.email" class="text-error text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-[10px] font-mono text-content-variant uppercase tracking-widest mb-2">Password</label>
                            <input v-model="form.password" type="password"
                                class="w-full bg-white text-slate-900 text-sm font-mono border border-slate-900 rounded-sm px-4 py-3 focus:outline-none focus:ring-2 focus:ring-slate-900/20 placeholder-slate-400 transition-colors"
                                placeholder="••••••••" />
                            <p v-if="form.errors.password" class="text-error text-xs mt-1">{{ form.errors.password }}</p>
                        </div>

                        <!-- Remember -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input v-model="form.remember" type="checkbox" class="w-4 h-4 rounded border border-slate-900 bg-white text-slate-900 focus:ring-slate-900/30 transition-all group-hover:bg-slate-50 shadow-sm" />
                                <span class="text-sm text-slate-800 font-bold">Remember me</span>
                            </label>
                        </div>

                        <!-- Submit -->
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-slate-900 text-white px-6 py-4 rounded-lg font-mono text-[11px] uppercase tracking-[0.2em] font-black border border-slate-900 hover:bg-slate-800 hover:shadow-[0_0_20px_rgba(15,23,42,0.15)] hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 shadow-md disabled:opacity-50">
                            {{ form.processing ? 'Processing...' : 'Sign In' }}
                        </button>
                    </div>
                </form>

                <!-- Grid overlay -->
                <div class="absolute inset-0 pointer-events-none opacity-[0.02] z-0" style="background-image: radial-gradient(#0f172a 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
            </div>

            <!-- Register link -->
            <p class="text-center mt-6 text-sm text-content-variant">
                Don't have an account?
                <Link href="/register" class="text-slate-900 hover:underline font-bold">Create one</Link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>
