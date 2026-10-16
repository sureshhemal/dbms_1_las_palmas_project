<script lang="ts">
import { defineComponent } from 'vue'
import PatientMedicationsChart from '@/components/patient/PatientMedicationsChart.vue'
import type { PatientMedication } from '@/types'

export default defineComponent({
    name: 'PatientMedicationsPanel',

    components: {
        PatientMedicationsChart,
    },

    props: {
        patientId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            medications: [] as PatientMedication[],
        }
    },

    created() {
        this.fetchMedications()
    },

    methods: {
        async fetchMedications(): Promise<void> {
            this.$wait.start('fetching-patient-medications')

            const { data } = await this.$http
                .get<PatientMedication[]>(
                    `patients/${this.patientId}/medications`,
                )
                .finally(() => this.$wait.end('fetching-patient-medications'))

            this.medications = data
        },
    },
})
</script>

<template>
    <PatientMedicationsChart
        :medications="medications"
        :is-loading="$wait.is('fetching-patient-medications')"
    />
</template>
