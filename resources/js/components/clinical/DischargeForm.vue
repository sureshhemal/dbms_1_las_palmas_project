<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import { Button } from '@/components/ui/button'
import { addDays } from '@/lib/calendarDate'
import type { Stay } from '@/types'

export default defineComponent({
    name: 'DischargeForm',

    components: {
        BaseDatePicker,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        stay: {
            type: Object as PropType<Stay>,
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
            endDate: this.latestDate,
        }
    },

    computed: {
        earliestDate(): string {
            return addDays(this.stay.startDate, 1)
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <p class="text-sm text-neutral-600 dark:text-neutral-300">
            {{ stay.patient.name }} has been in room
            {{ stay.room.roomID }} since {{ stay.startDate }}.
        </p>

        <BaseDatePicker
            v-model="endDate"
            label="Discharged On"
            name="endDate"
            rules="required"
            :min="earliestDate"
            :max="latestDate"
        />

        <UiButton
            class="w-full"
            :disabled="$wait.is('discharging-patient') || !meta.valid"
            @click="$emit('save', endDate)"
        >
            Discharge patient
        </UiButton>
    </VeeForm>
</template>
