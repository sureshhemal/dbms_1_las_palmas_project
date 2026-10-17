<script lang="ts">
import { Head } from '@inertiajs/vue3'
import { BedDouble, CalendarDays, Stethoscope, UserRound } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseSummaryCard from '@/components/base/BaseSummaryCard.vue'
import HospitalClock from '@/components/dashboard/HospitalClock.vue'
import OnCallNursesTable from '@/components/dashboard/OnCallNursesTable.vue'
import TodayAppointmentsTable from '@/components/dashboard/TodayAppointmentsTable.vue'
import TodayDischargesTable from '@/components/dashboard/TodayDischargesTable.vue'
import TodayProceduresTable from '@/components/dashboard/TodayProceduresTable.vue'
import Heading from '@/components/Heading.vue'
import { formatCount } from '@/lib/formatCount'
import { dashboard } from '@/routes'
import type { TodayAtHospital } from '@/types'

const longDateFormatter = new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    timeZone: 'UTC',
})

export default defineComponent({
    name: 'DashboardPage',

    components: {
        BaseSummaryCard,
        BedDouble,
        CalendarDays,
        Heading,
        HospitalClock,
        InertiaHead: Head,
        OnCallNursesTable,
        Stethoscope,
        TodayAppointmentsTable,
        TodayDischargesTable,
        TodayProceduresTable,
        UserRound,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },

    data() {
        return {
            today: null as TodayAtHospital | null,
            now: new Date().toISOString(),
        }
    },

    computed: {
        isLoading(): boolean {
            return this.$wait.is('fetching-dashboard') || !this.today
        },

        description(): string {
            if (!this.today) {
                return 'Loading today…'
            }

            const date = longDateFormatter.format(
                new Date(`${this.today.date}T00:00:00Z`),
            )

            return `${date} · hospital time (${this.$page.props.hospitalTimezone})`
        },
    },

    created() {
        this.fetchToday()
    },

    methods: {
        async fetchToday(): Promise<void> {
            this.$wait.start('fetching-dashboard')

            const { data } = await this.$http
                .get<TodayAtHospital>('dashboard')
                .finally(() => this.$wait.end('fetching-dashboard'))

            this.now = new Date().toISOString()
            this.today = data
        },

        countOf(items: unknown[] | undefined): string {
            return formatCount(items?.length ?? 0)
        },
    },
})
</script>

<template>
    <div class="flex h-full flex-1 flex-col p-4">
        <InertiaHead title="Dashboard" />

        <Heading title="Today at Las Palmas" :description="description" />

        <div class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="grid content-start gap-4 sm:grid-cols-2">
                    <BaseSummaryCard
                        label="Appointments Today"
                        tone="info"
                        :value="countOf(today?.appointments)"
                        :is-loading="isLoading"
                    >
                        <template #icon>
                            <CalendarDays />
                        </template>
                    </BaseSummaryCard>

                    <BaseSummaryCard
                        label="Procedures Today"
                        tone="brand"
                        :value="countOf(today?.procedures)"
                        :is-loading="isLoading"
                    >
                        <template #icon>
                            <Stethoscope />
                        </template>
                    </BaseSummaryCard>

                    <BaseSummaryCard
                        label="Nurses on Call"
                        tone="success"
                        :value="countOf(today?.onCallNurses)"
                        :is-loading="isLoading"
                    >
                        <template #icon>
                            <UserRound />
                        </template>
                    </BaseSummaryCard>

                    <BaseSummaryCard
                        label="Discharges Today"
                        tone="warning"
                        :value="countOf(today?.discharges)"
                        :is-loading="isLoading"
                    >
                        <template #icon>
                            <BedDouble />
                        </template>
                    </BaseSummaryCard>
                </div>

                <HospitalClock :time-zone="$page.props.hospitalTimezone" />
            </div>

            <section class="space-y-3">
                <h3
                    class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                >
                    Today's Appointments
                </h3>

                <TodayAppointmentsTable
                    :appointments="today?.appointments ?? []"
                    :now="now"
                    :is-loading="isLoading"
                />
            </section>

            <section class="space-y-3">
                <h3
                    class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                >
                    Today's Procedures
                </h3>

                <TodayProceduresTable
                    :procedures="today?.procedures ?? []"
                    :is-loading="isLoading"
                />
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="space-y-3">
                    <h3
                        class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                    >
                        Nurses on Call
                    </h3>

                    <OnCallNursesTable
                        :on-call-nurses="today?.onCallNurses ?? []"
                        :is-loading="isLoading"
                    />
                </section>

                <section class="space-y-3">
                    <h3
                        class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                    >
                        Discharges Today
                    </h3>

                    <TodayDischargesTable
                        :discharges="today?.discharges ?? []"
                        :is-loading="isLoading"
                    />
                </section>
            </div>
        </div>
    </div>
</template>
