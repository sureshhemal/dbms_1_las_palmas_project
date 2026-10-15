<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import { Button } from '@/components/ui/button'
import type { PhysicianFormData } from '@/types'
import { physicianPositions } from '@/types'

export default defineComponent({
    name: 'PhysicianForm',

    components: {
        BaseInput,
        BaseSelect,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        physician: {
            type: Object as PropType<PhysicianFormData>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.physician },
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
            this.form = { ...this.physician }
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

        <UiButton
            class="w-full"
            :disabled="$wait.any() > 0 || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            {{ saveButtonLabel }}
        </UiButton>
    </VeeForm>
</template>
