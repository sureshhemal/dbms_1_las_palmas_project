<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import { Button } from '@/components/ui/button'
import type { ProcedureFormData } from '@/types'

export default defineComponent({
    name: 'ProcedureForm',

    components: {
        BaseInput,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        procedure: {
            type: Object as PropType<ProcedureFormData>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.procedure },
        }
    },

    computed: {
        isEditingExistingProcedure(): boolean {
            return this.form.id !== null
        },

        procIdPlaceholder(): string {
            return this.isEditingExistingProcedure
                ? 'Leave blank to keep the current ID'
                : 'Leave blank to assign one automatically'
        },

        saveButtonLabel(): string {
            return this.isEditingExistingProcedure
                ? 'Save changes'
                : 'Add procedure'
        },
    },

    watch: {
        procedure(): void {
            this.form = { ...this.procedure }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <BaseInput
            v-model="form.procID"
            label="Procedure ID"
            name="procID"
            rules="integer"
            :placeholder="procIdPlaceholder"
        />

        <BaseInput
            v-model="form.name"
            label="Name"
            name="name"
            rules="required|max:40"
        />

        <BaseInput
            v-model="form.cost"
            label="Cost (USD)"
            name="cost"
            rules="amount"
            placeholder="Leave blank if the price is not set yet"
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
