<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTabs from '@/components/base/BaseTabs.vue'
import NursesTabPanel from '@/components/staff/NursesTabPanel.vue'
import OnCallTabPanel from '@/components/staff/OnCallTabPanel.vue'
import PhysiciansTabPanel from '@/components/staff/PhysiciansTabPanel.vue'
import type { StaffCounts, TabDefinition } from '@/types'

export default defineComponent({
    name: 'StaffTabs',

    components: {
        BaseTabs,
        NursesTabPanel,
        OnCallTabPanel,
        PhysiciansTabPanel,
    },

    props: {
        staffCounts: {
            type: Object as PropType<StaffCounts | null>,
            default: null,
        },
    },

    emits: ['staff-changed'],

    data() {
        return {
            selectedTabName: 'physicians',
        }
    },

    computed: {
        staffTabs(): TabDefinition[] {
            return [
                {
                    name: 'physicians',
                    label: 'Physicians',
                    count: this.staffCounts?.physician_count,
                },
                {
                    name: 'nurses',
                    label: 'Nurses',
                    count: this.staffCounts?.nurse_count,
                },
                {
                    name: 'on-call',
                    label: 'On Call',
                    count: this.staffCounts?.on_call_nurse_count,
                },
            ]
        },
    },
})
</script>

<template>
    <BaseTabs v-model="selectedTabName" :tabs="staffTabs">
        <template #physicians>
            <PhysiciansTabPanel @staff-changed="$emit('staff-changed')" />
        </template>

        <template #nurses>
            <NursesTabPanel @staff-changed="$emit('staff-changed')" />
        </template>

        <template #on-call>
            <OnCallTabPanel @staff-changed="$emit('staff-changed')" />
        </template>
    </BaseTabs>
</template>
