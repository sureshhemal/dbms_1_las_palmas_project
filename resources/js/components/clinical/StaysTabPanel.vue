<script lang="ts">
import { defineComponent } from 'vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import DischargeForm from '@/components/clinical/DischargeForm.vue'
import ExtendStayForm from '@/components/clinical/ExtendStayForm.vue'
import StayProcedureForm from '@/components/clinical/StayProcedureForm.vue'
import StaysTable from '@/components/clinical/StaysTable.vue'
import { todayIn } from '@/lib/calendarDate'
import type {
    PaginatedResponse,
    PaginationDetails,
    ProcedureDetailsFormData,
    SelectOption,
    Stay,
} from '@/types'

type StayAction = 'add-procedure' | 'extend' | 'discharge'

const stayStatuses: SelectOption[] = [
    { value: 'admitted', label: 'Admitted' },
    { value: 'scheduled', label: 'Scheduled' },
    { value: 'discharged', label: 'Discharged' },
]

const drawerTitles: Record<StayAction, string> = {
    'add-procedure': 'Add Procedure',
    extend: 'Extend Stay',
    discharge: 'Discharge Patient',
}

export default defineComponent({
    name: 'StaysTabPanel',

    components: {
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        DischargeForm,
        ExtendStayForm,
        StayProcedureForm,
        StaysTable,
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
            selectedStay: null as Stay | null,
            currentAction: null as StayAction | null,
            isShowingDrawer: false,
        }
    },

    computed: {
        today(): string {
            return todayIn(this.$page.props.hospitalTimezone)
        },

        isFilteringByStatus(): boolean {
            return this.selectedStatuses.length < stayStatuses.length
        },

        drawerTitle(): string {
            return this.currentAction ? drawerTitles[this.currentAction] : ''
        },

        staysEmptyMessage(): string {
            return this.searchTerm || this.isFilteringByStatus
                ? 'No stays match your search or filter.'
                : 'No stays yet. Patients are admitted when a procedure is recorded.'
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
                        statuses: this.isFilteringByStatus
                            ? this.selectedStatuses
                            : undefined,
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

        openDrawer(action: StayAction, stay: Stay): void {
            this.selectedStay = stay
            this.currentAction = action
            this.isShowingDrawer = true
        },

        closeDrawer(): void {
            this.isShowingDrawer = false
        },

        async addProcedure(procedure: ProcedureDetailsFormData): Promise<void> {
            if (!this.selectedStay) {
                return
            }

            this.$wait.start('recording-stay-procedure')

            await this.$http
                .post(`stays/${this.selectedStay.id}/procedures`, procedure)
                .finally(() => this.$wait.end('recording-stay-procedure'))

            this.notifySuccess('Procedure added')
            this.closeDrawer()
        },

        async extendStay(endDate: string): Promise<void> {
            if (!this.selectedStay) {
                return
            }

            this.$wait.start('extending-stay')

            await this.$http
                .patch(`stays/${this.selectedStay.id}/extend`, { endDate })
                .finally(() => this.$wait.end('extending-stay'))

            this.notifySuccess('Stay extended')
            this.closeDrawer()

            await this.fetchStays()
        },

        async dischargePatient(endDate: string): Promise<void> {
            if (!this.selectedStay) {
                return
            }

            this.$wait.start('discharging-patient')

            await this.$http
                .patch(`stays/${this.selectedStay.id}/discharge`, { endDate })
                .finally(() => this.$wait.end('discharging-patient'))

            this.notifySuccess('Discharge saved')
            this.closeDrawer()

            await this.fetchStays()
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-1 items-center gap-2">
            <BaseSearchInput
                v-model="searchTerm"
                placeholder="Search by stay ID, patient or room"
            />

            <BaseMultiSelect
                v-model="selectedStatuses"
                :options="stayStatuses"
                placeholder="No statuses selected"
                all-selected-label="All stays"
                trigger-class="w-48"
            />
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchStays"
            @change-per-page="fetchStays(1, $event)"
        />

        <StaysTable
            :stays="stays"
            :today="today"
            :empty-message="staysEmptyMessage"
            :is-loading="$wait.is('fetching-stays')"
            @add-procedure="openDrawer('add-procedure', $event)"
            @extend="openDrawer('extend', $event)"
            @discharge="openDrawer('discharge', $event)"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchStays"
            @change-per-page="fetchStays(1, $event)"
        />

        <BaseSideDrawer
            v-if="selectedStay && currentAction"
            :is-open="isShowingDrawer"
            :title="drawerTitle"
            @close="closeDrawer"
        >
            <StayProcedureForm
                v-if="currentAction === 'add-procedure'"
                :key="`procedure-${selectedStay.id}`"
                :stay="selectedStay"
                :today="today"
                @save="addProcedure"
            />
            <ExtendStayForm
                v-else-if="currentAction === 'extend'"
                :key="`extend-${selectedStay.id}`"
                :stay="selectedStay"
                @save="extendStay"
            />
            <DischargeForm
                v-else
                :key="`discharge-${selectedStay.id}`"
                :stay="selectedStay"
                :today="today"
                @save="dischargePatient"
            />
        </BaseSideDrawer>
    </div>
</template>
