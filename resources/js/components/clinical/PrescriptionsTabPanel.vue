<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import PrescriptionForm from '@/components/clinical/PrescriptionForm.vue'
import PrescriptionsTable from '@/components/clinical/PrescriptionsTable.vue'
import { Button } from '@/components/ui/button'
import { todayIn } from '@/lib/calendarDate'
import type {
    PaginatedResponse,
    PaginationDetails,
    Prescriber,
    Prescription,
    PrescriptionFormData,
    SelectOption,
} from '@/types'

export default defineComponent({
    name: 'PrescriptionsTabPanel',

    components: {
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        Plus,
        PrescriptionForm,
        PrescriptionsTable,
        UiButton: Button,
    },

    data() {
        return {
            prescriptions: [] as Prescription[],
            prescribers: [] as Prescriber[],
            prescriptionYears: [] as number[],
            selectedYears: [] as string[],
            searchTerm: '',
            paginationDetails: {
                current_page: 1,
                per_page: 20,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            newPrescription: null as PrescriptionFormData | null,
            isShowingPrescriptionForm: false,
        }
    },

    computed: {
        today(): string {
            return todayIn(this.$page.props.hospitalTimezone)
        },

        yearOptions(): SelectOption[] {
            return this.prescriptionYears.map((year) => ({
                value: String(year),
                label: String(year),
            }))
        },

        prescriberOptions(): SelectOption[] {
            return this.prescribers.map((physician) => ({
                value: physician.id,
                label: `${physician.name} (${physician.physicianID})`,
            }))
        },

        isFilteringByYears(): boolean {
            return this.selectedYears.length < this.prescriptionYears.length
        },

        prescriptionsEmptyMessage(): string {
            return this.searchTerm || this.isFilteringByYears
                ? 'No prescriptions match your search or filter.'
                : 'No prescriptions yet.'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchPrescriptions(1)
        },
    },

    created() {
        Promise.all([
            this.fetchPrescriptions(),
            this.fetchPrescriptionYears(),
            this.fetchPrescribers(),
        ])
    },

    methods: {
        async fetchPrescriptions(
            page?: number,
            perPage?: number,
        ): Promise<void> {
            if (
                this.prescriptionYears.length > 0 &&
                this.selectedYears.length === 0
            ) {
                this.showNoPrescriptions()

                return
            }

            this.$wait.start('fetching-prescriptions')

            const { data } = await this.$http
                .get<PaginatedResponse<Prescription>>('prescriptions', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                        years: this.isFilteringByYears
                            ? this.selectedYears
                            : undefined,
                    },
                })
                .finally(() => this.$wait.end('fetching-prescriptions'))

            const { data: prescriptions, ...paginationDetails } = data

            this.prescriptions = prescriptions
            this.paginationDetails = paginationDetails
        },

        async fetchPrescriptionYears(): Promise<void> {
            this.$wait.start('fetching-prescription-years')

            const { data } = await this.$http
                .get<number[]>('prescriptions/years')
                .finally(() => this.$wait.end('fetching-prescription-years'))

            this.prescriptionYears = data
            this.selectedYears = data.map(String)
        },

        async fetchPrescribers(): Promise<void> {
            this.$wait.start('fetching-prescribers')

            const { data } = await this.$http
                .get<Prescriber[]>('prescriber-candidates')
                .finally(() => this.$wait.end('fetching-prescribers'))

            this.prescribers = data
        },

        showNoPrescriptions(): void {
            this.prescriptions = []
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

            this.fetchPrescriptions(1)
        },

        addPrescription(): void {
            this.newPrescription = {
                patient_id: '',
                medication_id: '',
                physician_id: '',
                prescribedDate: this.today,
                dose: '',
            }

            this.isShowingPrescriptionForm = true
        },

        closePrescriptionForm(): void {
            this.isShowingPrescriptionForm = false
        },

        async savePrescription(
            prescription: PrescriptionFormData,
        ): Promise<void> {
            this.$wait.start('saving-prescription')

            await this.$http
                .post('prescriptions', prescription)
                .finally(() => this.$wait.end('saving-prescription'))

            this.notifySuccess('Prescription added')
            this.closePrescriptionForm()

            await Promise.all([
                this.fetchPrescriptions(1),
                this.fetchPrescriptionYears(),
            ])
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
                    placeholder="Search by patient, medication or physician"
                />

                <BaseMultiSelect
                    :model-value="selectedYears"
                    :options="yearOptions"
                    placeholder="No years selected"
                    all-selected-label="All years"
                    trigger-class="w-40"
                    @update:model-value="filterByYears"
                />
            </div>

            <UiButton :disabled="$wait.any() > 0" @click="addPrescription">
                <Plus />
                Add Prescription
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchPrescriptions"
            @change-per-page="fetchPrescriptions(1, $event)"
        />

        <PrescriptionsTable
            :prescriptions="prescriptions"
            :empty-message="prescriptionsEmptyMessage"
            :is-loading="$wait.is('fetching-prescriptions')"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchPrescriptions"
            @change-per-page="fetchPrescriptions(1, $event)"
        />

        <BaseSideDrawer
            v-if="newPrescription"
            :is-open="isShowingPrescriptionForm"
            title="Add Prescription"
            @close="closePrescriptionForm"
        >
            <PrescriptionForm
                :prescription="newPrescription"
                :prescriber-options="prescriberOptions"
                :latest-date="today"
                @save="savePrescription"
            />
        </BaseSideDrawer>
    </div>
</template>
