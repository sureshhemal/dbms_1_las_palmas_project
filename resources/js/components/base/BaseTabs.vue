<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import type { TabDefinition } from '@/types'

export default defineComponent({
    name: 'BaseTabs',

    components: {
        TabsContent,
        TabsList,
        TabsTrigger,
        UiTabs: Tabs,
    },

    props: {
        modelValue: {
            type: String,
            required: true,
        },
        tabs: {
            type: Array as PropType<TabDefinition[]>,
            required: true,
        },
    },

    emits: ['update:modelValue'],

    methods: {
        hasCount(tab: TabDefinition): boolean {
            return tab.count !== undefined && tab.count !== null
        },
    },
})
</script>

<template>
    <UiTabs
        :model-value="modelValue"
        class="gap-4"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <TabsList>
            <TabsTrigger
                v-for="tab in tabs"
                :key="tab.name"
                :value="tab.name"
                class="px-4 data-[state=active]:bg-brand-700 data-[state=active]:text-white dark:data-[state=active]:bg-brand-400 dark:data-[state=active]:text-brand-950"
            >
                {{ tab.label }}
                <span
                    v-if="hasCount(tab)"
                    class="rounded-full bg-neutral-900/10 px-2 text-xs font-semibold tabular-nums dark:bg-white/10"
                >
                    {{ tab.count }}
                </span>
            </TabsTrigger>
        </TabsList>

        <TabsContent v-for="tab in tabs" :key="tab.name" :value="tab.name">
            <slot :name="tab.name" />
        </TabsContent>
    </UiTabs>
</template>
