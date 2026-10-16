<script lang="ts">
import { BedDouble } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { calendarDateIn } from '@/lib/calendarDate'
import { formatAge } from '@/lib/formatAge'
import { maskSsn } from '@/lib/maskSsn'
import type { PatientProfile } from '@/types'

type PatientDetail = {
    label: string
    value: string | null
    isMonospace?: boolean
}

export default defineComponent({
    name: 'PatientDetailsCard',

    components: {
        BedDouble,
    },

    props: {
        patient: {
            type: Object as PropType<PatientProfile | null>,
            default: null,
        },
        today: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        personalDetails(): PatientDetail[] {
            if (!this.patient) {
                return []
            }

            return [
                {
                    label: 'Patient ID',
                    value: String(this.patient.patientID),
                    isMonospace: true,
                },
                { label: 'Date of Birth', value: this.patient.dob },
                {
                    label: 'Age',
                    value: this.patient.dob
                        ? formatAge(this.patient.dob, this.today)
                        : null,
                },
                { label: 'Phone', value: this.patient.phone },
                { label: 'Address', value: this.patient.address },
                {
                    label: 'Insurance Number',
                    value: this.patient.insuranceNumber
                        ? String(this.patient.insuranceNumber)
                        : null,
                    isMonospace: true,
                },
                {
                    label: 'SSN',
                    value: this.patient.ssn ? maskSsn(this.patient.ssn) : null,
                    isMonospace: true,
                },
            ]
        },

        registeredOn(): string {
            return this.patient?.created_at
                ? calendarDateIn(
                      this.$page.props.hospitalTimezone,
                      new Date(this.patient.created_at),
                  )
                : '—'
        },

        isPrimaryPhysicianOffboarded(): boolean {
            return Boolean(this.patient?.primary_physician?.deleted_at)
        },
    },
})
</script>

<template>
    <section
        class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div
            v-if="isLoading || !patient"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
        >
            <div v-for="placeholder in 8" :key="placeholder" class="space-y-2">
                <div
                    class="h-3 w-1/3 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
                <div
                    class="h-4 w-2/3 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
            </div>
        </div>

        <div v-else class="space-y-6">
            <div
                class="flex items-center gap-3 rounded-lg px-3 py-2"
                :class="
                    patient.current_stay
                        ? 'bg-info-50 text-info-800 dark:bg-info-950 dark:text-info-300'
                        : 'bg-neutral-50 text-neutral-600 dark:bg-neutral-900 dark:text-neutral-400'
                "
            >
                <BedDouble class="size-5 shrink-0" />
                <p v-if="patient.current_stay" class="text-sm">
                    Admitted to
                    <span class="font-semibold">
                        Room {{ patient.current_stay.room.roomID }}
                    </span>
                    ({{ patient.current_stay.room.roomType }}) since
                    {{ patient.current_stay.startDate }}
                    <template v-if="patient.current_stay.endDate">
                        · discharge planned on
                        {{ patient.current_stay.endDate }}
                    </template>
                </p>
                <p v-else class="text-sm">Not admitted right now.</p>
            </div>

            <div>
                <h3
                    class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                >
                    Personal Details
                </h3>

                <dl class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="detail in personalDetails" :key="detail.label">
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

            <div>
                <h3
                    class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                >
                    Care
                </h3>

                <dl class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt
                            class="text-xs text-neutral-500 dark:text-neutral-400"
                        >
                            Primary Physician
                        </dt>
                        <dd
                            class="mt-1 text-sm"
                            :class="
                                !patient.primary_physician ||
                                isPrimaryPhysicianOffboarded
                                    ? 'text-neutral-500 dark:text-neutral-400'
                                    : 'text-neutral-900 dark:text-neutral-100'
                            "
                        >
                            <span
                                :class="{
                                    'line-through':
                                        isPrimaryPhysicianOffboarded,
                                }"
                                :title="
                                    isPrimaryPhysicianOffboarded
                                        ? 'Offboarded'
                                        : undefined
                                "
                            >
                                {{ patient.primary_physician?.name ?? '—' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt
                            class="text-xs text-neutral-500 dark:text-neutral-400"
                        >
                            Registered On
                        </dt>
                        <dd
                            class="mt-1 text-sm text-neutral-900 tabular-nums dark:text-neutral-100"
                        >
                            {{ registeredOn }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
