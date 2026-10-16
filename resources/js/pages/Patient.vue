<script lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import Heading from '@/components/Heading.vue'
import PatientDetailsCard from '@/components/patient/PatientDetailsCard.vue'
import PatientMedicationsPanel from '@/components/patient/PatientMedicationsPanel.vue'
import PatientPrescriptionsPanel from '@/components/patient/PatientPrescriptionsPanel.vue'
import PatientProceduresPanel from '@/components/patient/PatientProceduresPanel.vue'
import PatientSummaryCards from '@/components/patient/PatientSummaryCards.vue'
import { todayIn } from '@/lib/calendarDate'
import { patients } from '@/routes'
import { show } from '@/routes/patients'
import type { PatientProfile } from '@/types'

export default defineComponent({
    name: 'PatientPage',

    components: {
        Heading,
        InertiaHead: Head,
        PatientDetailsCard,
        PatientMedicationsPanel,
        PatientPrescriptionsPanel,
        PatientProceduresPanel,
        PatientSummaryCards,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Patients',
                href: patients(),
            },
        ],
    },

    props: {
        patientId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            patient: null as PatientProfile | null,
        }
    },

    computed: {
        title(): string {
            return this.patient?.name ?? 'Patient'
        },

        today(): string {
            return todayIn(this.$page.props.hospitalTimezone)
        },
    },

    created() {
        this.fetchPatient()
    },

    methods: {
        async fetchPatient(): Promise<void> {
            this.$wait.start('fetching-patient')

            const { data } = await this.$http
                .get<PatientProfile>(`patients/${this.patientId}`)
                .finally(() => this.$wait.end('fetching-patient'))

            this.patient = data

            setLayoutProps({
                breadcrumbs: [
                    {
                        title: 'Patients',
                        href: patients(),
                    },
                    {
                        title: data.name,
                        href: show(this.patientId),
                    },
                ],
            })
        },
    },
})
</script>

<template>
    <div class="flex h-full flex-1 flex-col p-4">
        <InertiaHead :title="title" />

        <Heading :title="title" />

        <div class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <PatientSummaryCards :patient-id="patientId" />

                <PatientMedicationsPanel :patient-id="patientId" />
            </div>

            <PatientDetailsCard
                :patient="patient"
                :today="today"
                :is-loading="$wait.is('fetching-patient')"
            />

            <PatientProceduresPanel
                :patient-id="patientId"
                procedure-list="upcoming-procedures"
                heading="Upcoming Procedures"
                empty-message="No procedures scheduled."
            />

            <PatientProceduresPanel
                :patient-id="patientId"
                procedure-list="performed-procedures"
                heading="Performed Procedures"
                empty-message="No procedures performed yet."
            />

            <PatientPrescriptionsPanel :patient-id="patientId" />
        </div>
    </div>
</template>
