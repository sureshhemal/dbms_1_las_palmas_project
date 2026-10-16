<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSearchableSelect from '@/components/base/BaseSearchableSelect.vue'
import { Button } from '@/components/ui/button'
import { todayIn } from '@/lib/calendarDate'
import type { PatientFormData, SelectOption } from '@/types'

const noPrimaryPhysician = 'none'

export default defineComponent({
    name: 'PatientForm',

    components: {
        BaseDatePicker,
        BaseInput,
        BaseSearchableSelect,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        patient: {
            type: Object as PropType<PatientFormData>,
            required: true,
        },
        primaryPhysicianOptions: {
            type: Array as PropType<SelectOption[]>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.patient },
        }
    },

    computed: {
        today(): string {
            return todayIn(this.$page.props.hospitalTimezone)
        },

        isEditingExistingPatient(): boolean {
            return this.form.id !== null
        },

        patientIdPlaceholder(): string {
            return this.isEditingExistingPatient
                ? 'Leave blank to keep the current ID'
                : 'Leave blank to assign one automatically'
        },

        saveButtonLabel(): string {
            return this.isEditingExistingPatient
                ? 'Save changes'
                : 'Add patient'
        },

        primaryPhysicianChoices(): SelectOption[] {
            return [
                { value: noPrimaryPhysician, label: 'No primary physician' },
                ...this.primaryPhysicianOptions,
            ]
        },

        selectedPrimaryPhysician: {
            get(): string {
                return this.form.primary_physician_id || noPrimaryPhysician
            },

            set(physicianId: string): void {
                this.form.primary_physician_id =
                    physicianId === noPrimaryPhysician ? '' : physicianId
            },
        },
    },

    watch: {
        patient(): void {
            this.form = { ...this.patient }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <BaseInput
            v-model="form.patientID"
            label="Patient ID"
            name="patientID"
            rules="integer"
            :placeholder="patientIdPlaceholder"
        />

        <BaseInput
            v-model="form.name"
            label="Name"
            name="name"
            rules="required|max:40"
        />

        <BaseDatePicker
            v-model="form.dob"
            label="Date of Birth"
            name="dob"
            :max="today"
        />

        <BaseInput
            v-model="form.phone"
            label="Phone"
            name="phone"
            rules="max:16"
            placeholder="e.g. (555) 867-5309"
        />

        <BaseInput
            v-model="form.address"
            label="Address"
            name="address"
            rules="max:100"
        />

        <BaseInput
            v-model="form.insuranceNumber"
            label="Insurance Number"
            name="insuranceNumber"
            rules="integer"
            placeholder="Family members can share one"
        />

        <BaseSearchableSelect
            v-model="selectedPrimaryPhysician"
            label="Primary Physician"
            name="primary_physician_id"
            :options="primaryPhysicianChoices"
        />

        <BaseInput
            v-model="form.ssn"
            label="SSN"
            name="ssn"
            rules="digits:9"
            placeholder="9 digits, e.g. 012345678"
        />

        <UiButton
            class="w-full"
            :disabled="$wait.any() > 0 || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            {{ saveButtonLabel }}
        </UiButton>
    </VeeForm>
</template>
