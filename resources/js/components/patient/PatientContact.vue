<script lang="ts">
import { Phone } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import type { PatientWithPhone } from '@/types'

export default defineComponent({
    name: 'PatientContact',

    components: {
        PatientNameLink,
        Phone,
    },

    props: {
        patient: {
            type: Object as PropType<PatientWithPhone>,
            required: true,
        },
    },

    computed: {
        telephoneHref(): string {
            return `tel:${(this.patient.phone ?? '').replace(/[^\d+]/g, '')}`
        },
    },
})
</script>

<template>
    <div class="space-y-0.5">
        <PatientNameLink :patient="patient" />
        <a
            v-if="patient.phone"
            :href="telephoneHref"
            class="flex items-center gap-1 text-xs text-neutral-500 tabular-nums hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100"
        >
            <Phone class="size-3" />
            {{ patient.phone }}
        </a>
        <p v-else class="text-xs text-neutral-400 dark:text-neutral-500">
            No phone
        </p>
    </div>
</template>
