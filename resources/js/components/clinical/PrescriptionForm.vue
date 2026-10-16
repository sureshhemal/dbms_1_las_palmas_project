<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSearchableSelect from '@/components/base/BaseSearchableSelect.vue'
import { Button } from '@/components/ui/button'
import type {
    Medication,
    PaginatedResponse,
    Patient,
    PrescriptionFormData,
    SelectOption,
} from '@/types'

const optionsPerSearch = 20

export default defineComponent({
    name: 'PrescriptionForm',

    components: {
        BaseDatePicker,
        BaseInput,
        BaseSearchableSelect,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        prescription: {
            type: Object as PropType<PrescriptionFormData>,
            required: true,
        },
        prescriberOptions: {
            type: Array as PropType<SelectOption[]>,
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
            form: { ...this.prescription },
            patientOptions: [] as SelectOption[],
            medicationOptions: [] as SelectOption[],
        }
    },

    watch: {
        prescription(): void {
            this.form = { ...this.prescription }
        },
    },

    created() {
        Promise.all([this.searchPatients(''), this.searchMedications('')])
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

        async searchMedications(searchTerm: string): Promise<void> {
            this.$wait.start('searching-medications')

            const { data } = await this.$http
                .get<PaginatedResponse<Medication>>('medications', {
                    params: {
                        search: searchTerm || undefined,
                        per_page: optionsPerSearch,
                    },
                })
                .finally(() => this.$wait.end('searching-medications'))

            this.medicationOptions = data.data.map((medication) => ({
                value: medication.id,
                label: medication.name,
            }))
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
            v-model="form.medication_id"
            label="Medication"
            name="medication_id"
            rules="required"
            placeholder="Search medications"
            :options="medicationOptions"
            searches-remotely
            :is-searching="$wait.is('searching-medications')"
            @search="searchMedications"
        />

        <BaseInput
            v-model="form.dose"
            label="Dose"
            name="dose"
            rules="required|max:40"
            placeholder="e.g. 500 mg twice daily"
        />

        <BaseSearchableSelect
            v-model="form.physician_id"
            label="Prescriber"
            name="physician_id"
            rules="required"
            placeholder="Choose a physician"
            :options="prescriberOptions"
        />

        <BaseDatePicker
            v-model="form.prescribedDate"
            label="Prescribed On"
            name="prescribedDate"
            rules="required"
            :max="latestDate"
        />
        <p class="-mt-2 text-xs text-neutral-500 dark:text-neutral-400">
            Prescriptions cannot be changed or removed after they are added.
        </p>

        <UiButton
            class="w-full"
            :disabled="$wait.is('saving-prescription') || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            Add prescription
        </UiButton>
    </VeeForm>
</template>
