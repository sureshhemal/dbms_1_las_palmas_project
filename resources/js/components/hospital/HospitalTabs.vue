<script lang="ts">
import { defineComponent } from 'vue'
import BaseTabs from '@/components/base/BaseTabs.vue'
import DepartmentsTabPanel from '@/components/hospital/DepartmentsTabPanel.vue'
import RoomsTabPanel from '@/components/hospital/RoomsTabPanel.vue'
import type { TabDefinition } from '@/types'

export default defineComponent({
    name: 'HospitalTabs',

    components: {
        BaseTabs,
        DepartmentsTabPanel,
        RoomsTabPanel,
    },

    data() {
        return {
            selectedTabName: 'departments',
            hospitalTabs: [
                {
                    name: 'departments',
                    label: 'Departments',
                },
                {
                    name: 'rooms',
                    label: 'Rooms',
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

        <template #rooms>
            <RoomsTabPanel />
        </template>
    </BaseTabs>
</template>
