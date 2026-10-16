<script lang="ts">
import { Banknote, CalendarClock, Pill, Stethoscope } from '@lucide/vue'
import { defineComponent } from 'vue'
import PatientSummaryCard from '@/components/patient/PatientSummaryCard.vue'
import { formatCost } from '@/lib/formatCost'
import { formatCount } from '@/lib/formatCount'
import type { PatientSummary } from '@/types'

export default defineComponent({
    name: 'PatientSummaryCards',

    components: {
        Banknote,
        CalendarClock,
        PatientSummaryCard,
        Pill,
        Stethoscope,
    },

    props: {
        patientId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            summary: null as PatientSummary | null,
        }
    },

    computed: {
        isLoading(): boolean {
            return this.$wait.is('fetching-patient-summary') || !this.summary
        },

        nextProcedureName(): string {
            return (
                this.summary?.nextProcedure?.procedure.name ?? 'None scheduled'
            )
        },

        nextProcedureDate(): string {
            return this.summary?.nextProcedure?.procDate ?? ''
        },

        performedProceduresCount(): string {
            return formatCount(this.summary?.performedProceduresCount ?? 0)
        },

        prescriptionsCount(): string {
            return formatCount(this.summary?.prescriptionsCount ?? 0)
        },

        totalRevenue(): string {
            return formatCost(this.summary?.totalRevenue ?? 0)
        },
    },

    created() {
        this.fetchSummary()
    },

    methods: {
        async fetchSummary(): Promise<void> {
            this.$wait.start('fetching-patient-summary')

            const { data } = await this.$http
                .get<PatientSummary>(`patients/${this.patientId}/summary`)
                .finally(() => this.$wait.end('fetching-patient-summary'))

            this.summary = data
        },
    },
})
</script>

<template>
    <div class="grid content-start gap-4 sm:grid-cols-2">
        <PatientSummaryCard
            label="Upcoming Procedure"
            tone="info"
            :value="nextProcedureName"
            :detail="nextProcedureDate"
            :is-muted="!summary?.nextProcedure"
            :is-loading="isLoading"
        >
            <template #icon>
                <CalendarClock />
            </template>
        </PatientSummaryCard>

        <PatientSummaryCard
            label="Procedures Performed"
            tone="brand"
            :value="performedProceduresCount"
            :is-loading="isLoading"
        >
            <template #icon>
                <Stethoscope />
            </template>
        </PatientSummaryCard>

        <PatientSummaryCard
            label="Prescriptions Issued"
            tone="warning"
            :value="prescriptionsCount"
            :is-loading="isLoading"
        >
            <template #icon>
                <Pill />
            </template>
        </PatientSummaryCard>

        <PatientSummaryCard
            label="Total Revenue"
            tone="success"
            :value="totalRevenue"
            :is-loading="isLoading"
        >
            <template #icon>
                <Banknote />
            </template>
        </PatientSummaryCard>
    </div>
</template>
