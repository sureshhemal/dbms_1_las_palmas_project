<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSearchableSelect from '@/components/base/BaseSearchableSelect.vue'
import { Button } from '@/components/ui/button'
import { addMinutesToTime, minutesBetween } from '@/lib/hospitalTime'
import type {
    AppointmentFormData,
    PaginatedResponse,
    Patient,
    SelectOption,
} from '@/types'

const optionsPerSearch = 20

const usualAppointmentMinutes = 30

export default defineComponent({
    name: 'AppointmentForm',

    components: {
        BaseDatePicker,
        BaseInput,
        BaseSearchableSelect,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        appointment: {
            type: Object as PropType<AppointmentFormData>,
            required: true,
        },
        patientOption: {
            type: Object as PropType<SelectOption | null>,
            default: null,
        },
        physicianOptions: {
            type: Array as PropType<SelectOption[]>,
            required: true,
        },
        nurseOptions: {
            type: Array as PropType<SelectOption[]>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.appointment },
            searchedPatientOptions: [] as SelectOption[],
            usualAppointmentMinutes,
        }
    },

    computed: {
        isEditing(): boolean {
            return Boolean(this.form.id)
        },

        patientOptions(): SelectOption[] {
            const isListed = this.searchedPatientOptions.some(
                (option) => option.value === this.patientOption?.value,
            )

            return this.patientOption && !isListed
                ? [this.patientOption, ...this.searchedPatientOptions]
                : this.searchedPatientOptions
        },
    },

    watch: {
        appointment(): void {
            this.form = { ...this.appointment }
        },
    },

    created() {
        this.searchPatients('')
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

            this.searchedPatientOptions = data.data.map((patient) => ({
                value: patient.id,
                label: `${patient.name} (${patient.patientID})`,
            }))
        },

        changeStartTime(startTime: string | number): void {
            const previousMinutes =
                this.form.startTime && this.form.endTime
                    ? minutesBetween(this.form.startTime, this.form.endTime)
                    : 0
            const minutes =
                previousMinutes > 0 ? previousMinutes : usualAppointmentMinutes

            this.form.startTime = String(startTime)

            if (this.form.startTime) {
                this.form.endTime = addMinutesToTime(
                    this.form.startTime,
                    minutes,
                )
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

        <BaseSearchableSelect
            v-model="form.physician_id"
            label="Physician"
            name="physician_id"
            rules="required"
            placeholder="Choose a physician"
            :options="physicianOptions"
        />

        <BaseSearchableSelect
            v-model="form.nurse_id"
            label="Nurse"
            name="nurse_id"
            rules="required"
            placeholder="Choose a nurse"
            :options="nurseOptions"
        />

        <BaseDatePicker
            v-model="form.date"
            label="Date"
            name="date"
            rules="required"
        />

        <div class="grid grid-cols-2 gap-4">
            <BaseInput
                :model-value="form.startTime"
                label="Start Time"
                name="startTime"
                type="time"
                rules="required"
                @update:model-value="changeStartTime"
            />

            <BaseInput
                v-model="form.endTime"
                label="End Time"
                name="endTime"
                type="time"
                rules="required"
            />
        </div>
        <p class="-mt-2 text-xs text-neutral-500 dark:text-neutral-400">
            Times are in hospital time. An appointment usually lasts
            {{ usualAppointmentMinutes }} minutes; change the end time to make
            it longer or shorter.
        </p>

        <UiButton
            class="w-full"
            :disabled="$wait.is('saving-appointment') || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            {{ isEditing ? 'Save changes' : 'Add appointment' }}
        </UiButton>
    </VeeForm>
</template>
