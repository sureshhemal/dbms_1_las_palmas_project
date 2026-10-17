<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseConfirmDialog from '@/components/base/BaseConfirmDialog.vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import AppointmentForm from '@/components/clinical/AppointmentForm.vue'
import AppointmentsTable from '@/components/clinical/AppointmentsTable.vue'
import { Button } from '@/components/ui/button'
import { todayIn } from '@/lib/calendarDate'
import { hospitalDateOf, hospitalTimeOf } from '@/lib/hospitalTime'
import type {
    Appointment,
    AppointmentFormData,
    NurseSummary,
    PaginatedResponse,
    PaginationDetails,
    Prescriber,
    SelectOption,
} from '@/types'

const appointmentStatuses: SelectOption[] = [
    { value: 'upcoming', label: 'Upcoming' },
    { value: 'in_progress', label: 'In progress' },
    { value: 'completed', label: 'Completed' },
]

export default defineComponent({
    name: 'AppointmentsTabPanel',

    components: {
        AppointmentForm,
        AppointmentsTable,
        BaseConfirmDialog,
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        Plus,
        UiButton: Button,
    },

    data() {
        return {
            appointments: [] as Appointment[],
            physicians: [] as Prescriber[],
            nurses: [] as NurseSummary[],
            appointmentYears: [] as number[],
            selectedYears: [] as string[],
            appointmentStatuses,
            selectedStatuses: appointmentStatuses.map((status) => status.value),
            selectedPhysicianIds: [] as string[],
            searchTerm: '',
            now: new Date().toISOString(),
            paginationDetails: {
                current_page: 1,
                per_page: 20,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            selectedAppointment: null as AppointmentFormData | null,
            selectedPatientOption: null as SelectOption | null,
            isShowingAppointmentForm: false,
            appointmentToCancel: null as Appointment | null,
            isConfirmingCancellation: false,
        }
    },

    computed: {
        timeZone(): string {
            return this.$page.props.hospitalTimezone
        },

        yearOptions(): SelectOption[] {
            return this.appointmentYears.map((year) => ({
                value: String(year),
                label: String(year),
            }))
        },

        physicianOptions(): SelectOption[] {
            return this.physicians.map((physician) => ({
                value: physician.id,
                label: `${physician.name} (${physician.physicianID})`,
            }))
        },

        nurseOptions(): SelectOption[] {
            return this.nurses.map((nurse) => ({
                value: nurse.id,
                label: `${nurse.name} (${nurse.nurseID})`,
            }))
        },

        isFilteringByYears(): boolean {
            return this.selectedYears.length < this.appointmentYears.length
        },

        physicianFilterOptions(): SelectOption[] {
            return this.physicians.map((physician) => ({
                value: physician.id,
                label: physician.name,
            }))
        },

        isFilteringByPhysicians(): boolean {
            return this.selectedPhysicianIds.length < this.physicians.length
        },

        isFilteringByStatus(): boolean {
            return this.selectedStatuses.length < appointmentStatuses.length
        },

        appointmentsEmptyMessage(): string {
            return this.searchTerm ||
                this.isFilteringByYears ||
                this.isFilteringByStatus ||
                this.isFilteringByPhysicians
                ? 'No appointments match your search or filter.'
                : 'No appointments yet.'
        },

        appointmentFormTitle(): string {
            return this.selectedAppointment?.id
                ? 'Edit Appointment'
                : 'Add Appointment'
        },

        cancellationDescription(): string {
            if (!this.appointmentToCancel) {
                return ''
            }

            const date = hospitalDateOf(
                this.appointmentToCancel.startDateTime,
                this.timeZone,
            )
            const time = hospitalTimeOf(
                this.appointmentToCancel.startDateTime,
                this.timeZone,
            )

            return `${this.appointmentToCancel.patient.name}'s appointment on ${date} at ${time} will be cancelled. Its record is kept.`
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchAppointments(1)
        },
    },

    created() {
        Promise.all([
            this.fetchAppointments(),
            this.fetchAppointmentYears(),
            this.fetchPhysicians(),
            this.fetchNurses(),
        ])
    },

    methods: {
        async fetchAppointments(
            page?: number,
            perPage?: number,
        ): Promise<void> {
            if (
                (this.appointmentYears.length > 0 &&
                    this.selectedYears.length === 0) ||
                this.selectedStatuses.length === 0 ||
                (this.physicians.length > 0 &&
                    this.selectedPhysicianIds.length === 0)
            ) {
                this.showNoAppointments()

                return
            }

            this.$wait.start('fetching-appointments')

            const { data } = await this.$http
                .get<PaginatedResponse<Appointment>>('appointments', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                        years: this.isFilteringByYears
                            ? this.selectedYears
                            : undefined,
                        statuses: this.isFilteringByStatus
                            ? this.selectedStatuses
                            : undefined,
                        physician_ids: this.isFilteringByPhysicians
                            ? this.selectedPhysicianIds
                            : undefined,
                    },
                })
                .finally(() => this.$wait.end('fetching-appointments'))

            const { data: appointments, ...paginationDetails } = data

            this.now = new Date().toISOString()
            this.appointments = appointments
            this.paginationDetails = paginationDetails
        },

        async fetchAppointmentYears(): Promise<void> {
            this.$wait.start('fetching-appointment-years')

            const { data } = await this.$http
                .get<number[]>('appointments/years')
                .finally(() => this.$wait.end('fetching-appointment-years'))

            this.appointmentYears = data
            this.selectedYears = data.map(String)
        },

        async fetchPhysicians(): Promise<void> {
            this.$wait.start('fetching-appointment-physicians')

            const { data } = await this.$http
                .get<Prescriber[]>('prescriber-candidates')
                .finally(() =>
                    this.$wait.end('fetching-appointment-physicians'),
                )

            this.physicians = data
            this.selectedPhysicianIds = data.map((physician) => physician.id)
        },

        async fetchNurses(): Promise<void> {
            this.$wait.start('fetching-appointment-nurses')

            const { data } = await this.$http
                .get<NurseSummary[]>('on-call-nurse-candidates')
                .finally(() => this.$wait.end('fetching-appointment-nurses'))

            this.nurses = data
        },

        showNoAppointments(): void {
            this.appointments = []
            this.paginationDetails = {
                ...this.paginationDetails,
                current_page: 1,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            }
        },

        filterByYears(years: string[]): void {
            this.selectedYears = years

            this.fetchAppointments(1)
        },

        filterByStatuses(statuses: string[]): void {
            this.selectedStatuses = statuses

            this.fetchAppointments(1)
        },

        filterByPhysicians(physicianIds: string[]): void {
            this.selectedPhysicianIds = physicianIds

            this.fetchAppointments(1)
        },

        addAppointment(): void {
            this.selectedPatientOption = null
            this.selectedAppointment = {
                id: null,
                patient_id: '',
                physician_id: '',
                nurse_id: '',
                date: todayIn(this.timeZone),
                startTime: '09:00',
                endTime: '09:30',
            }

            this.isShowingAppointmentForm = true
        },

        editAppointment(appointment: Appointment): void {
            this.selectedPatientOption = {
                value: appointment.patient.id,
                label: `${appointment.patient.name} (${appointment.patient.patientID})`,
            }
            this.selectedAppointment = {
                id: appointment.id,
                patient_id: appointment.patient.id,
                physician_id: appointment.physician.id,
                nurse_id: appointment.nurse.id,
                date: hospitalDateOf(appointment.startDateTime, this.timeZone),
                startTime: hospitalTimeOf(
                    appointment.startDateTime,
                    this.timeZone,
                ),
                endTime: hospitalTimeOf(appointment.endDateTime, this.timeZone),
            }

            this.isShowingAppointmentForm = true
        },

        closeAppointmentForm(): void {
            this.isShowingAppointmentForm = false
        },

        async saveAppointment(appointment: AppointmentFormData): Promise<void> {
            const url = appointment.id
                ? `appointments/${appointment.id}`
                : 'appointments'
            const method = appointment.id ? 'put' : 'post'

            this.$wait.start('saving-appointment')

            await this.$http[method](url, appointment).finally(() =>
                this.$wait.end('saving-appointment'),
            )

            this.notifySuccess(
                appointment.id ? 'Appointment saved' : 'Appointment added',
            )
            this.closeAppointmentForm()

            await Promise.all([
                this.fetchAppointments(appointment.id ? undefined : 1),
                this.fetchAppointmentYears(),
            ])
        },

        cancelAppointment(appointment: Appointment): void {
            this.appointmentToCancel = appointment
            this.isConfirmingCancellation = true
        },

        keepAppointment(): void {
            this.isConfirmingCancellation = false
        },

        async cancelSelectedAppointment(): Promise<void> {
            if (!this.appointmentToCancel) {
                return
            }

            this.$wait.start('cancelling-appointment')

            await this.$http
                .delete(`appointments/${this.appointmentToCancel.id}`)
                .finally(() => this.$wait.end('cancelling-appointment'))

            this.notifySuccess('Appointment cancelled')
            this.isConfirmingCancellation = false

            await this.fetchAppointments()
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-1 items-center gap-2">
                <BaseSearchInput
                    v-model="searchTerm"
                    placeholder="Search by patient name or ID"
                />

                <BaseMultiSelect
                    :model-value="selectedYears"
                    :options="yearOptions"
                    placeholder="No years selected"
                    all-selected-label="All years"
                    trigger-class="w-40"
                    @update:model-value="filterByYears"
                />

                <BaseMultiSelect
                    :model-value="selectedPhysicianIds"
                    :options="physicianFilterOptions"
                    placeholder="No physicians selected"
                    all-selected-label="All physicians"
                    trigger-class="w-48"
                    @update:model-value="filterByPhysicians"
                />

                <BaseMultiSelect
                    :model-value="selectedStatuses"
                    :options="appointmentStatuses"
                    placeholder="No statuses selected"
                    all-selected-label="All statuses"
                    trigger-class="w-44"
                    @update:model-value="filterByStatuses"
                />
            </div>

            <UiButton :disabled="$wait.any() > 0" @click="addAppointment">
                <Plus />
                Add Appointment
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchAppointments"
            @change-per-page="fetchAppointments(1, $event)"
        />

        <AppointmentsTable
            :appointments="appointments"
            :now="now"
            :empty-message="appointmentsEmptyMessage"
            :is-loading="$wait.is('fetching-appointments')"
            @edit="editAppointment"
            @cancel="cancelAppointment"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchAppointments"
            @change-per-page="fetchAppointments(1, $event)"
        />

        <BaseSideDrawer
            v-if="selectedAppointment"
            :is-open="isShowingAppointmentForm"
            :title="appointmentFormTitle"
            @close="closeAppointmentForm"
        >
            <AppointmentForm
                :appointment="selectedAppointment"
                :patient-option="selectedPatientOption"
                :physician-options="physicianOptions"
                :nurse-options="nurseOptions"
                @save="saveAppointment"
            />
        </BaseSideDrawer>

        <BaseConfirmDialog
            :is-open="isConfirmingCancellation"
            title="Cancel appointment?"
            :description="cancellationDescription"
            confirm-label="Cancel appointment"
            dismiss-label="Keep appointment"
            @confirm="cancelSelectedAppointment"
            @cancel="keepAppointment"
        />
    </div>
</template>
