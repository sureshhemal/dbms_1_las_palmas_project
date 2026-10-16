<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import PerformedProcedureForm from '@/components/clinical/PerformedProcedureForm.vue'
import PerformedProceduresTable from '@/components/clinical/PerformedProceduresTable.vue'
import { Button } from '@/components/ui/button'
import { todayIn } from '@/lib/calendarDate'
import type {
    NurseSummary,
    PaginatedResponse,
    PaginationDetails,
    PerformedProcedure,
    Prescriber,
    Procedure,
    ProcedureEntryFormData,
    SelectOption,
} from '@/types'

const allProceduresAtOnce = 1000

const procedureStatuses: SelectOption[] = [
    { value: 'performed', label: 'Performed' },
    { value: 'scheduled', label: 'Scheduled' },
]

export default defineComponent({
    name: 'PerformedProceduresTabPanel',

    components: {
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        PerformedProcedureForm,
        PerformedProceduresTable,
        Plus,
        UiButton: Button,
    },

    data() {
        return {
            performedProcedures: [] as PerformedProcedure[],
            years: [] as number[],
            selectedYears: [] as string[],
            procedureStatuses,
            selectedStatuses: procedureStatuses.map((status) => status.value),
            physicians: [] as Prescriber[],
            selectedPhysicianIds: [] as string[],
            nurses: [] as NurseSummary[],
            selectedNurseIds: [] as string[],
            procedures: [] as Procedure[],
            selectedProcedureIds: [] as string[],
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
            newEntry: null as ProcedureEntryFormData | null,
            isShowingForm: false,
        }
    },

    computed: {
        today(): string {
            return todayIn(this.$page.props.hospitalTimezone)
        },

        yearOptions(): SelectOption[] {
            return this.years.map((year) => ({
                value: String(year),
                label: String(year),
            }))
        },

        isFilteringByYears(): boolean {
            return this.selectedYears.length < this.years.length
        },

        isFilteringByStatus(): boolean {
            return this.selectedStatuses.length < procedureStatuses.length
        },

        physicianOptions(): SelectOption[] {
            return this.physicians.map((physician) => ({
                value: physician.id,
                label: physician.name,
            }))
        },

        nurseOptions(): SelectOption[] {
            return this.nurses.map((nurse) => ({
                value: nurse.id,
                label: nurse.name,
            }))
        },

        procedureOptions(): SelectOption[] {
            return this.procedures.map((procedure) => ({
                value: procedure.id,
                label: procedure.name,
            }))
        },

        isFilteringByPhysicians(): boolean {
            return this.selectedPhysicianIds.length < this.physicians.length
        },

        isFilteringByNurses(): boolean {
            return this.selectedNurseIds.length < this.nurses.length
        },

        isFilteringByProcedures(): boolean {
            return this.selectedProcedureIds.length < this.procedures.length
        },

        hasEmptyFilter(): boolean {
            return (
                (this.years.length > 0 && this.selectedYears.length === 0) ||
                this.selectedStatuses.length === 0 ||
                (this.physicians.length > 0 &&
                    this.selectedPhysicianIds.length === 0) ||
                (this.nurses.length > 0 &&
                    this.selectedNurseIds.length === 0) ||
                (this.procedures.length > 0 &&
                    this.selectedProcedureIds.length === 0)
            )
        },

        emptyMessage(): string {
            return this.searchTerm ||
                this.isFilteringByYears ||
                this.isFilteringByStatus ||
                this.isFilteringByPhysicians ||
                this.isFilteringByNurses ||
                this.isFilteringByProcedures
                ? 'No performed procedures match your search or filter.'
                : 'No procedures performed yet.'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchPerformedProcedures(1)
        },

        selectedStatuses(): void {
            this.fetchPerformedProcedures(1)
        },
    },

    created() {
        Promise.all([
            this.fetchPerformedProcedures(),
            this.fetchYears(),
            this.fetchPhysicians(),
            this.fetchNurses(),
            this.fetchProcedures(),
        ])
    },

    methods: {
        async fetchPerformedProcedures(
            page?: number,
            perPage?: number,
        ): Promise<void> {
            if (this.hasEmptyFilter) {
                this.showNothing()

                return
            }

            this.$wait.start('fetching-performed-procedures')

            const { data } = await this.$http
                .get<PaginatedResponse<PerformedProcedure>>(
                    'performed-procedures',
                    {
                        params: {
                            page: page ?? this.paginationDetails.current_page,
                            per_page:
                                perPage ?? this.paginationDetails.per_page,
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
                            nurse_ids: this.isFilteringByNurses
                                ? this.selectedNurseIds
                                : undefined,
                            procedure_ids: this.isFilteringByProcedures
                                ? this.selectedProcedureIds
                                : undefined,
                        },
                    },
                )
                .finally(() => this.$wait.end('fetching-performed-procedures'))

            const { data: performedProcedures, ...paginationDetails } = data

            this.performedProcedures = performedProcedures
            this.paginationDetails = paginationDetails
        },

        async fetchYears(): Promise<void> {
            this.$wait.start('fetching-performed-procedure-years')

            const { data } = await this.$http
                .get<number[]>('performed-procedures/years')
                .finally(() =>
                    this.$wait.end('fetching-performed-procedure-years'),
                )

            this.years = data
            this.selectedYears = data.map(String)
        },

        async fetchPhysicians(): Promise<void> {
            this.$wait.start('fetching-filter-physicians')

            const { data } = await this.$http
                .get<Prescriber[]>('prescriber-candidates')
                .finally(() => this.$wait.end('fetching-filter-physicians'))

            this.physicians = data
            this.selectedPhysicianIds = data.map((physician) => physician.id)
        },

        async fetchNurses(): Promise<void> {
            this.$wait.start('fetching-filter-nurses')

            const { data } = await this.$http
                .get<NurseSummary[]>('on-call-nurse-candidates')
                .finally(() => this.$wait.end('fetching-filter-nurses'))

            this.nurses = data
            this.selectedNurseIds = data.map((nurse) => nurse.id)
        },

        async fetchProcedures(): Promise<void> {
            this.$wait.start('fetching-filter-procedures')

            const { data } = await this.$http
                .get<PaginatedResponse<Procedure>>('procedures', {
                    params: { per_page: allProceduresAtOnce },
                })
                .finally(() => this.$wait.end('fetching-filter-procedures'))

            this.procedures = data.data
            this.selectedProcedureIds = data.data.map(
                (procedure) => procedure.id,
            )
        },

        showNothing(): void {
            this.performedProcedures = []
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

        filterBy(
            filter:
                | 'selectedPhysicianIds'
                | 'selectedNurseIds'
                | 'selectedProcedureIds',
            ids: string[],
        ): void {
            this[filter] = ids

            this.fetchPerformedProcedures(1)
        },

        filterByYears(years: string[]): void {
            this.selectedYears = years

            this.fetchPerformedProcedures(1)
        },

        recordPerformedProcedure(): void {
            this.newEntry = {
                patient_id: '',
                procedure_id: '',
                physician_id: '',
                nurse_id: '',
                procDate: this.today,
                room_id: '',
                startDate: this.today,
                endDate: '',
            }

            this.isShowingForm = true
        },

        closeForm(): void {
            this.isShowingForm = false
        },

        async savePerformedProcedure(
            entry: ProcedureEntryFormData,
        ): Promise<void> {
            this.$wait.start('recording-performed-procedure')

            await this.$http
                .post('performed-procedures', entry)
                .finally(() => this.$wait.end('recording-performed-procedure'))

            this.notifySuccess('Procedure recorded and patient admitted')
            this.closeForm()

            await Promise.all([
                this.fetchPerformedProcedures(1),
                this.fetchYears(),
            ])
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-1 flex-wrap items-center gap-2">
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
                    v-model="selectedStatuses"
                    :options="procedureStatuses"
                    placeholder="No statuses selected"
                    all-selected-label="All statuses"
                    trigger-class="w-40"
                />

                <BaseMultiSelect
                    :model-value="selectedProcedureIds"
                    :options="procedureOptions"
                    placeholder="No procedures selected"
                    all-selected-label="All procedures"
                    trigger-class="w-48"
                    @update:model-value="
                        filterBy('selectedProcedureIds', $event)
                    "
                />

                <BaseMultiSelect
                    :model-value="selectedPhysicianIds"
                    :options="physicianOptions"
                    placeholder="No physicians selected"
                    all-selected-label="All physicians"
                    trigger-class="w-48"
                    @update:model-value="
                        filterBy('selectedPhysicianIds', $event)
                    "
                />

                <BaseMultiSelect
                    :model-value="selectedNurseIds"
                    :options="nurseOptions"
                    placeholder="No nurses selected"
                    all-selected-label="All nurses"
                    trigger-class="w-44"
                    @update:model-value="filterBy('selectedNurseIds', $event)"
                />
            </div>

            <UiButton
                :disabled="$wait.any() > 0"
                @click="recordPerformedProcedure"
            >
                <Plus />
                Record Procedure
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchPerformedProcedures"
            @change-per-page="fetchPerformedProcedures(1, $event)"
        />

        <PerformedProceduresTable
            :performed-procedures="performedProcedures"
            :today="today"
            :empty-message="emptyMessage"
            :is-loading="$wait.is('fetching-performed-procedures')"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchPerformedProcedures"
            @change-per-page="fetchPerformedProcedures(1, $event)"
        />

        <BaseSideDrawer
            v-if="newEntry"
            :is-open="isShowingForm"
            title="Record Procedure"
            @close="closeForm"
        >
            <PerformedProcedureForm
                :entry="newEntry"
                @save="savePerformedProcedure"
            />
        </BaseSideDrawer>
    </div>
</template>
