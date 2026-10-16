<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import BaseSearchableSelect from '@/components/base/BaseSearchableSelect.vue'
import ProcedureDetailsFields from '@/components/clinical/ProcedureDetailsFields.vue'
import { Button } from '@/components/ui/button'
import { addDays } from '@/lib/calendarDate'
import type {
    PaginatedResponse,
    Patient,
    ProcedureDetailsFormData,
    ProcedureEntryFormData,
    RoomAvailability,
    SelectOption,
} from '@/types'

const optionsPerSearch = 20

export default defineComponent({
    name: 'PerformedProcedureForm',

    components: {
        BaseDatePicker,
        BaseSearchableSelect,
        ProcedureDetailsFields,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        entry: {
            type: Object as PropType<ProcedureEntryFormData>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.entry },
            patientOptions: [] as SelectOption[],
            rooms: [] as RoomAvailability[],
        }
    },

    computed: {
        procedureDetails: {
            get(): ProcedureDetailsFormData {
                return {
                    procedure_id: this.form.procedure_id,
                    physician_id: this.form.physician_id,
                    nurse_id: this.form.nurse_id,
                    procDate: this.form.procDate,
                }
            },

            set(procedureDetails: ProcedureDetailsFormData): void {
                this.form = { ...this.form, ...procedureDetails }
            },
        },

        earliestDischargeDate(): string {
            const dayAfterAdmission = this.form.startDate
                ? addDays(this.form.startDate, 1)
                : ''

            return this.form.procDate > dayAfterAdmission
                ? this.form.procDate
                : dayAfterAdmission
        },

        roomOptions(): SelectOption[] {
            return this.rooms.map((room) => ({
                value: room.id,
                label: `Room ${room.roomID} · ${room.roomType} · ${room.bedsFree} of ${room.beds} beds free`,
            }))
        },
    },

    watch: {
        entry(): void {
            this.form = { ...this.entry }
        },

        'form.procDate'(procDate: string): void {
            if (!this.form.startDate || this.form.startDate > procDate) {
                this.form.startDate = procDate
            }

            if (this.form.endDate && this.form.endDate < procDate) {
                this.form.endDate = ''
            }
        },

        'form.startDate'(): void {
            if (this.form.endDate && this.form.endDate <= this.form.startDate) {
                this.form.endDate = ''
            }

            this.fetchRooms()
        },

        'form.endDate'(): void {
            this.fetchRooms()
        },
    },

    created() {
        Promise.all([this.searchPatients(''), this.fetchRooms()])
    },

    methods: {
        async searchPatients(searchTerm: string): Promise<void> {
            this.$wait.start('searching-patients')

            const { data } = await this.$http
                .get<PaginatedResponse<Patient>>('patients', {
                    params: {
                        search: searchTerm || undefined,
                        per_page: optionsPerSearch,
                    },
                })
                .finally(() => this.$wait.end('searching-patients'))

            this.patientOptions = data.data.map((patient) => ({
                value: patient.id,
                label: `${patient.name} (${patient.patientID})`,
            }))
        },

        async fetchRooms(): Promise<void> {
            if (!this.form.startDate) {
                this.rooms = []

                return
            }

            this.$wait.start('fetching-room-availability')

            const { data } = await this.$http
                .get<RoomAvailability[]>('room-availability', {
                    params: {
                        date: this.form.startDate,
                        until: this.form.endDate || undefined,
                    },
                })
                .finally(() => this.$wait.end('fetching-room-availability'))

            this.rooms = data

            if (!data.some((room) => room.id === this.form.room_id)) {
                this.form.room_id = ''
            }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-6">
        <section class="space-y-4">
            <h3
                class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
            >
                Procedure
            </h3>

            <BaseSearchableSelect
                v-model="form.patient_id"
                label="Patient"
                name="patient_id"
                rules="required"
                placeholder="Search patients by name or ID"
                :options="patientOptions"
                searches-remotely
                :is-searching="$wait.is('searching-patients')"
                @search="searchPatients"
            />

            <ProcedureDetailsFields v-model="procedureDetails" />
            <p class="-mt-2 text-xs text-neutral-500 dark:text-neutral-400">
                A past date records a procedure, a future date schedules it.
            </p>
        </section>

        <section
            class="space-y-4 border-t border-neutral-200 pt-4 dark:border-neutral-800"
        >
            <h3
                class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
            >
                Stay
            </h3>

            <BaseDatePicker
                v-model="form.startDate"
                label="Admitted On"
                name="startDate"
                rules="required"
                :max="form.procDate"
            />

            <div class="space-y-1.5">
                <BaseDatePicker
                    v-model="form.endDate"
                    label="Discharge On (optional)"
                    name="endDate"
                    :min="earliestDischargeDate"
                    placeholder="Not decided yet"
                />
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Leave blank if the discharge date is not known yet.
                </p>
            </div>

            <BaseSearchableSelect
                v-model="form.room_id"
                label="Room"
                name="room_id"
                rules="required"
                :placeholder="
                    $wait.is('fetching-room-availability')
                        ? 'Finding free rooms…'
                        : 'Choose a room'
                "
                :options="roomOptions"
            />
            <p class="-mt-2 text-xs text-neutral-500 dark:text-neutral-400">
                Only rooms with a free bed for the whole stay.
            </p>
        </section>

        <p class="text-xs text-neutral-500 dark:text-neutral-400">
            Further procedures during this stay are added from the Stays tab.
            Performed procedures cannot be changed or removed.
        </p>

        <UiButton
            class="w-full"
            :disabled="$wait.is('recording-performed-procedure') || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            Record procedure and admit
        </UiButton>
    </VeeForm>
</template>
