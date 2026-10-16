<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import AdmissionForm from '@/components/clinical/AdmissionForm.vue'
import DischargeForm from '@/components/clinical/DischargeForm.vue'
import StaysTable from '@/components/clinical/StaysTable.vue'
import { Button } from '@/components/ui/button'
import { todayIn } from '@/lib/calendarDate'
import type {
    AdmissionFormData,
    PaginatedResponse,
    PaginationDetails,
    SelectOption,
    Stay,
} from '@/types'

const stayStatuses: SelectOption[] = [
    { value: 'current', label: 'Currently admitted' },
    { value: 'discharged', label: 'Discharged' },
]

export default defineComponent({
    name: 'StaysTabPanel',

    components: {
        AdmissionForm,
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        DischargeForm,
        Plus,
        StaysTable,
        UiButton: Button,
    },

    data() {
        return {
            stays: [] as Stay[],
            searchTerm: '',
            stayStatuses,
            selectedStatuses: stayStatuses.map((status) => status.value),
            paginationDetails: {
                current_page: 1,
                per_page: 20,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            newAdmission: null as AdmissionFormData | null,
            isShowingAdmissionForm: false,
            stayBeingDischarged: null as Stay | null,
            isShowingDischargeForm: false,
        }
    },

    computed: {
        today(): string {
            return todayIn(this.$page.props.hospitalTimezone)
        },

        statusFilter(): string | undefined {
            return this.selectedStatuses.length === 1
                ? this.selectedStatuses[0]
                : undefined
        },

        staysEmptyMessage(): string {
            return this.searchTerm || this.statusFilter
                ? 'No stays match your search or filter.'
                : 'No stays yet.'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchStays(1)
        },

        selectedStatuses(): void {
            this.fetchStays(1)
        },
    },

    created() {
        this.fetchStays()
    },

    methods: {
        async fetchStays(page?: number, perPage?: number): Promise<void> {
            if (this.selectedStatuses.length === 0) {
                this.showNoStays()

                return
            }

            this.$wait.start('fetching-stays')

            const { data } = await this.$http
                .get<PaginatedResponse<Stay>>('stays', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                        status: this.statusFilter,
                    },
                })
                .finally(() => this.$wait.end('fetching-stays'))

            const { data: stays, ...paginationDetails } = data

            this.stays = stays
            this.paginationDetails = paginationDetails
        },

        showNoStays(): void {
            this.stays = []
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

        admitPatient(): void {
            this.newAdmission = {
                patient_id: '',
                room_id: '',
                startDate: this.today,
                endDate: '',
            }

            this.isShowingAdmissionForm = true
        },

        closeAdmissionForm(): void {
            this.isShowingAdmissionForm = false
        },

        async saveAdmission(admission: AdmissionFormData): Promise<void> {
            this.$wait.start('admitting-patient')

            await this.$http
                .post('stays', admission)
                .finally(() => this.$wait.end('admitting-patient'))

            this.notifySuccess('Patient admitted')
            this.closeAdmissionForm()

            await this.fetchStays(1)
        },

        dischargePatient(stay: Stay): void {
            this.stayBeingDischarged = stay
            this.isShowingDischargeForm = true
        },

        closeDischargeForm(): void {
            this.isShowingDischargeForm = false
        },

        async saveDischarge(endDate: string): Promise<void> {
            if (!this.stayBeingDischarged) {
                return
            }

            this.$wait.start('discharging-patient')

            await this.$http
                .patch(`stays/${this.stayBeingDischarged.id}/discharge`, {
                    endDate,
                })
                .finally(() => this.$wait.end('discharging-patient'))

            this.notifySuccess('Patient discharged')
            this.closeDischargeForm()

            await this.fetchStays()
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
                    placeholder="Search by patient or room"
                />

                <BaseMultiSelect
                    v-model="selectedStatuses"
                    :options="stayStatuses"
                    placeholder="No statuses selected"
                    all-selected-label="All stays"
                />
            </div>

            <UiButton :disabled="$wait.any() > 0" @click="admitPatient">
                <Plus />
                Admit Patient
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchStays"
            @change-per-page="fetchStays(1, $event)"
        />

        <StaysTable
            :stays="stays"
            :empty-message="staysEmptyMessage"
            :is-loading="$wait.is('fetching-stays')"
            @discharge="dischargePatient"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchStays"
            @change-per-page="fetchStays(1, $event)"
        />

        <BaseSideDrawer
            v-if="newAdmission"
            :is-open="isShowingAdmissionForm"
            title="Admit Patient"
            @close="closeAdmissionForm"
        >
            <AdmissionForm
                :admission="newAdmission"
                :latest-date="today"
                @save="saveAdmission"
            />
        </BaseSideDrawer>

        <BaseSideDrawer
            v-if="stayBeingDischarged"
            :is-open="isShowingDischargeForm"
            title="Discharge Patient"
            @close="closeDischargeForm"
        >
            <DischargeForm
                :key="stayBeingDischarged.id"
                :stay="stayBeingDischarged"
                :latest-date="today"
                @save="saveDischarge"
            />
        </BaseSideDrawer>
    </div>
</template>
