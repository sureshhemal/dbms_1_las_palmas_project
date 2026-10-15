<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import { Button } from '@/components/ui/button'
import type { NurseFormData } from '@/types'
import { nursePositions } from '@/types'

export default defineComponent({
    name: 'NurseForm',

    components: {
        BaseInput,
        BaseSelect,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        nurse: {
            type: Object as PropType<NurseFormData>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.nurse },
            nursePositions,
        }
    },

    computed: {
        isEditingExistingNurse(): boolean {
            return this.form.id !== null
        },

        nurseIdPlaceholder(): string {
            return this.isEditingExistingNurse
                ? 'Leave blank to keep the current ID'
                : 'Leave blank to assign one automatically'
        },

        saveButtonLabel(): string {
            return this.isEditingExistingNurse ? 'Save changes' : 'Add nurse'
        },
    },

    watch: {
        nurse(): void {
            this.form = { ...this.nurse }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <BaseInput
            v-model="form.nurseID"
            label="Nurse ID"
            name="nurseID"
            rules="integer"
            :placeholder="nurseIdPlaceholder"
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
            :options="nursePositions"
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
