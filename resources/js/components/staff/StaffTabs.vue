<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import NursesTabPanel from '@/components/staff/NursesTabPanel.vue'
import PhysiciansTabPanel from '@/components/staff/PhysiciansTabPanel.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import type { StaffCounts } from '@/types'

export default defineComponent({
    name: 'StaffTabs',

    components: {
        NursesTabPanel,
        PhysiciansTabPanel,
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
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
            staffTabs: [
                {
                    name: 'physicians',
                    label: 'Physicians',
                    countName: 'physician_count' as keyof StaffCounts,
                    panelComponentName: 'PhysiciansTabPanel',
                },
                {
                    name: 'nurses',
                    label: 'Nurses',
                    countName: 'nurse_count' as keyof StaffCounts,
                    panelComponentName: 'NursesTabPanel',
                },
            ],
        }
    },
})
</script>

<template>
    <Tabs v-model="selectedTabName" class="gap-4">
        <TabsList>
            <TabsTrigger
                v-for="staffTab in staffTabs"
                :key="staffTab.name"
                :value="staffTab.name"
                class="px-4 data-[state=active]:bg-brand-700 data-[state=active]:text-white dark:data-[state=active]:bg-brand-400 dark:data-[state=active]:text-brand-950"
            >
                {{ staffTab.label }}
                <span
                    v-if="staffCounts"
                    class="rounded-full bg-neutral-900/10 px-2 text-xs font-semibold tabular-nums dark:bg-white/10"
                >
                    {{ staffCounts[staffTab.countName] }}
                </span>
            </TabsTrigger>
        </TabsList>

        <TabsContent
            v-for="staffTab in staffTabs"
            :key="staffTab.name"
            :value="staffTab.name"
        >
            <component
                :is="staffTab.panelComponentName"
                @staff-changed="$emit('staff-changed')"
            />
        </TabsContent>
    </Tabs>
</template>
