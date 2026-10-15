<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import type { PhysicianFormData, SelectOption } from '@/types'
import { physicianPositions } from '@/types'

export default defineComponent({
    name: 'PhysicianForm',

    components: {
        BaseInput,
        BaseMultiSelect,
        BaseSelect,
        UiButton: Button,
        UiLabel: Label,
        VeeForm: Form,
    },

    props: {
        physician: {
            type: Object as PropType<PhysicianFormData>,
            required: true,
        },
        departmentOptions: {
            type: Array as PropType<SelectOption[]>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: {
                ...this.physician,
                department_ids: [...this.physician.department_ids],
            },
            physicianPositions,
        }
    },

    computed: {
        isEditingExistingPhysician(): boolean {
            return this.form.id !== null
        },

        physicianIdPlaceholder(): string {
            return this.isEditingExistingPhysician
                ? 'Leave blank to keep the current ID'
                : 'Leave blank to assign one automatically'
        },

        saveButtonLabel(): string {
            return this.isEditingExistingPhysician
                ? 'Save changes'
                : 'Add physician'
        },
    },

    watch: {
        physician(): void {
            this.form = {
                ...this.physician,
                department_ids: [...this.physician.department_ids],
            }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <BaseInput
            v-model="form.physicianID"
            label="Physician ID"
            name="physicianID"
            rules="integer"
            :placeholder="physicianIdPlaceholder"
        />

        <BaseInput
            v-model="form.name"
            label="Name"
            name="name"
            rules="required|max:40"
        />

        <BaseSelect
            v-model="form.position"
            label="Position"
            name="position"
            rules="required"
            :options="physicianPositions"
        />

        <BaseInput
            v-model="form.ssn"
            label="SSN"
            name="ssn"
            rules="digits:9"
            placeholder="9 digits, e.g. 012345678"
        />

        <div class="space-y-1.5">
            <UiLabel>Departments</UiLabel>
            <BaseMultiSelect
                v-model="form.department_ids"
                :options="departmentOptions"
                placeholder="No departments"
                all-selected-label="All departments"
                trigger-class="w-full"
            />
        </div>

        <UiButton
            class="w-full"
            :disabled="$wait.any() > 0 || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            {{ saveButtonLabel }}
        </UiButton>
    </VeeForm>
</template>
