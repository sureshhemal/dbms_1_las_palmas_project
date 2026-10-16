<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import PatientPerformedProceduresTable from '@/components/patient/PatientPerformedProceduresTable.vue'
import type {
    PaginatedResponse,
    PaginationDetails,
    PatientPerformedProcedure,
} from '@/types'

export default defineComponent({
    name: 'PatientProceduresPanel',

    components: {
        BasePaginationBar,
        PatientPerformedProceduresTable,
    },

    props: {
        patientId: {
            type: String,
            required: true,
        },
        procedureList: {
            type: String as PropType<
                'performed-procedures' | 'upcoming-procedures'
            >,
            required: true,
        },
        heading: {
            type: String,
            required: true,
        },
        emptyMessage: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            performedProcedures: [] as PatientPerformedProcedure[],
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

    computed: {
        fetchingWaiter(): string {
            return `fetching-patient-${this.procedureList}`
        },
    },

    created() {
        this.fetchProcedures()
    },

    methods: {
        async fetchProcedures(page?: number, perPage?: number): Promise<void> {
            this.$wait.start(this.fetchingWaiter)

            const { data } = await this.$http
                .get<PaginatedResponse<PatientPerformedProcedure>>(
                    `patients/${this.patientId}/${this.procedureList}`,
                    {
                        params: {
                            page: page ?? this.paginationDetails.current_page,
                            per_page:
                                perPage ?? this.paginationDetails.per_page,
                        },
                    },
                )
                .finally(() => this.$wait.end(this.fetchingWaiter))

            const { data: performedProcedures, ...paginationDetails } = data

            this.performedProcedures = performedProcedures
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
            {{ heading }}
        </h3>

        <PatientPerformedProceduresTable
            :performed-procedures="performedProcedures"
            :is-loading="$wait.is(fetchingWaiter)"
            :empty-message="emptyMessage"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchProcedures"
            @change-per-page="fetchProcedures(1, $event)"
        />
    </section>
</template>
