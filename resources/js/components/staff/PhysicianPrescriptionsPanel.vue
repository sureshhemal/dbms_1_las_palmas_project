<script lang="ts">
import { defineComponent } from 'vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import PhysicianPrescriptionsTable from '@/components/staff/PhysicianPrescriptionsTable.vue'
import type {
    PaginatedResponse,
    PaginationDetails,
    PhysicianPrescription,
} from '@/types'

export default defineComponent({
    name: 'PhysicianPrescriptionsPanel',

    components: {
        BasePaginationBar,
        PhysicianPrescriptionsTable,
    },

    props: {
        physicianId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            prescriptions: [] as PhysicianPrescription[],
            paginationDetails: {
                current_page: 1,
                per_page: 10,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
        }
    },

    created() {
        this.fetchPrescriptions()
    },

    methods: {
        async fetchPrescriptions(
            page?: number,
            perPage?: number,
        ): Promise<void> {
            this.$wait.start('fetching-physician-prescriptions')

            const { data } = await this.$http
                .get<PaginatedResponse<PhysicianPrescription>>(
                    `physicians/${this.physicianId}/prescriptions`,
                    {
                        params: {
                            page: page ?? this.paginationDetails.current_page,
                            per_page:
                                perPage ?? this.paginationDetails.per_page,
                        },
                    },
                )
                .finally(() =>
                    this.$wait.end('fetching-physician-prescriptions'),
                )

            const { data: prescriptions, ...paginationDetails } = data

            this.prescriptions = prescriptions
            this.paginationDetails = paginationDetails
        },
    },
})
</script>

<template>
    <section class="space-y-3">
        <h3
            class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
        >
            Prescriptions
        </h3>

        <PhysicianPrescriptionsTable
            :prescriptions="prescriptions"
            :is-loading="$wait.is('fetching-physician-prescriptions')"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchPrescriptions"
            @change-per-page="fetchPrescriptions(1, $event)"
        />
    </section>
</template>
