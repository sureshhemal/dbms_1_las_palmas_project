<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { calendarDateIn } from '@/lib/calendarDate'
import { formatCount } from '@/lib/formatCount'
import { maskSsn } from '@/lib/maskSsn'
import type { PhysicianProfile } from '@/types'

type PhysicianDetail = {
    label: string
    value: string | null
    isMonospace?: boolean
}

export default defineComponent({
    name: 'PhysicianDetailsCard',

    props: {
        physician: {
            type: Object as PropType<PhysicianProfile | null>,
            default: null,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        detailGroups(): { heading: string; details: PhysicianDetail[] }[] {
            if (!this.physician) {
                return []
            }

            const departments = this.physician.departments ?? []
            const headedDepartments = this.physician.headed_departments ?? []
            const primaryPatientsCount = this.physician.primary_patients_count

            return [
                {
                    heading: 'Personal Details',
                    details: [
                        {
                            label: 'Physician ID',
                            value: String(this.physician.physicianID),
                            isMonospace: true,
                        },
                        { label: 'Position', value: this.physician.position },
                        {
                            label: 'SSN',
                            value: this.physician.ssn
                                ? maskSsn(this.physician.ssn)
                                : null,
                            isMonospace: true,
                        },
                        { label: 'Joined On', value: this.joinedOn },
                    ],
                },
                {
                    heading: 'Work',
                    details: [
                        {
                            label: 'Departments',
                            value:
                                departments
                                    .map((department) => department.name)
                                    .join(', ') || null,
                        },
                        {
                            label: 'Heads',
                            value:
                                headedDepartments
                                    .map((department) => department.name)
                                    .join(', ') || null,
                        },
                        {
                            label: 'Primary Physician Of',
                            value: `${formatCount(primaryPatientsCount)} ${primaryPatientsCount === 1 ? 'patient' : 'patients'}`,
                        },
                    ],
                },
            ]
        },

        joinedOn(): string | null {
            return this.physician?.created_at
                ? calendarDateIn(
                      this.$page.props.hospitalTimezone,
                      new Date(this.physician.created_at),
                  )
                : null
        },
    },
})
</script>

<template>
    <section
        class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div
            v-if="isLoading || !physician"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
        >
            <div v-for="placeholder in 7" :key="placeholder" class="space-y-2">
                <div
                    class="h-3 w-1/3 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
                <div
                    class="h-4 w-2/3 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
            </div>
        </div>

        <div v-else class="space-y-6">
            <div v-for="group in detailGroups" :key="group.heading">
                <h3
                    class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                >
                    {{ group.heading }}
                </h3>

                <dl class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="detail in group.details" :key="detail.label">
                        <dt
                            class="text-xs text-neutral-500 dark:text-neutral-400"
                        >
                            {{ detail.label }}
                        </dt>
                        <dd
                            class="mt-1 text-sm"
                            :class="[
                                detail.value
                                    ? 'text-neutral-900 dark:text-neutral-100'
                                    : 'text-neutral-500 dark:text-neutral-400',
                                { 'font-mono': detail.isMonospace },
                            ]"
                        >
                            {{ detail.value ?? '—' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
