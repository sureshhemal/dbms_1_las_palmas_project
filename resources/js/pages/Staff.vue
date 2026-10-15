<script lang="ts">
import { Head } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import Heading from '@/components/Heading.vue'
import StaffTabs from '@/components/staff/StaffTabs.vue'
import { staff } from '@/routes'
import type { StaffCounts } from '@/types'

export default defineComponent({
    name: 'StaffPage',

    components: {
        InertiaHead: Head,
        Heading,
        StaffTabs,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Staff',
                href: staff(),
            },
        ],
    },

    data() {
        return {
            staffCounts: null as StaffCounts | null,
        }
    },

    created() {
        this.fetchStaffCounts()
    },

    methods: {
        async fetchStaffCounts(): Promise<void> {
            this.$wait.start('fetching-staff-counts')

            const { data } = await this.$http
                .get<StaffCounts>('staff')
                .finally(() => this.$wait.end('fetching-staff-counts'))

            this.staffCounts = data
        },
    },
})
</script>

<template>
    <div class="flex h-full flex-1 flex-col p-4">
        <InertiaHead title="Staff" />

        <Heading
            title="Staff"
            description="Physicians and nurses working at Las Palmas Medical Center."
        />

        <StaffTabs
            :staff-counts="staffCounts"
            @staff-changed="fetchStaffCounts"
        />
    </div>
</template>
