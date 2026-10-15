<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import { Button } from '@/components/ui/button'
import type { MedicationFormData } from '@/types'

export default defineComponent({
    name: 'MedicationForm',

    components: {
        BaseInput,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        medication: {
            type: Object as PropType<MedicationFormData>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.medication },
        }
    },

    computed: {
        isEditingExistingMedication(): boolean {
            return this.form.id !== null
        },

        medIdPlaceholder(): string {
            return this.isEditingExistingMedication
                ? 'Leave blank to keep the current ID'
                : 'Leave blank to assign one automatically'
        },

        saveButtonLabel(): string {
            return this.isEditingExistingMedication
                ? 'Save changes'
                : 'Add medication'
        },
    },

    watch: {
        medication(): void {
            this.form = { ...this.medication }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <BaseInput
            v-model="form.medID"
            label="Med ID"
            name="medID"
            rules="integer"
            :placeholder="medIdPlaceholder"
        />

        <BaseInput
            v-model="form.name"
            label="Name"
            name="name"
            rules="required|max:40"
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
