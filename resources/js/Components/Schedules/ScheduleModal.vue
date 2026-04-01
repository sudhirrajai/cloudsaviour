<template>
    <Transition name="modal-fade">
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-canvas/80 backdrop-blur-md transition-opacity" @click="close"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-2xl bg-surface-elevated/90 border border-white/10 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-xl animate-in fade-in zoom-in duration-300 my-8">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
                            <span class="material-symbols-outlined text-primary text-2xl">{{ isEditing ? 'edit_calendar' : 'add_task' }}</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-display font-bold text-white">{{ isEditing ? 'Edit Schedule' : 'Create Schedule' }}</h3>
                            <p class="text-[10px] font-mono text-slate-500 uppercase tracking-widest opacity-70">Automation Rule</p>
                        </div>
                    </div>
                    <button @click="close" class="text-slate-400 hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/5">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        
                        <!-- Name -->
                        <div class="col-span-full">
                            <label class="block text-xs font-mono text-slate-400 uppercase tracking-wider mb-2">Schedule Name</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                required
                                class="w-full bg-surface border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-primary/50 transition-all placeholder:text-slate-600"
                                placeholder="e.g., Nightly Shutdown"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-xs text-error">{{ form.errors.name }}</div>
                        </div>

                        <!-- Target Resource -->
                        <div>
                            <label class="block text-xs font-mono text-slate-400 uppercase tracking-wider mb-2">Resource Type</label>
                            <select 
                                v-model="form.resource_type" 
                                class="w-full bg-surface border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-primary/50 transition-all appearance-none"
                            >
                                <option value="ec2" class="bg-slate-900 text-white">EC2 Instance</option>
                                <option value="rds" class="bg-slate-900 text-white">RDS Instance</option>
                            </select>
                            <div v-if="form.errors.resource_type" class="mt-1 text-xs text-error">{{ form.errors.resource_type }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-mono text-slate-400 uppercase tracking-wider mb-2">Target Instance</label>
                            <select 
                                v-model="form.resource_id" 
                                required
                                class="w-full bg-surface border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-primary/50 transition-all disabled:opacity-50 appearance-none"
                                :disabled="!availableInstances.length"
                            >
                                <option value="" disabled class="bg-slate-900 text-slate-400">Select an instance</option>
                                <option v-for="instance in availableInstances" :key="instance.id" :value="instance.id" class="bg-slate-900 text-white">
                                    {{ instance.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.resource_id" class="mt-1 text-xs text-error">{{ form.errors.resource_id }}</div>
                        </div>

                        <!-- Action -->
                        <div class="col-span-full">
                            <label class="block text-xs font-mono text-slate-400 uppercase tracking-wider mb-2">Action to Perform</label>
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <button 
                                        type="button"
                                        @click="form.action = 'start'"
                                        class="flex-1 py-3 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2 border"
                                        :class="form.action === 'start' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/50 shadow-[0_0_15px_rgba(16,185,129,0.15)]' : 'bg-surface border-white/10 text-slate-400 hover:text-white hover:border-white/30'"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">play_arrow</span> Start
                                    </button>
                                    <button 
                                        type="button"
                                        @click="form.action = 'stop'"
                                        class="flex-1 py-3 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2 border"
                                        :class="form.action === 'stop' ? 'bg-amber-500/10 text-amber-500 border-amber-500/50 shadow-[0_0_15px_rgba(245,158,11,0.15)]' : 'bg-surface border-white/10 text-slate-400 hover:text-white hover:border-white/30'"
                                    >
                                        <span class="material-symbols-outlined text-[18px]">stop</span> Stop
                                    </button>
                                </div>
                                <div class="pt-4 border-t border-white/5">
                                    <div class="flex flex-wrap lg:flex-nowrap items-center gap-4 group/danger">
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="material-symbols-outlined text-xs text-slate-600 group-hover/danger:text-error/60 transition-colors">gpp_maybe</span>
                                            <div class="text-[9px] font-mono text-slate-600 uppercase tracking-[0.2em] font-bold group-hover/danger:text-error/60 transition-colors">Critical Actions</div>
                                        </div>
                                        <div class="h-px bg-white/5 flex-1 group-hover/danger:bg-error/10 transition-colors"></div>
                                        <div class="flex items-center gap-2 w-full lg:w-auto">
                                            <button 
                                                v-if="form.resource_type === 'ec2'"
                                                type="button"
                                                @click="form.action = 'terminate'"
                                                class="flex-1 lg:flex-none px-5 py-2.5 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 border border-error/20 text-error/60 hover:text-error hover:bg-error/10 group/btn"
                                                :class="form.action === 'terminate' ? 'bg-error/10 text-error border-error shadow-glow-error' : ''"
                                                title="This will permanently destroy the instance"
                                            >
                                                <span class="material-symbols-outlined text-[16px] group-hover/btn:animate-pulse">delete_forever</span> Terminate
                                            </button>
                                            <button 
                                                v-if="form.resource_type === 'rds'"
                                                type="button"
                                                @click="form.action = 'delete'"
                                                class="flex-1 lg:flex-none px-5 py-2.5 text-xs font-bold rounded-lg transition-all flex items-center justify-center gap-2 border border-error/20 text-error/60 hover:text-error hover:bg-error/10 group/btn"
                                                :class="form.action === 'delete' ? 'bg-error/10 text-error border-error shadow-glow-error' : ''"
                                                title="This will permanently delete the database"
                                            >
                                                <span class="material-symbols-outlined text-[16px] group-hover/btn:animate-pulse">heart_broken</span> Delete DB
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.action" class="mt-1 text-xs text-error">{{ form.errors.action }}</div>
                        </div>

                        <!-- Warning Message -->
                        <div v-if="['terminate', 'delete'].includes(form.action)" class="col-span-full animate-in fade-in slide-in-from-top-2 duration-300">
                            <div class="bg-error/10 border border-error/30 rounded-xl p-4 flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-error/20 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-error">warning</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white mb-1">Destructive Action Warning</p>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">
                                        You are scheduling the permanent {{ form.action === 'terminate' ? 'termination' : 'deletion' }} of this resource. 
                                        This action is irreversible and will result in {{ form.action === 'terminate' ? 'the EC2 instance being destroyed' : 'all database data being lost (SkipFinalSnapshot: Yes)' }}.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Time & Timezone -->
                        <div>
                            <label class="block text-xs font-mono text-slate-400 uppercase tracking-wider mb-2">Time of Day</label>
                            <input 
                                v-model="form.time_of_day" 
                                type="time" 
                                required
                                class="w-full bg-surface border border-white/10 rounded-lg px-4 py-[11px] text-white focus:outline-none focus:border-primary/50 transition-all"
                            />
                            <div v-if="form.errors.time_of_day" class="mt-1 text-xs text-error">{{ form.errors.time_of_day }}</div>
                        </div>

                        <div class="col-span-full">
                            <label class="block text-xs font-mono text-slate-400 uppercase tracking-wider mb-2">Timezone</label>
                            <select 
                                v-model="form.timezone" 
                                class="w-full bg-surface border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-primary/50 transition-all font-mono appearance-none"
                            >
                                <option v-for="tz in availableTimezones" :key="tz" :value="tz" class="bg-slate-900 text-white">{{ tz }}</option>
                            </select>
                            <div v-if="form.errors.timezone" class="mt-1 text-xs text-error">{{ form.errors.timezone }}</div>
                        </div>

                        <!-- Days of Week -->
                        <div class="col-span-full">
                            <label class="block text-xs font-mono text-slate-400 uppercase tracking-wider mb-2">Days of Week</label>
                            <div class="flex flex-wrap gap-2">
                                <label 
                                    v-for="day in daysOfWeekOptions" 
                                    :key="day.value"
                                    class="cursor-pointer relative"
                                >
                                    <input 
                                        type="checkbox" 
                                        :value="day.value" 
                                        v-model="form.days_of_week"
                                        class="sr-only peer"
                                    />
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-mono text-sm border transition-all peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary peer-checked:shadow-[0_0_15px_rgba(59,130,246,0.3)] border-white/10 text-slate-400 bg-surface hover:border-white/30">
                                        {{ day.label }}
                                    </div>
                                </label>
                            </div>
                            <div class="mt-2 text-xs text-slate-500 font-mono">
                                Selected: {{ form.days_of_week.length > 0 ? form.days_of_week.map(d => daysOfWeekOptions.find(opt => opt.value === d).full).join(', ') : 'None' }}
                            </div>
                            <div v-if="form.errors.days_of_week" class="mt-1 text-xs text-error">{{ form.errors.days_of_week }}</div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-white/5">
                        <button 
                            type="button"
                            @click="close"
                            class="px-5 py-2.5 rounded-lg text-[11px] font-mono uppercase tracking-widest text-slate-400 hover:text-white hover:bg-white/5 transition-all"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 rounded-lg text-[11px] font-mono uppercase tracking-widest font-bold flex items-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed bg-primary text-white shadow-[0_0_20px_rgba(59,130,246,0.2)] hover:shadow-[0_0_30px_rgba(59,130,246,0.3)] hover:brightness-110 active:scale-95 group"
                        >
                            <span>{{ isEditing ? 'Save Changes' : 'Create Schedule' }}</span>
                            <span v-if="form.processing" class="material-symbols-outlined text-sm animate-spin">sync</span>
                            <span v-else class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">send</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    schedule: {
        type: Object,
        default: null
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

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.schedule);

const daysOfWeekOptions = [
    { label: 'M', full: 'Mon', value: 1 },
    { label: 'T', full: 'Tue', value: 2 },
    { label: 'W', full: 'Wed', value: 3 },
    { label: 'T', full: 'Thu', value: 4 },
    { label: 'F', full: 'Fri', value: 5 },
    { label: 'S', full: 'Sat', value: 6 },
    { label: 'S', full: 'Sun', value: 7 },
];

const commonTimezones = [
    'UTC', 'Asia/Kolkata', 'Europe/London', 'America/New_York', 'America/Los_Angeles',
    'Asia/Tokyo', 'Asia/Singapore', 'Australia/Sydney', 'Europe/Paris', 'Europe/Berlin'
];

const guessTimezone = () => {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (e) {
        return 'UTC';
    }
};

const userTimezone = guessTimezone();
const availableTimezones = computed(() => {
    const list = [...commonTimezones];
    if (!list.includes(userTimezone)) {
        list.push(userTimezone);
    }
    return list.sort();
});

const form = useForm({
    name: '',
    resource_type: 'ec2',
    resource_id: '',
    action: 'start',
    time_of_day: '09:00',
    days_of_week: [1, 2, 3, 4, 5], // Default M-F
    timezone: userTimezone,
});

// Calculate available instances based on chosen type
const availableInstances = computed(() => {
    if (form.resource_type === 'ec2') {
        return props.ec2Instances.map(i => ({
            id: i.instance_id, // AWS unique identifier is stored here usually, check db field
            label: i.name ? `${i.name} (${i.instance_id})` : i.instance_id
        }));
    } else {
        return props.rdsInstances.map(i => ({
            id: i.db_instance_identifier,
            label: i.db_instance_identifier
        }));
    }
});

// Watch for modal opening to populate form
watch(() => props.show, (showing) => {
    if (showing) {
        if (props.schedule) {
            form.name = props.schedule.name;
            form.resource_type = props.schedule.resource_type;
            form.resource_id = props.schedule.resource_id;
            form.action = props.schedule.action;
            form.time_of_day = props.schedule.time_of_day;
            form.days_of_week = [...props.schedule.days_of_week];
            form.timezone = props.schedule.timezone;
        } else {
            form.reset();
            form.timezone = guessTimezone();
            // Optional: Auto-select first availability if existing
            if (form.resource_type === 'ec2' && props.ec2Instances.length) {
                form.resource_id = props.ec2Instances[0].instance_id;
            }
        }
    }
});

// Auto-reset resource ID if changing type
watch(() => form.resource_type, (newType, oldType) => {
    if (newType !== oldType && props.show) {
        form.resource_id = '';
    }
});

const close = () => {
    emit('close');
    form.clearErrors();
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/dashboard/schedules/${props.schedule.id}`, {
            preserveScroll: true,
            onSuccess: () => close()
        });
    } else {
        form.post('/dashboard/schedules', {
            preserveScroll: true,
            onSuccess: () => close()
        });
    }
};
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
