<script lang="ts">
import { defineComponent } from 'vue'
import BaseShareChart from '@/components/base/BaseShareChart.vue'
import type { PatientMedication, ShareSlice } from '@/types'

export default defineComponent({
    name: 'PatientMedicationsPanel',

    components: {
        BaseShareChart,
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

    computed: {
        slices(): ShareSlice[] {
            return this.medications.map((medication) => ({
                key: medication.medicationId,
                name: medication.name,
                isRemoved: medication.isRemoved,
                count: medication.prescriptionsCount,
                percentage: medication.percentage,
            }))
        },
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
    <BaseShareChart
        :slices="slices"
        count-label="prescriptions"
        item-label="medications"
        empty-message="No medications prescribed yet."
        :is-loading="$wait.is('fetching-patient-medications')"
    />
</template>
