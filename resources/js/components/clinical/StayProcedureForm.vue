<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import ProcedureDetailsFields from '@/components/clinical/ProcedureDetailsFields.vue'
import { Button } from '@/components/ui/button'
import type { ProcedureDetailsFormData, Stay } from '@/types'

export default defineComponent({
    name: 'StayProcedureForm',

    components: {
        ProcedureDetailsFields,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        stay: {
            type: Object as PropType<Stay>,
            required: true,
        },
        today: {
            type: String,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: {
                procedure_id: '',
                physician_id: '',
                nurse_id: '',
                procDate:
                    this.stay.startDate > this.today
                        ? this.stay.startDate
                        : this.today,
            } as ProcedureDetailsFormData,
        }
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <p class="text-sm text-neutral-600 dark:text-neutral-300">
            {{ stay.patient.name }} · room {{ stay.room.roomID }} ·
            {{ stay.startDate }} → {{ stay.endDate ?? 'not decided' }}
        </p>

        <ProcedureDetailsFields
            v-model="form"
            :earliest-date="stay.startDate"
            :latest-date="stay.endDate ?? ''"
        />

        <UiButton
            class="w-full"
            :disabled="$wait.is('recording-stay-procedure') || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            Add procedure
        </UiButton>
    </VeeForm>
</template>
