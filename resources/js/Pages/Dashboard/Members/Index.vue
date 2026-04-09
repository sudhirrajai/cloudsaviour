<template>
    <DashboardLayout currentPage="members">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-slate-900 mb-2">Team Members</h1>
                <p class="text-content-variant font-sans">Manage your workspace team and access permissions.</p>
            </div>
            <button class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-sm font-mono text-[11px] uppercase tracking-wider font-bold flex items-center gap-3 active:scale-95 transition-all shadow-glow">
                <span class="material-symbols-outlined text-sm">person_add</span>
                Invite Member
            </button>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard label="Total Members" :value="String(members.length)" sublabel="ACTIVE USERS" icon="groups" borderColor="primary" />
            <StatCard label="Admins" :value="String(adminCount)" sublabel="FULL ACCESS" icon="admin_panel_settings" borderColor="secondary" />
            <StatCard label="Developers" :value="String(devCount)" sublabel="LIMITED ACCESS" icon="code" borderColor="tertiary" />
            <StatCard label="Pending Invites" :value="String(invitations.length)" sublabel="AWAITING RESPONSE" icon="mail" borderColor="amber" />
        </div>

        <!-- Members Table -->
        <section class="bg-white rounded-lg border border-slate-900 overflow-hidden relative mb-10 shadow-ambient">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-900 flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-[20px] shadow-glow">groups</span>
                <span class="text-sm font-semibold text-slate-900 tracking-wide">Active Workspace Members</span>
                <span class="bg-primary/20 text-primary text-[10px] font-mono px-2 py-0.5 rounded-full border border-primary/30">{{ members.length }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead class="bg-slate-50 text-[10px] font-mono text-slate-900 uppercase tracking-[0.2em] border-b border-slate-900">
                        <tr>
                            <th class="px-6 py-4 font-normal">Member</th>
                            <th class="px-6 py-4 font-normal">Contact Information</th>
                            <th class="px-6 py-4 font-normal">Access Level</th>
                            <th class="px-6 py-4 font-normal">Activity Status</th>
                            <th class="px-6 py-4 font-normal text-right">Management</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="member in members" :key="member.id" class="group hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold text-white border border-slate-200 shadow-sm group-hover:scale-105 transition-transform', avatarBg(member.role)]">
                                        {{ member.initials }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-slate-900 group-hover:text-primary transition-colors">{{ member.name }}</span>
                                        <span v-if="member.id === $page.props.auth.user.id" class="text-[9px] font-mono text-primary uppercase tracking-widest mt-0.5 font-bold">You</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-xs text-slate-500 opacity-80">{{ member.email }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-mono border font-bold tracking-wider',
                                    roleBadge(member.role)
                                ]">
                                    <span v-if="member.role === 'owner'" class="material-symbols-outlined text-[14px]">stars</span>
                                    <span v-else-if="member.role === 'admin'" class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                                    {{ member.role.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-tertiary shadow-glow shadow-tertiary/50"></span>
                                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest opacity-70">Active Now</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <template v-if="member.role !== 'owner' && member.id !== $page.props.auth.user.id">
                                        <select @change="updateRole(member.id, $event.target.value)" class="bg-white text-slate-900 text-[10px] font-mono border border-slate-300 rounded-lg px-3 py-1.5 appearance-none cursor-pointer focus:outline-none focus:border-primary/50 transition-all hover:bg-slate-50 shadow-sm">
                                            <option v-for="role in ['admin', 'developer', 'viewer']" :key="role" :value="role" :selected="member.role === role">{{ role.toUpperCase() }}</option>
                                        </select>
                                        <button @click="removeMember(member.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-error/60 hover:text-error hover:bg-error/10 transition-all border border-transparent hover:border-error/20 active:scale-90">
                                            <span class="material-symbols-outlined text-[18px]">person_remove</span>
                                        </button>
                                    </template>
                                    <span v-else class="text-[10px] font-mono text-slate-600 uppercase tracking-widest">Permanent</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>

        <!-- Pending Invitations -->
        <section v-if="invitations.length" class="bg-white rounded-lg border border-slate-900 overflow-hidden relative shadow-ambient mb-10">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-900 flex items-center gap-3">
                <span class="material-symbols-outlined text-amber-500 text-[20px]">mail</span>
                <span class="text-sm font-semibold text-slate-900 tracking-wide">Pending Invitations</span>
                <span class="bg-amber-500/10 text-amber-500 text-[10px] font-mono px-2 py-0.5 rounded-full border border-amber-500/20">{{ invitations.length }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead class="bg-slate-50 text-[10px] font-mono text-slate-900 uppercase tracking-[0.2em] border-b border-slate-900">
                        <tr>
                            <th class="px-6 py-4 font-normal">Invitation Email</th>
                            <th class="px-6 py-4 font-normal">Assigned Role</th>
                            <th class="px-6 py-4 font-normal">Inviter</th>
                            <th class="px-6 py-4 font-normal">Expiration</th>
                            <th class="px-6 py-4 font-normal text-right">Management</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="inv in invitations" :key="inv.id" class="group hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-5"><span class="font-mono text-sm text-slate-900 font-bold">{{ inv.email }}</span></td>
                            <td class="px-6 py-5">
                                <span :class="['inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono border tracking-wider font-bold', roleBadge(inv.role)]">
                                    {{ inv.role.toUpperCase() }}
                                </span>
                            </td>
                            <td class="px-6 py-5"><span class="text-sm text-slate-400">{{ inv.inviter?.name || 'Identity System' }}</span></td>
                            <td class="px-6 py-5"><span class="font-mono text-[10px] text-slate-500 uppercase tracking-widest">{{ inv.expires_at }}</span></td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button class="bg-primary text-white px-4 py-2 rounded-lg text-[10px] font-mono uppercase tracking-widest font-bold transition-all active:scale-95 shadow-sm">Resend</button>
                                    <button class="bg-white hover:bg-slate-50 text-error border border-slate-200 px-4 py-2 rounded-lg text-[10px] font-mono uppercase tracking-widest font-bold transition-all active:scale-95 shadow-sm">Revoke</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';

const props = defineProps({
    members: {
        type: Array,
        required: true
    },
    invitations: {
        type: Array,
        required: true
    }
});

const adminCount = computed(() => props.members.filter(m => m.role === 'admin').length);
const devCount = computed(() => props.members.filter(m => m.role === 'developer').length);

const roleBadge = (role) => ({
    owner: 'bg-secondary/10 text-secondary border-secondary/20',
    admin: 'bg-primary/10 text-primary border-primary/20',
    developer: 'bg-tertiary/10 text-tertiary border-tertiary/20',
    viewer: 'bg-surface-elevated text-content-variant border-border-ghost',
}[role] || 'bg-surface-elevated text-content-variant border-border-ghost');

const avatarBg = (role) => ({
    owner: 'bg-secondary/60',
    admin: 'bg-primary/60',
    developer: 'bg-tertiary/60',
    viewer: 'bg-slate-600/60',
}[role] || 'bg-slate-600/60');

const updateRole = (userId, role) => {
    router.post(`/dashboard/members/${userId}/role`, { role }, {
        preserveScroll: true
    });
};

const removeMember = (userId) => {
    if (confirm('Are you sure you want to remove this member?')) {
        router.delete(`/dashboard/members/${userId}`, {
            preserveScroll: true
        });
    }
};
</script>

