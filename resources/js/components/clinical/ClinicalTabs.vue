<script lang="ts">
import { defineComponent } from 'vue'
import BaseTabs from '@/components/base/BaseTabs.vue'
import MedicationsTabPanel from '@/components/clinical/MedicationsTabPanel.vue'
import ProceduresTabPanel from '@/components/clinical/ProceduresTabPanel.vue'
import type { TabDefinition } from '@/types'

export default defineComponent({
    name: 'ClinicalTabs',

    components: {
        BaseTabs,
        MedicationsTabPanel,
        ProceduresTabPanel,
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
    </BaseTabs>
</template>
