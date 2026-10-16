<script lang="ts">
import { Banknote, CalendarClock, Pill, Stethoscope } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseSummaryCard from '@/components/base/BaseSummaryCard.vue'
import { formatCost } from '@/lib/formatCost'
import { formatCount } from '@/lib/formatCount'
import type { ClinicalSummary } from '@/types'

export default defineComponent({
    name: 'ClinicalSummaryCards',

    components: {
        Banknote,
        BaseSummaryCard,
        CalendarClock,
        Pill,
        Stethoscope,
    },

    props: {
        summaryPath: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            summary: null as ClinicalSummary | null,
        }
    },

    computed: {
        isLoading(): boolean {
            return this.$wait.is('fetching-clinical-summary') || !this.summary
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
            this.$wait.start('fetching-clinical-summary')

            const { data } = await this.$http
                .get<ClinicalSummary>(this.summaryPath)
                .finally(() => this.$wait.end('fetching-clinical-summary'))

            this.summary = data
        },
    },
})
</script>

<template>
    <div class="grid content-start gap-4 sm:grid-cols-2">
        <BaseSummaryCard
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
        </BaseSummaryCard>

        <BaseSummaryCard
            label="Procedures Performed"
            tone="brand"
            :value="performedProceduresCount"
            :is-loading="isLoading"
        >
            <template #icon>
                <Stethoscope />
            </template>
        </BaseSummaryCard>

        <BaseSummaryCard
            label="Prescriptions Issued"
            tone="warning"
            :value="prescriptionsCount"
            :is-loading="isLoading"
        >
            <template #icon>
                <Pill />
            </template>
        </BaseSummaryCard>

        <BaseSummaryCard
            label="Total Revenue"
            tone="success"
            :value="totalRevenue"
            :is-loading="isLoading"
        >
            <template #icon>
                <Banknote />
            </template>
        </BaseSummaryCard>
    </div>
</template>
