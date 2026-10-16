<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import BaseSearchableSelect from '@/components/base/BaseSearchableSelect.vue'
import { Button } from '@/components/ui/button'
import { addDays } from '@/lib/calendarDate'
import type {
    AdmissionFormData,
    PaginatedResponse,
    Patient,
    RoomAvailability,
    SelectOption,
} from '@/types'

const optionsPerSearch = 20

export default defineComponent({
    name: 'AdmissionForm',

    components: {
        BaseDatePicker,
        BaseSearchableSelect,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        admission: {
            type: Object as PropType<AdmissionFormData>,
            required: true,
        },
        latestDate: {
            type: String,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.admission },
            patientOptions: [] as SelectOption[],
            rooms: [] as RoomAvailability[],
        }
    },

    computed: {
        canRecordPastStay(): boolean {
            return (
                Boolean(this.form.startDate) &&
                this.form.startDate < this.latestDate
            )
        },

        earliestDischargeDate(): string {
            return this.form.startDate ? addDays(this.form.startDate, 1) : ''
        },

        roomOptions(): SelectOption[] {
            return this.rooms.map((room) => ({
                value: room.id,
                label: `Room ${room.roomID} · ${room.roomType} · ${room.bedsFree} of ${room.beds} beds free`,
            }))
        },
    },

    watch: {
        admission(): void {
            this.form = { ...this.admission }
        },

        'form.startDate'(): void {
            if (
                !this.canRecordPastStay ||
                (this.form.endDate && this.form.endDate <= this.form.startDate)
            ) {
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
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
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

        <BaseDatePicker
            v-model="form.startDate"
            label="Admitted On"
            name="startDate"
            rules="required"
            :max="latestDate"
        />

        <div v-if="canRecordPastStay" class="space-y-1.5">
            <BaseDatePicker
                v-model="form.endDate"
                label="Discharged On (optional)"
                name="endDate"
                :min="earliestDischargeDate"
                :max="latestDate"
                placeholder="Still admitted"
            />
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                Only for recording a past stay that is already over.
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

        <UiButton
            class="w-full"
            :disabled="$wait.is('admitting-patient') || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            Admit patient
        </UiButton>
    </VeeForm>
</template>
