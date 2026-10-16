<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import { Button } from '@/components/ui/button'
import { addDays } from '@/lib/calendarDate'
import type { Stay } from '@/types'

export default defineComponent({
    name: 'ExtendStayForm',

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
    },

    emits: ['save'],

    data() {
        return {
            endDate: '',
        }
    },

    computed: {
        earliestDate(): string {
            return this.stay.endDate ? addDays(this.stay.endDate, 1) : ''
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <p class="text-sm text-neutral-600 dark:text-neutral-300">
            {{ stay.patient.name }} is in room {{ stay.room.roomID }} until
            {{ stay.endDate }}.
        </p>

        <BaseDatePicker
            v-model="endDate"
            label="New Discharge Date"
            name="endDate"
            rules="required"
            :min="earliestDate"
            placeholder="Pick the new last day"
        />
        <p class="-mt-2 text-xs text-neutral-500 dark:text-neutral-400">
            The stay currently ends on {{ stay.endDate }}. Pick a later date.
        </p>

        <UiButton
            class="w-full"
            :disabled="$wait.is('extending-stay') || !meta.valid"
            @click="$emit('save', endDate)"
        >
            Extend stay
        </UiButton>
    </VeeForm>
</template>
