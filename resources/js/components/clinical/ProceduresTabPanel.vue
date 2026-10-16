<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseConfirmDialog from '@/components/base/BaseConfirmDialog.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import ProcedureForm from '@/components/clinical/ProcedureForm.vue'
import ProceduresTable from '@/components/clinical/ProceduresTable.vue'
import { Button } from '@/components/ui/button'
import type {
    PaginatedResponse,
    PaginationDetails,
    Procedure,
    ProcedureFormData,
    TableSorting,
} from '@/types'

export default defineComponent({
    name: 'ProceduresTabPanel',

    components: {
        BaseConfirmDialog,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        Plus,
        ProcedureForm,
        ProceduresTable,
        UiButton: Button,
    },

    data() {
        return {
            procedures: [] as Procedure[],
            searchTerm: '',
            sorting: {
                column: 'performedShare',
                direction: 'desc',
            } as TableSorting,
            paginationDetails: {
                current_page: 1,
                per_page: 20,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            selectedProcedure: null as ProcedureFormData | null,
            isShowingProcedureForm: false,
            isConfirmingRemoval: false,
        }
    },

    computed: {
        removalConfirmationDescription(): string {
            const procedureName =
                this.selectedProcedure?.name || 'This procedure'

            return `${procedureName} will be removed from the procedure list. Its record is kept.`
        },

        proceduresEmptyMessage(): string {
            return this.searchTerm
                ? 'No procedures match your search.'
                : 'No procedures yet.'
        },

        procedureFormTitle(): string {
            return this.selectedProcedure?.id
                ? 'Edit Procedure'
                : 'Add Procedure'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchProcedures(1)
        },
    },

    created() {
        this.fetchProcedures()
    },

    methods: {
        async fetchProcedures(page?: number, perPage?: number): Promise<void> {
            this.$wait.start('fetching-procedures')

            const { data } = await this.$http
                .get<PaginatedResponse<Procedure>>('procedures', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                        sort: this.sorting.column,
                        direction: this.sorting.direction,
                    },
                })
                .finally(() => this.$wait.end('fetching-procedures'))

            const { data: procedures, ...paginationDetails } = data

            this.procedures = procedures
            this.paginationDetails = paginationDetails
        },

        sortProcedures(sorting: TableSorting): void {
            this.sorting = sorting
            this.fetchProcedures(1)
        },

        toProcedureFormData(procedure: Procedure): ProcedureFormData {
            return {
                id: procedure.id,
                procID: String(procedure.procID),
                name: procedure.name,
                cost: procedure.cost === null ? '' : String(procedure.cost),
            }
        },

        addProcedure(): void {
            this.selectedProcedure = {
                id: null,
                procID: '',
                name: '',
                cost: '',
            }

            this.openProcedureForm()
        },

        editProcedure(procedure: Procedure): void {
            this.selectedProcedure = this.toProcedureFormData(procedure)

            this.openProcedureForm()
        },

        removeProcedure(procedure: Procedure): void {
            this.selectedProcedure = this.toProcedureFormData(procedure)

            this.isConfirmingRemoval = true
        },

        openProcedureForm(): void {
            this.isShowingProcedureForm = true
        },

        closeProcedureForm(): void {
            this.isShowingProcedureForm = false
        },

        cancelRemoval(): void {
            this.isConfirmingRemoval = false
        },

        async saveProcedure(procedure: ProcedureFormData): Promise<void> {
            const url = procedure.id
                ? `procedures/${procedure.id}`
                : 'procedures'
            const method = procedure.id ? 'put' : 'post'

            this.$wait.start('saving-procedure')

            await this.$http[method](url, procedure).finally(() =>
                this.$wait.end('saving-procedure'),
            )

            this.notifySuccess('Procedure saved')
            this.closeProcedureForm()

            await this.fetchProcedures()
        },

        async removeSelectedProcedure(): Promise<void> {
            if (!this.selectedProcedure?.id) {
                return
            }

            this.$wait.start('removing-procedure')

            await this.$http
                .delete(`procedures/${this.selectedProcedure.id}`)
                .finally(() => this.$wait.end('removing-procedure'))

            this.notifySuccess('Procedure removed')
            this.isConfirmingRemoval = false

            await this.fetchProcedures()
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <BaseSearchInput
                v-model="searchTerm"
                placeholder="Search procedures by name or ID"
            />

            <UiButton :disabled="$wait.any() > 0" @click="addProcedure">
                <Plus />
                Add Procedure
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchProcedures"
            @change-per-page="fetchProcedures(1, $event)"
        />

        <ProceduresTable
            :procedures="procedures"
            :empty-message="proceduresEmptyMessage"
            :is-loading="$wait.is('fetching-procedures')"
            :sorting="sorting"
            @edit="editProcedure"
            @remove="removeProcedure"
            @sort="sortProcedures"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchProcedures"
            @change-per-page="fetchProcedures(1, $event)"
        />

        <BaseSideDrawer
            v-if="selectedProcedure"
            :is-open="isShowingProcedureForm"
            :title="procedureFormTitle"
            @close="closeProcedureForm"
        >
            <ProcedureForm
                :procedure="selectedProcedure"
                @save="saveProcedure"
            />
        </BaseSideDrawer>

        <BaseConfirmDialog
            :is-open="isConfirmingRemoval"
            title="Remove procedure?"
            :description="removalConfirmationDescription"
            confirm-label="Remove"
            @confirm="removeSelectedProcedure"
            @cancel="cancelRemoval"
        />
    </div>
</template>
