<script lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import ClinicalSummaryCards from '@/components/clinical/ClinicalSummaryCards.vue'
import Heading from '@/components/Heading.vue'
import PhysicianDetailsCard from '@/components/staff/PhysicianDetailsCard.vue'
import PhysicianPrescriptionsPanel from '@/components/staff/PhysicianPrescriptionsPanel.vue'
import PhysicianProceduresPanel from '@/components/staff/PhysicianProceduresPanel.vue'
import PhysicianProcedureSharesPanel from '@/components/staff/PhysicianProcedureSharesPanel.vue'
import { staff } from '@/routes'
import { show } from '@/routes/staff/physicians'
import type { PhysicianProfile } from '@/types'

export default defineComponent({
    name: 'PhysicianPage',

    components: {
        ClinicalSummaryCards,
        Heading,
        InertiaHead: Head,
        PhysicianDetailsCard,
        PhysicianPrescriptionsPanel,
        PhysicianProcedureSharesPanel,
        PhysicianProceduresPanel,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Staff',
                href: staff(),
            },
        ],
    },

    props: {
        physicianId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            physician: null as PhysicianProfile | null,
        }
    },

    computed: {
        title(): string {
            return this.physician?.name ?? 'Physician'
        },
    },

    created() {
        this.fetchPhysician()
    },

    methods: {
        async fetchPhysician(): Promise<void> {
            this.$wait.start('fetching-physician')

            const { data } = await this.$http
                .get<PhysicianProfile>(`physicians/${this.physicianId}`)
                .finally(() => this.$wait.end('fetching-physician'))

            this.physician = data

            setLayoutProps({
                breadcrumbs: [
                    {
                        title: 'Staff',
                        href: staff(),
                    },
                    {
                        title: data.name,
                        href: show(this.physicianId),
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
                <ClinicalSummaryCards
                    :summary-path="`physicians/${physicianId}/summary`"
                />

                <PhysicianProcedureSharesPanel :physician-id="physicianId" />
            </div>

            <PhysicianDetailsCard
                :physician="physician"
                :is-loading="$wait.is('fetching-physician')"
            />

            <PhysicianProceduresPanel
                :physician-id="physicianId"
                procedure-list="upcoming-procedures"
                heading="Upcoming Procedures"
                empty-message="No procedures scheduled."
            />

            <PhysicianProceduresPanel
                :physician-id="physicianId"
                procedure-list="performed-procedures"
                heading="Performed Procedures"
                empty-message="No procedures performed yet."
            />

            <PhysicianPrescriptionsPanel :physician-id="physicianId" />
        </div>
    </div>
</template>
