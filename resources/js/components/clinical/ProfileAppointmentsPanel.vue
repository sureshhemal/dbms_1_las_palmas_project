<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseTable from '@/components/base/BaseTable.vue'
import ProfileAppointmentsTableRow from '@/components/clinical/ProfileAppointmentsTableRow.vue'
import type {
    Appointment,
    ColumnAlignment,
    PaginatedResponse,
    PaginationDetails,
} from '@/types'

export default defineComponent({
    name: 'ProfileAppointmentsPanel',

    components: {
        BasePaginationBar,
        BaseTable,
        ProfileAppointmentsTableRow,
    },

    props: {
        appointmentsPath: {
            type: String,
            required: true,
        },
        counterpart: {
            type: String as PropType<'patient' | 'physician'>,
            required: true,
        },
    },

    data() {
        return {
            appointments: [] as Appointment[],
            now: new Date().toISOString(),
            paginationDetails: {
                current_page: 1,
                per_page: 10,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            columnAlignments: {
                Date: 'center',
                Time: 'center',
                Status: 'center',
            } as Record<string, ColumnAlignment>,
        }
    },

    computed: {
        columnHeadings(): string[] {
            return [
                'Date',
                'Time',
                'Status',
                this.counterpart === 'physician' ? 'Physician' : 'Patient',
                'Nurse',
            ]
        },
    },

    created() {
        this.fetchAppointments()
    },

    methods: {
        async fetchAppointments(
            page?: number,
            perPage?: number,
        ): Promise<void> {
            this.$wait.start('fetching-profile-appointments')

            const { data } = await this.$http
                .get<PaginatedResponse<Appointment>>(this.appointmentsPath, {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                    },
                })
                .finally(() => this.$wait.end('fetching-profile-appointments'))

            const { data: appointments, ...paginationDetails } = data

            this.now = new Date().toISOString()
            this.appointments = appointments
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
            Appointments
        </h3>

        <BaseTable
            :column-headings="columnHeadings"
            :column-alignments="columnAlignments"
            :is-loading="$wait.is('fetching-profile-appointments')"
            :is-empty="appointments.length === 0"
            empty-message="No appointments yet."
        >
            <ProfileAppointmentsTableRow
                v-for="appointment in appointments"
                :key="appointment.id"
                :appointment="appointment"
                :counterpart="counterpart"
                :now="now"
            />
        </BaseTable>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchAppointments"
            @change-per-page="fetchAppointments(1, $event)"
        />
    </section>
</template>
