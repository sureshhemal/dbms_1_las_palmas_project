<script lang="ts">
import { defineComponent } from 'vue'
import BaseTabs from '@/components/base/BaseTabs.vue'
import DepartmentsTabPanel from '@/components/hospital/DepartmentsTabPanel.vue'
import type { TabDefinition } from '@/types'

export default defineComponent({
    name: 'HospitalTabs',

    components: {
        BaseTabs,
        DepartmentsTabPanel,
    },

    data() {
        return {
            selectedTabName: 'departments',
            hospitalTabs: [
                {
                    name: 'departments',
                    label: 'Departments',
                },
            ] as TabDefinition[],
        }
    },
})
</script>

<template>
    <BaseTabs v-model="selectedTabName" :tabs="hospitalTabs">
        <template #departments>
            <DepartmentsTabPanel />
        </template>
    </BaseTabs>
</template>
