<script lang="ts">
import { defineComponent } from 'vue'
import BaseTabs from '@/components/base/BaseTabs.vue'
import MedicationsTabPanel from '@/components/clinical/MedicationsTabPanel.vue'
import PrescriptionsTabPanel from '@/components/clinical/PrescriptionsTabPanel.vue'
import ProceduresTabPanel from '@/components/clinical/ProceduresTabPanel.vue'
import StaysTabPanel from '@/components/clinical/StaysTabPanel.vue'
import type { TabDefinition } from '@/types'

export default defineComponent({
    name: 'ClinicalTabs',

    components: {
        BaseTabs,
        MedicationsTabPanel,
        PrescriptionsTabPanel,
        ProceduresTabPanel,
        StaysTabPanel,
    },

    data() {
        return {
            selectedTabName: 'medications',
            clinicalTabs: [
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
            ] as TabDefinition[],
        }
    },
})
</script>

<template>
    <BaseTabs v-model="selectedTabName" :tabs="clinicalTabs">
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
    </BaseTabs>
</template>
