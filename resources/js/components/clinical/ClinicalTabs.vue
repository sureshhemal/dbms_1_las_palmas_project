<script lang="ts">
import { defineComponent } from 'vue'
import BaseTabs from '@/components/base/BaseTabs.vue'
import AppointmentsTabPanel from '@/components/clinical/AppointmentsTabPanel.vue'
import MedicationsTabPanel from '@/components/clinical/MedicationsTabPanel.vue'
import PerformedProceduresTabPanel from '@/components/clinical/PerformedProceduresTabPanel.vue'
import PrescriptionsTabPanel from '@/components/clinical/PrescriptionsTabPanel.vue'
import ProceduresTabPanel from '@/components/clinical/ProceduresTabPanel.vue'
import StaysTabPanel from '@/components/clinical/StaysTabPanel.vue'
import type { TabDefinition } from '@/types'

export default defineComponent({
    name: 'ClinicalTabs',

    components: {
        AppointmentsTabPanel,
        BaseTabs,
        MedicationsTabPanel,
        PerformedProceduresTabPanel,
        PrescriptionsTabPanel,
        ProceduresTabPanel,
        StaysTabPanel,
    },

    data() {
        return {
            selectedTabName: 'appointments',
            clinicalTabs: [
                {
                    name: 'appointments',
                    label: 'Appointments',
                },
                {
                    name: 'medications',
                    label: 'Medications',
                },
                {
                    name: 'procedures',
                    label: 'Procedures',
                },
                {
                    name: 'prescriptions',
                    label: 'Prescriptions',
                },
                {
                    name: 'stays',
                    label: 'Stays',
                },
                {
                    name: 'performed-procedures',
                    label: 'Performed Procedures',
                },
            ] as TabDefinition[],
        }
    },
})
</script>

<template>
    <BaseTabs v-model="selectedTabName" :tabs="clinicalTabs">
        <template #appointments>
            <AppointmentsTabPanel />
        </template>

        <template #medications>
            <MedicationsTabPanel />
        </template>

        <template #procedures>
            <ProceduresTabPanel />
        </template>

        <template #prescriptions>
            <PrescriptionsTabPanel />
        </template>

        <template #stays>
            <StaysTabPanel />
        </template>

        <template #performed-procedures>
            <PerformedProceduresTabPanel />
        </template>
    </BaseTabs>
</template>
