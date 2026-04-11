<template>
    <DashboardLayout currentPage="members">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-slate-900 mb-2">Team Members</h1>
                <p class="text-content-variant font-sans">Manage your workspace team and access permissions.</p>
            </div>
            <button 
                v-if="['owner', 'admin'].includes($page.props.auth.user.role)"
                @click="openInviteModal" 
                class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-sm font-mono text-[11px] uppercase tracking-wider font-bold flex items-center gap-3 active:scale-95 transition-all shadow-md"
            >
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
                <span class="material-symbols-outlined text-slate-900 text-[20px]">groups</span>
                <span class="text-sm font-semibold text-slate-900 tracking-wide">Active Workspace Members</span>
                <span class="bg-slate-900/10 text-slate-900 text-[10px] font-mono px-2 py-0.5 rounded-full border border-slate-900/20">{{ members.length }}</span>
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
                                        <span class="text-sm font-semibold text-slate-900 group-hover:text-slate-900 transition-colors">{{ member.name }}</span>
                                        <span v-if="member.id === $page.props.auth.user.id" class="text-[9px] font-mono text-slate-900 uppercase tracking-widest mt-0.5 font-bold">You</span>
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
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-900"></span>
                                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest opacity-70">Active Now</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <template v-if="member.role !== 'owner' && member.id !== $page.props.auth.user.id && ['owner', 'admin'].includes($page.props.auth.user.role)">
                                        <select @change="updateRole(member.id, $event.target.value)" class="bg-white text-slate-900 text-[10px] font-mono border border-slate-300 rounded-lg px-3 py-1.5 appearance-none cursor-pointer focus:outline-none focus:border-slate-900 transition-all hover:bg-slate-50 shadow-sm">
                                            <option v-for="role in ['admin', 'developer', 'viewer']" :key="role" :value="role" :selected="member.role === role">{{ role.toUpperCase() }}</option>
                                        </select>
                                        <button @click="removeMember(member.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-error/60 hover:text-error hover:bg-error/10 transition-all border border-transparent hover:border-error/20 active:scale-90">
                                            <span class="material-symbols-outlined text-[18px]">person_remove</span>
                                        </button>
                                    </template>
                                    <span v-else class="text-[10px] font-mono text-slate-600 uppercase tracking-widest">
                                        {{ member.id === $page.props.auth.user.id ? 'YOU' : 'READ ONLY' }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#0f172a 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
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
                                <div class="flex items-center justify-end gap-3" v-if="['owner', 'admin'].includes($page.props.auth.user.role)">
                                    <button @click="copyInviteLink(inv.token)" class="bg-white hover:bg-slate-50 text-slate-900 border border-slate-900 px-4 py-2 rounded-lg text-[10px] font-mono uppercase tracking-widest font-bold transition-all active:scale-95 shadow-sm flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">content_copy</span>
                                        Copy Link
                                    </button>
                                    <button @click="revokeInvite(inv.id)" class="bg-white hover:bg-slate-50 text-error border border-slate-200 px-4 py-2 rounded-lg text-[10px] font-mono uppercase tracking-widest font-bold transition-all active:scale-95 shadow-sm">Revoke</button>
                                </div>
                                <span v-else class="text-[10px] font-mono text-slate-400">RESTRICTED</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#0f172a 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>
        
        <!-- Invite Modal -->
        <Modal :show="showInviteModal" @close="closeInviteModal" maxWidth="md">
            <div class="p-8 bg-white subpixel-antialiased" style="-webkit-font-smoothing: auto !important; transform: translateZ(0); text-rendering: geometricPrecision;">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-xl rotate-3 hover:rotate-0 transition-transform duration-300">
                        <span class="material-symbols-outlined text-[28px]">person_add_alt</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-display font-black text-slate-900 uppercase tracking-tight leading-none mb-1">Invite Team</h2>
                        <p class="text-[10px] font-mono text-slate-500 uppercase tracking-[0.2em] font-bold">Workspace Authorization</p>
                    </div>
                </div>

                <form @submit.prevent="submitInvite" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label class="block text-xs font-sans text-slate-900 uppercase tracking-widest mb-2 font-black">Member Email Address</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-xl group-focus-within:text-slate-900 transition-colors">mail</span>
                            <input 
                                v-model="inviteForm.email" 
                                type="email" 
                                required
                                placeholder="colleague@company.com"
                                class="w-full bg-slate-50 border-2 border-slate-200 pl-12 pr-4 py-3.5 rounded-xl font-sans text-base focus:outline-none focus:border-slate-900 focus:bg-white transition-all placeholder:text-slate-400 text-slate-900 font-bold"
                            >
                        </div>
                        <div v-if="inviteForm.errors.email" class="mt-2 text-[10px] font-sans text-error uppercase font-bold tracking-tight bg-error/10 px-3 py-1.5 rounded-md inline-block">{{ inviteForm.errors.email }}</div>
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-xs font-sans text-slate-900 uppercase tracking-widest mb-3 font-black">Choose Access Role</label>
                        <div class="space-y-3">
                            <label v-for="role in roles" :key="role.key" 
                                :class="[
                                    'relative flex items-center p-4 border-2 cursor-pointer rounded-xl transition-all duration-200 group',
                                    inviteForm.role === role.key 
                                        ? 'border-slate-900 bg-slate-50 shadow-sm' 
                                        : 'border-slate-200 hover:border-slate-300 bg-white'
                                ]"
                            >
                                <input type="radio" v-model="inviteForm.role" :value="role.key" class="sr-only">
                                
                                <div :class="[
                                    'w-12 h-12 rounded-lg flex items-center justify-center shrink-0 border-2 transition-colors',
                                    inviteForm.role === role.key ? 'bg-slate-900 border-slate-900 text-white' : 'bg-slate-50 border-slate-200 text-slate-400 group-hover:border-slate-300 group-hover:text-slate-500'
                                ]">
                                    <span class="material-symbols-outlined text-[22px]">{{ role.icon }}</span>
                                </div>
                                
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-sm font-black text-slate-900 uppercase tracking-widest">{{ role.name }}</span>
                                        <span v-if="inviteForm.role === role.key" class="material-symbols-outlined text-slate-900 text-[20px]">check_circle</span>
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium leading-relaxed pr-6">
                                        {{ role.description }}
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-3">
                        <button type="button" @click="closeInviteModal" class="flex-1 px-6 py-3.5 border-2 border-slate-200 rounded-xl text-xs font-sans uppercase tracking-[0.1em] font-black text-slate-500 hover:text-slate-900 hover:border-slate-900 hover:bg-slate-50 transition-all">Cancel</button>
                        <button 
                            type="submit" 
                            :disabled="inviteForm.processing"
                            class="flex-[2] px-6 py-3.5 bg-slate-900 text-white rounded-xl text-xs font-sans uppercase tracking-[0.1em] font-black hover:bg-black transition-all shadow-md active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-3"
                        >
                            <span class="material-symbols-outlined text-[18px]">{{ inviteForm.processing ? 'sync' : 'send' }}</span>
                            {{ inviteForm.processing ? 'Processing...' : 'Send Invitation' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import Modal from '@/Components/UI/Modal.vue';

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

const page = usePage();
const showInviteModal = ref(false);

const roles = [
    { key: 'admin', name: 'Admin', icon: 'admin_panel_settings', description: 'Full access to manage all workspace resources and members.' },
    { key: 'developer', name: 'Developer', icon: 'code', description: 'Can manage technical resources but cannot manage members.' },
    { key: 'viewer', name: 'Viewer', icon: 'visibility', description: 'Read-only access to all dashboards and resources.' }
];

const inviteForm = useForm({
    email: '',
    role: 'developer'
});

const adminCount = computed(() => props.members.filter(m => m.role === 'admin').length);
const devCount = computed(() => props.members.filter(m => m.role === 'developer').length);

const roleBadge = (role) => ({
    owner: 'bg-slate-900/10 text-slate-900 border-slate-900/20',
    admin: 'bg-slate-800/10 text-slate-800 border-slate-800/20',
    developer: 'bg-slate-600/10 text-slate-600 border-slate-600/20',
    viewer: 'bg-surface-elevated text-content-variant border-border-ghost',
}[role] || 'bg-surface-elevated text-content-variant border-border-ghost');

const avatarBg = (role) => ({
    owner: 'bg-slate-900',
    admin: 'bg-slate-800',
    developer: 'bg-slate-600',
    viewer: 'bg-slate-500',
}[role] || 'bg-slate-500');

const openInviteModal = () => {
    showInviteModal.value = true;
};

const closeInviteModal = () => {
    showInviteModal.value = false;
    inviteForm.reset();
    inviteForm.clearErrors();
};

const submitInvite = () => {
    inviteForm.post('/dashboard/members/invite', {
        onSuccess: () => closeInviteModal(),
        preserveScroll: true
    });
};

const updateRole = (userId, role) => {
    router.put(`/dashboard/members/${userId}/role`, { role }, {
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

const copyInviteLink = (token) => {
    const url = `${window.location.origin}/invitation/${token}`;
    navigator.clipboard.writeText(url).then(() => {
        // Create a temporary flash message since we're using the standard layout toast
        router.get(window.location.pathname, {}, {
            preserveScroll: true,
            onFinish: () => {
                // We'll let the user know via the UI if possible, or just assume the toast trigger
                // Since router.get might be too heavy, we'll just use a small window alert or assume success
            }
        });
        alert('Invitation link copied to clipboard!');
    });
};

const revokeInvite = (id) => {
    if (confirm('Are you sure you want to revoke this invitation?')) {
        router.delete(`/dashboard/members/invitations/${id}`, {
            preserveScroll: true
        });
    }
};
</script>

