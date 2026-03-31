<template>
    <div class="min-h-screen bg-canvas flex items-center justify-center p-6 antialiased selection:bg-primary/30 selection:text-white">
        <div class="fixed inset-0 pointer-events-none z-0" style="background-image: linear-gradient(to right, rgba(59, 130, 246, 0.02) 1px, transparent 1px), linear-gradient(to bottom, rgba(59, 130, 246, 0.02) 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="w-full max-w-md relative z-10">
            <div class="text-center mb-10">
                <h1 class="font-display font-bold text-3xl tracking-tight text-white mb-2">CloudSaviour</h1>
                <p class="text-content-variant font-mono text-xs uppercase tracking-widest">Create your account</p>
            </div>

            <div class="bg-surface rounded-sm border border-slate-800/30 p-8 relative overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-[10px] font-mono text-content-variant uppercase tracking-widest mb-2">Full Name</label>
                            <input v-model="form.name" type="text" autofocus
                                class="w-full bg-surface-elevated text-white text-sm border border-border-ghost rounded-sm px-4 py-3 focus:outline-none focus:border-primary placeholder-content-variant transition-colors"
                                placeholder="Sudhir Kumar" />
                            <p v-if="form.errors.name" class="text-error text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-mono text-content-variant uppercase tracking-widest mb-2">Email</label>
                            <input v-model="form.email" type="email"
                                class="w-full bg-surface-elevated text-white text-sm font-mono border border-border-ghost rounded-sm px-4 py-3 focus:outline-none focus:border-primary placeholder-content-variant transition-colors"
                                placeholder="you@example.com" />
                            <p v-if="form.errors.email" class="text-error text-xs mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-mono text-content-variant uppercase tracking-widest mb-2">Password</label>
                            <input v-model="form.password" type="password"
                                class="w-full bg-surface-elevated text-white text-sm font-mono border border-border-ghost rounded-sm px-4 py-3 focus:outline-none focus:border-primary placeholder-content-variant transition-colors"
                                placeholder="••••••••" />
                            <p v-if="form.errors.password" class="text-error text-xs mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-mono text-content-variant uppercase tracking-widest mb-2">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password"
                                class="w-full bg-surface-elevated text-white text-sm font-mono border border-border-ghost rounded-sm px-4 py-3 focus:outline-none focus:border-primary placeholder-content-variant transition-colors"
                                placeholder="••••••••" />
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-primary text-white px-6 py-3 rounded-sm font-mono text-[11px] uppercase tracking-wider font-bold hover:brightness-110 active:scale-[0.98] transition-all shadow-[0_0_20px_rgba(59,130,246,0.2)] disabled:opacity-50">
                            {{ form.processing ? 'Creating account...' : 'Create Account' }}
                        </button>
                    </div>
                </form>

                <div class="absolute inset-0 pointer-events-none opacity-[0.02] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
            </div>

            <p class="text-center mt-6 text-sm text-content-variant">
                Already have an account?
                <Link href="/login" class="text-primary hover:underline font-medium">Sign in</Link>
            </p>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
