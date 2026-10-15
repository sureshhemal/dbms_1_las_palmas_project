<script lang="ts">
import { defineComponent } from 'vue'
import BaseTabs from '@/components/base/BaseTabs.vue'
import MedicationsTabPanel from '@/components/clinical/MedicationsTabPanel.vue'
import type { TabDefinition } from '@/types'

export default defineComponent({
    name: 'ClinicalTabs',

    components: {
        BaseTabs,
        MedicationsTabPanel,
    },

    data() {
        return {
            selectedTabName: 'medications',
            clinicalTabs: [
                {
                    name: 'medications',
                    label: 'Medications',
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
    </BaseTabs>
</template>
