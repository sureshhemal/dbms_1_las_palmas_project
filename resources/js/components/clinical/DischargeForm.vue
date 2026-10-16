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
        today: {
            type: String,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            endDate:
                this.stay.startDate < this.today
                    ? this.today
                    : addDays(this.stay.startDate, 1),
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
            {{ stay.patient.name }} · room {{ stay.room.roomID }} ·
            {{ stay.startDate }} → {{ stay.endDate ?? 'not decided' }}
        </p>

        <BaseDatePicker
            v-model="endDate"
            label="Discharge On"
            name="endDate"
            rules="required"
            :min="earliestDate"
        />
        <p class="-mt-2 text-xs text-neutral-500 dark:text-neutral-400">
            A future date plans the discharge. It cannot be before the stay's
            last procedure.
        </p>

        <UiButton
            class="w-full"
            :disabled="$wait.is('discharging-patient') || !meta.valid"
            @click="$emit('save', endDate)"
        >
            Discharge
        </UiButton>
    </VeeForm>
</template>
