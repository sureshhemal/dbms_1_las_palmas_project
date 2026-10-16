<script lang="ts">
import { router } from '@inertiajs/vue3'
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseConfirmDialog from '@/components/base/BaseConfirmDialog.vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import PatientForm from '@/components/patient/PatientForm.vue'
import PatientsTable from '@/components/patient/PatientsTable.vue'
import { Button } from '@/components/ui/button'
import { show } from '@/routes/patients'
import type {
    PaginatedResponse,
    PaginationDetails,
    Patient,
    PatientFormData,
    PrimaryPhysician,
    SelectOption,
} from '@/types'

export default defineComponent({
    name: 'PatientsPanel',

    components: {
        BaseConfirmDialog,
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        PatientForm,
        PatientsTable,
        Plus,
        UiButton: Button,
    },

    data() {
        return {
            patients: [] as Patient[],
            primaryPhysicianCandidates: [] as PrimaryPhysician[],
            searchTerm: '',
            selectedPrimaryPhysicianIds: [] as string[],
            paginationDetails: {
                current_page: 1,
                per_page: 20,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            selectedPatient: null as PatientFormData | null,
            offboardedPrimaryPhysicianOption: null as SelectOption | null,
            isShowingPatientForm: false,
            isConfirmingDeactivation: false,
        }
    },

    computed: {
        primaryPhysicianOptions(): SelectOption[] {
            return this.primaryPhysicianCandidates.map((physician) => ({
                value: physician.id,
                label: `${physician.name} (${physician.physicianID})`,
            }))
        },

        primaryPhysicianFormOptions(): SelectOption[] {
            return this.offboardedPrimaryPhysicianOption
                ? [
                      ...this.primaryPhysicianOptions,
                      this.offboardedPrimaryPhysicianOption,
                  ]
                : this.primaryPhysicianOptions
        },

        isFilteringByPrimaryPhysicians(): boolean {
            return (
                this.selectedPrimaryPhysicianIds.length <
                this.primaryPhysicianCandidates.length
            )
        },

        deactivationConfirmationDescription(): string {
            const patientName = this.selectedPatient?.name || 'This patient'

            return `${patientName} will no longer be served. Their record and history are kept.`
        },

        patientsEmptyMessage(): string {
            return this.searchTerm || this.isFilteringByPrimaryPhysicians
                ? 'No patients match your search or filter.'
                : 'No patients yet.'
        },

        patientFormTitle(): string {
            return this.selectedPatient?.id ? 'Edit Patient' : 'Add Patient'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchPatients(1)
        },
    },

    created() {
        Promise.all([
            this.fetchPatients(),
            this.fetchPrimaryPhysicianCandidates(),
        ])
    },

    methods: {
        openPatient(patient: Patient): void {
            router.visit(show(patient.id))
        },

        async fetchPatients(page?: number, perPage?: number): Promise<void> {
            if (
                this.primaryPhysicianCandidates.length > 0 &&
                this.selectedPrimaryPhysicianIds.length === 0
            ) {
                this.showNoPatients()

                return
            }

            this.$wait.start('fetching-patients')

            const { data } = await this.$http
                .get<PaginatedResponse<Patient>>('patients', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                        primary_physician_ids: this
                            .isFilteringByPrimaryPhysicians
                            ? this.selectedPrimaryPhysicianIds
                            : undefined,
                    },
                })
                .finally(() => this.$wait.end('fetching-patients'))

            const { data: patients, ...paginationDetails } = data

            this.patients = patients
            this.paginationDetails = paginationDetails
        },

        async fetchPrimaryPhysicianCandidates(): Promise<void> {
            this.$wait.start('fetching-primary-physician-candidates')

            const { data } = await this.$http
                .get<PrimaryPhysician[]>('primary-physician-candidates')
                .finally(() =>
                    this.$wait.end('fetching-primary-physician-candidates'),
                )

            this.primaryPhysicianCandidates = data
            this.selectedPrimaryPhysicianIds = data.map(
                (physician) => physician.id,
            )
        },

        showNoPatients(): void {
            this.patients = []
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

        filterByPrimaryPhysicians(physicianIds: string[]): void {
            this.selectedPrimaryPhysicianIds = physicianIds

            this.fetchPatients(1)
        },

        toPatientFormData(patient: Patient): PatientFormData {
            return {
                id: patient.id,
                patientID: String(patient.patientID),
                name: patient.name,
                ssn: patient.ssn ?? '',
                address: patient.address ?? '',
                dob: patient.dob ?? '',
                phone: patient.phone ?? '',
                insuranceNumber:
                    patient.insuranceNumber === null
                        ? ''
                        : String(patient.insuranceNumber),
                primary_physician_id: patient.primary_physician_id ?? '',
            }
        },

        addPatient(): void {
            this.offboardedPrimaryPhysicianOption = null
            this.selectedPatient = {
                id: null,
                patientID: '',
                name: '',
                ssn: '',
                address: '',
                dob: '',
                phone: '',
                insuranceNumber: '',
                primary_physician_id: '',
            }

            this.openPatientForm()
        },

        editPatient(patient: Patient): void {
            const primaryPhysician = patient.primary_physician

            this.offboardedPrimaryPhysicianOption = primaryPhysician?.deleted_at
                ? {
                      value: primaryPhysician.id,
                      label: `${primaryPhysician.name} (${primaryPhysician.physicianID}, offboarded)`,
                  }
                : null
            this.selectedPatient = this.toPatientFormData(patient)

            this.openPatientForm()
        },

        deactivatePatient(patient: Patient): void {
            this.selectedPatient = this.toPatientFormData(patient)

            this.isConfirmingDeactivation = true
        },

        openPatientForm(): void {
            this.isShowingPatientForm = true
        },

        closePatientForm(): void {
            this.isShowingPatientForm = false
        },

        cancelDeactivation(): void {
            this.isConfirmingDeactivation = false
        },

        async savePatient(patient: PatientFormData): Promise<void> {
            const url = patient.id ? `patients/${patient.id}` : 'patients'
            const method = patient.id ? 'put' : 'post'

            this.$wait.start('saving-patient')

            await this.$http[method](url, patient).finally(() =>
                this.$wait.end('saving-patient'),
            )

            this.notifySuccess('Patient saved')
            this.closePatientForm()

            await this.fetchPatients()
        },

        async deactivateSelectedPatient(): Promise<void> {
            if (!this.selectedPatient?.id) {
                return
            }

            this.$wait.start('deactivating-patient')

            await this.$http
                .delete(`patients/${this.selectedPatient.id}`)
                .finally(() => this.$wait.end('deactivating-patient'))

            this.notifySuccess('Patient deactivated')
            this.isConfirmingDeactivation = false

            await this.fetchPatients()
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
                    placeholder="Search by name, ID, phone or insurance number"
                />

                <BaseMultiSelect
                    :model-value="selectedPrimaryPhysicianIds"
                    :options="primaryPhysicianOptions"
                    placeholder="No physicians selected"
                    all-selected-label="All primary physicians"
                    trigger-class="w-64"
                    @update:model-value="filterByPrimaryPhysicians"
                />
            </div>

            <UiButton :disabled="$wait.any() > 0" @click="addPatient">
                <Plus />
                Add Patient
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchPatients"
            @change-per-page="fetchPatients(1, $event)"
        />

        <PatientsTable
            :patients="patients"
            :empty-message="patientsEmptyMessage"
            :is-loading="$wait.is('fetching-patients')"
            @edit="editPatient"
            @deactivate="deactivatePatient"
            @open="openPatient"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchPatients"
            @change-per-page="fetchPatients(1, $event)"
        />

        <BaseSideDrawer
            v-if="selectedPatient"
            :is-open="isShowingPatientForm"
            :title="patientFormTitle"
            @close="closePatientForm"
        >
            <PatientForm
                :patient="selectedPatient"
                :primary-physician-options="primaryPhysicianFormOptions"
                @save="savePatient"
            />
        </BaseSideDrawer>

        <BaseConfirmDialog
            :is-open="isConfirmingDeactivation"
            title="Deactivate patient?"
            :description="deactivationConfirmationDescription"
            confirm-label="Deactivate"
            @confirm="deactivateSelectedPatient"
            @cancel="cancelDeactivation"
        />
    </div>
</template>
