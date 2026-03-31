<template>
    <DashboardLayout currentPage="schedules">
        <!-- Page Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
            <div>
                <h1 class="text-4xl lg:text-5xl font-display font-bold tracking-tight text-white mb-2">Schedules</h1>
                <p class="text-content-variant font-sans">Automate start/stop actions for your EC2 and RDS instances.</p>
            </div>
            <button @click="openModal()" class="bg-primary text-white px-6 py-2.5 rounded-sm font-mono text-[11px] uppercase tracking-wider font-bold flex items-center gap-3 hover:brightness-110 active:scale-95 transition-all shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                <span class="material-symbols-outlined text-sm">add</span>
                Create Schedule
            </button>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard label="Active Schedules" :value="String(activeCount)" sublabel="AUTOMATED RULES" icon="schedule" borderColor="primary" valueColor="white" />
            <StatCard label="Next Execution" sublabel="UPCOMING" icon="timer" borderColor="tertiary">
                <span class="font-mono text-tertiary">{{ nextExecutionTime }}</span>
            </StatCard>
            <StatCard label="Paused" :value="String(pausedCount)" sublabel="DISABLED RULES" icon="pause_circle" borderColor="grey" valueColor="white" />
            <StatCard label="Total Rules" :value="String(schedules.length)" sublabel="SAVING COSTS" icon="analytics" borderColor="secondary" valueColor="white" />
        </div>

        <!-- Schedules Table -->
        <section class="bg-surface/50 backdrop-blur-sm rounded-lg border border-white/5 overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1100px]">
                    <thead class="bg-canvas text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-normal">Schedule Name</th>
                            <th class="px-6 py-4 font-normal">Target Resource</th>
                            <th class="px-6 py-4 font-normal">Operation</th>
                            <th class="px-6 py-4 font-normal">Time (Local)</th>
                            <th class="px-6 py-4 font-normal">Recurrence</th>
                            <th class="px-6 py-4 font-normal">Timezone</th>
                            <th class="px-6 py-4 font-normal">Status</th>
                            <th class="px-6 py-4 font-normal text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="schedule in schedules" :key="schedule.id" class="group hover:bg-white/[0.03] transition-colors">
                            <td class="px-6 py-5">
                                <span class="text-sm font-semibold text-white group-hover:text-primary transition-colors">{{ schedule.name }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2 px-2 py-1 bg-white/5 rounded border border-white/5 w-fit">
                                    <span :class="['text-[10px] font-mono font-bold', schedule.resource_type === 'ec2' ? 'text-primary' : 'text-secondary']">{{ schedule.resource_type.toUpperCase() }}</span>
                                    <span class="text-[10px] font-mono text-slate-500">{{ schedule.resource_id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span :class="[
                                    'inline-flex items-center px-2.5 py-1 rounded text-[10px] font-mono border font-bold uppercase tracking-wider',
                                    schedule.action === 'start' ? 'bg-tertiary/10 text-tertiary border-tertiary/20' :
                                    schedule.action === 'stop' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' :
                                    'bg-error/10 text-error border-error/20'
                                ]">
                                    <span class="material-symbols-outlined text-xs mr-1.5">
                                        {{ 
                                            schedule.action === 'start' ? 'play_arrow' : 
                                            schedule.action === 'stop' ? 'stop' : 
                                            schedule.action === 'terminate' ? 'delete_forever' : 'delete'
                                        }}
                                    </span>
                                    {{ schedule.action }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-sm text-white font-bold">{{ schedule.time_of_day }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex gap-1">
                                    <DayPill v-for="(day, i) in dayLabels" :key="i" :label="day" :active="schedule.days_of_week.includes(i + 1) || schedule.days_of_week.includes(String(i + 1))" />
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="font-mono text-[10px] text-slate-500 uppercase tracking-widest">{{ schedule.timezone }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <Toggle :modelValue="!!schedule.is_active" @update:modelValue="toggleSchedule(schedule.id)" />
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openModal(schedule)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all border border-transparent hover:border-white/10 active:scale-90">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <button @click="deleteSchedule(schedule.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-error/60 hover:text-error hover:bg-error/10 transition-all border border-transparent hover:border-error/20 active:scale-90">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="schedules.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-content-variant font-mono text-xs italic">
                                No automation schedules detected.
                                <span class="block mt-1 text-[10px] opacity-70 uppercase tracking-widest">Click 'Create Schedule' to start automating.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="absolute inset-0 pointer-events-none opacity-[0.03] z-0" style="background-image: radial-gradient(#3b82f6 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
        </section>

        <!-- Schedule Create/Edit Modal -->
        <ScheduleModal 
            :show="isModalOpen" 
            :schedule="editingSchedule" 
            :ec2Instances="ec2Instances" 
            :rdsInstances="rdsInstances" 
            @close="closeModal" 
        />
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import Toggle from '@/Components/UI/Toggle.vue';
import DayPill from '@/Components/UI/DayPill.vue';
import ScheduleModal from '@/Components/Schedules/ScheduleModal.vue';

const props = defineProps({
    schedules: {
        type: Array,
        required: true
    },
    ec2Instances: {
        type: Array,
        default: () => []
    },
    rdsInstances: {
        type: Array,
        default: () => []
    }
});

const dayLabels = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];

const isModalOpen = ref(false);
const editingSchedule = ref(null);

const activeCount = computed(() => props.schedules.filter(s => s.is_active).length);
const pausedCount = computed(() => props.schedules.filter(s => !s.is_active).length);

const nextExecutionTime = computed(() => {
    const active = props.schedules.filter(s => s.is_active);
    if (active.length === 0) return '—';
    // Simplified for now, in a real app you'd calculate this based on time_of_day and days_of_week
    return active[0].time_of_day;
});

const openModal = (schedule = null) => {
    editingSchedule.value = schedule;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        editingSchedule.value = null;
    }, 300);
};

const toggleSchedule = (id) => {
    router.patch(`/dashboard/schedules/${id}/toggle`, {}, {
        preserveScroll: true
    });
};

const deleteSchedule = (id) => {
    if (confirm('Are you sure you want to delete this schedule?')) {
        router.delete(`/dashboard/schedules/${id}`, {
            preserveScroll: true
        });
    }
};
</script>

