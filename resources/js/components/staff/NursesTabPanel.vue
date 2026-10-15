<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import NurseForm from '@/components/staff/NurseForm.vue'
import NursesTable from '@/components/staff/NursesTable.vue'
import { Button } from '@/components/ui/button'
import type {
    Nurse,
    NurseFormData,
    NursePosition,
    PaginatedResponse,
    PaginationDetails,
} from '@/types'
import { nursePositions } from '@/types'

export default defineComponent({
    name: 'NursesTabPanel',

    components: {
        BaseMultiSelect,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        NurseForm,
        NursesTable,
        Plus,
        UiButton: Button,
    },

    emits: ['staff-saved'],

    data() {
        return {
            nurses: [] as Nurse[],
            searchTerm: '',
            selectedPositions: [...nursePositions] as NursePosition[],
            nursePositions,
            paginationDetails: {
                current_page: 1,
                per_page: 20,
                from: null,
                to: null,
                total: 0,
                last_page: 1,
                links: [],
            } as PaginationDetails,
            selectedNurse: null as NurseFormData | null,
            isShowingNurseForm: false,
        }
    },

    computed: {
        nursesEmptyMessage(): string {
            return this.searchTerm ||
                this.selectedPositions.length < nursePositions.length
                ? 'No nurses match your search or filter.'
                : 'No nurses yet.'
        },

        nurseFormTitle(): string {
            return this.selectedNurse?.id ? 'Edit Nurse' : 'Add Nurse'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchNurses(1)
        },

        selectedPositions(): void {
            this.fetchNurses(1)
        },
    },

    created() {
        this.fetchNurses()
    },

    methods: {
        async fetchNurses(page?: number, perPage?: number): Promise<void> {
            if (this.selectedPositions.length === 0) {
                this.showNoNurses()

                return
            }

            this.$wait.start('fetching-nurses')

            const { data } = await this.$http
                .get<PaginatedResponse<Nurse>>('nurses', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                        positions: this.selectedPositions,
                    },
                })
                .finally(() => this.$wait.end('fetching-nurses'))

            const { data: nurses, ...paginationDetails } = data

            this.nurses = nurses
            this.paginationDetails = paginationDetails
        },

        showNoNurses(): void {
            this.nurses = []
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

        addNurse(): void {
            this.selectedNurse = {
                id: null,
                nurseID: '',
                name: '',
                position: '',
                ssn: '',
            }

            this.openNurseForm()
        },

        editNurse(nurse: Nurse): void {
            this.selectedNurse = {
                id: nurse.id,
                nurseID: String(nurse.nurseID),
                name: nurse.name,
                position: nurse.position,
                ssn: nurse.ssn ?? '',
            }

            this.openNurseForm()
        },

        openNurseForm(): void {
            this.isShowingNurseForm = true
        },

        closeNurseForm(): void {
            this.isShowingNurseForm = false
        },

        async saveNurse(nurse: NurseFormData): Promise<void> {
            const url = nurse.id ? `nurses/${nurse.id}` : 'nurses'
            const method = nurse.id ? 'put' : 'post'

            this.$wait.start('saving-nurse')

            await this.$http[method](url, nurse).finally(() =>
                this.$wait.end('saving-nurse'),
            )

            this.notifySuccess('Nurse saved')
            this.closeNurseForm()
            this.$emit('staff-saved')

            await this.fetchNurses()
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
                    placeholder="Search nurses by name or ID"
                />

                <BaseMultiSelect
                    v-model="selectedPositions"
                    :options="nursePositions"
                    placeholder="No positions selected"
                    all-selected-label="All positions"
                />
            </div>

            <UiButton :disabled="$wait.any() > 0" @click="addNurse">
                <Plus />
                Add Nurse
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchNurses"
            @change-per-page="fetchNurses(1, $event)"
        />

        <NursesTable
            :nurses="nurses"
            :empty-message="nursesEmptyMessage"
            :is-loading="$wait.is('fetching-nurses')"
            @select="editNurse"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchNurses"
            @change-per-page="fetchNurses(1, $event)"
        />

        <BaseSideDrawer
            v-if="selectedNurse"
            :is-open="isShowingNurseForm"
            :title="nurseFormTitle"
            @close="closeNurseForm"
        >
            <NurseForm :nurse="selectedNurse" @save="saveNurse" />
        </BaseSideDrawer>
    </div>
</template>
