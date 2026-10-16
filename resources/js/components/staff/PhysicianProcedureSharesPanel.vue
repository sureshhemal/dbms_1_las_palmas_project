<script lang="ts">
import { defineComponent } from 'vue'
import BaseShareChart from '@/components/base/BaseShareChart.vue'
import type { ProcedureShare, ShareSlice } from '@/types'

export default defineComponent({
    name: 'PhysicianProcedureSharesPanel',

    components: {
        BaseShareChart,
    },

    props: {
        physicianId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            procedureShares: [] as ProcedureShare[],
        }
    },

    computed: {
        slices(): ShareSlice[] {
            return this.procedureShares.map((procedureShare) => ({
                key: procedureShare.procedureId,
                name: procedureShare.name,
                isRemoved: procedureShare.isRemoved,
                count: procedureShare.performedCount,
                percentage: procedureShare.percentage,
            }))
        },
    },

    created() {
        this.fetchProcedureShares()
    },

    methods: {
        async fetchProcedureShares(): Promise<void> {
            this.$wait.start('fetching-physician-procedure-shares')

            const { data } = await this.$http
                .get<ProcedureShare[]>(
                    `physicians/${this.physicianId}/procedures`,
                )
                .finally(() =>
                    this.$wait.end('fetching-physician-procedure-shares'),
                )

            this.procedureShares = data
        },
    },
})
</script>

<template>
    <BaseShareChart
        :slices="slices"
        count-label="procedures"
        item-label="procedures"
        empty-message="No procedures performed yet."
        :is-loading="$wait.is('fetching-physician-procedure-shares')"
    />
</template>
