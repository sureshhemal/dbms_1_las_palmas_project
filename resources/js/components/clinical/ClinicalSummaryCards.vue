<script lang="ts">
import {
    Banknote,
    CalendarCheck2,
    CalendarClock,
    CalendarDays,
    Pill,
    Stethoscope,
} from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseSummaryCard from '@/components/base/BaseSummaryCard.vue'
import { formatCost } from '@/lib/formatCost'
import { formatCount } from '@/lib/formatCount'
import { hospitalDateOf, hospitalTimeOf } from '@/lib/hospitalTime'
import type { ClinicalSummary } from '@/types'

export default defineComponent({
    name: 'ClinicalSummaryCards',

    components: {
        Banknote,
        CalendarCheck2,
        BaseSummaryCard,
        CalendarClock,
        CalendarDays,
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

        nextAppointmentDate(): string {
            const nextAppointment = this.summary?.nextAppointment

            return nextAppointment
                ? hospitalDateOf(
                      nextAppointment.startDateTime,
                      this.$page.props.hospitalTimezone,
                  )
                : 'None booked'
        },

        nextAppointmentTime(): string {
            const nextAppointment = this.summary?.nextAppointment
            const timeZone = this.$page.props.hospitalTimezone

            return nextAppointment
                ? `${hospitalTimeOf(nextAppointment.startDateTime, timeZone)} – ${hospitalTimeOf(nextAppointment.endDateTime, timeZone)}`
                : ''
        },

        completedAppointmentsCount(): string {
            return formatCount(this.summary?.completedAppointmentsCount ?? 0)
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
            label="Next Appointment"
            tone="info"
            :value="nextAppointmentDate"
            :detail="nextAppointmentTime"
            :is-muted="!summary?.nextAppointment"
            :is-loading="isLoading"
        >
            <template #icon>
                <CalendarDays />
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
            label="Appointments Completed"
            tone="brand"
            :value="completedAppointmentsCount"
            :is-loading="isLoading"
        >
            <template #icon>
                <CalendarCheck2 />
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
