<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseConfirmDialog from '@/components/base/BaseConfirmDialog.vue'
import BasePaginationBar from '@/components/base/BasePaginationBar.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import MedicationForm from '@/components/clinical/MedicationForm.vue'
import MedicationsTable from '@/components/clinical/MedicationsTable.vue'
import { Button } from '@/components/ui/button'
import type {
    Medication,
    MedicationFormData,
    PaginatedResponse,
    PaginationDetails,
} from '@/types'

export default defineComponent({
    name: 'MedicationsTabPanel',

    components: {
        BaseConfirmDialog,
        BasePaginationBar,
        BaseSearchInput,
        BaseSideDrawer,
        MedicationForm,
        MedicationsTable,
        Plus,
        UiButton: Button,
    },

    data() {
        return {
            medications: [] as Medication[],
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
            selectedMedication: null as MedicationFormData | null,
            isShowingMedicationForm: false,
            isConfirmingRemoval: false,
        }
    },

    computed: {
        removalConfirmationDescription(): string {
            const medicationName =
                this.selectedMedication?.name || 'This medication'

            return `${medicationName} will be removed from the medication list. Its record is kept.`
        },

        medicationsEmptyMessage(): string {
            return this.searchTerm
                ? 'No medications match your search.'
                : 'No medications yet.'
        },

        medicationFormTitle(): string {
            return this.selectedMedication?.id
                ? 'Edit Medication'
                : 'Add Medication'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchMedications(1)
        },
    },

    created() {
        this.fetchMedications()
    },

    methods: {
        async fetchMedications(page?: number, perPage?: number): Promise<void> {
            this.$wait.start('fetching-medications')

            const { data } = await this.$http
                .get<PaginatedResponse<Medication>>('medications', {
                    params: {
                        page: page ?? this.paginationDetails.current_page,
                        per_page: perPage ?? this.paginationDetails.per_page,
                        search: this.searchTerm || undefined,
                    },
                })
                .finally(() => this.$wait.end('fetching-medications'))

            const { data: medications, ...paginationDetails } = data

            this.medications = medications
            this.paginationDetails = paginationDetails
        },

        toMedicationFormData(medication: Medication): MedicationFormData {
            return {
                id: medication.id,
                medID: String(medication.medID),
                name: medication.name,
            }
        },

        addMedication(): void {
            this.selectedMedication = {
                id: null,
                medID: '',
                name: '',
            }

            this.openMedicationForm()
        },

        editMedication(medication: Medication): void {
            this.selectedMedication = this.toMedicationFormData(medication)

            this.openMedicationForm()
        },

        removeMedication(medication: Medication): void {
            this.selectedMedication = this.toMedicationFormData(medication)

            this.isConfirmingRemoval = true
        },

        openMedicationForm(): void {
            this.isShowingMedicationForm = true
        },

        closeMedicationForm(): void {
            this.isShowingMedicationForm = false
        },

        cancelRemoval(): void {
            this.isConfirmingRemoval = false
        },

        async saveMedication(medication: MedicationFormData): Promise<void> {
            const url = medication.id
                ? `medications/${medication.id}`
                : 'medications'
            const method = medication.id ? 'put' : 'post'

            this.$wait.start('saving-medication')

            await this.$http[method](url, medication).finally(() =>
                this.$wait.end('saving-medication'),
            )

            this.notifySuccess('Medication saved')
            this.closeMedicationForm()

            await this.fetchMedications()
        },

        async removeSelectedMedication(): Promise<void> {
            if (!this.selectedMedication?.id) {
                return
            }

            this.$wait.start('removing-medication')

            await this.$http
                .delete(`medications/${this.selectedMedication.id}`)
                .finally(() => this.$wait.end('removing-medication'))

            this.notifySuccess('Medication removed')
            this.isConfirmingRemoval = false

            await this.fetchMedications()
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <BaseSearchInput
                v-model="searchTerm"
                placeholder="Search medications by name or ID"
            />

            <UiButton :disabled="$wait.any() > 0" @click="addMedication">
                <Plus />
                Add Medication
            </UiButton>
        </div>

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchMedications"
            @change-per-page="fetchMedications(1, $event)"
        />

        <MedicationsTable
            :medications="medications"
            :empty-message="medicationsEmptyMessage"
            :is-loading="$wait.is('fetching-medications')"
            @edit="editMedication"
            @remove="removeMedication"
        />

        <BasePaginationBar
            v-if="paginationDetails.total > 0"
            :pagination-details="paginationDetails"
            @change-page="fetchMedications"
            @change-per-page="fetchMedications(1, $event)"
        />

        <BaseSideDrawer
            v-if="selectedMedication"
            :is-open="isShowingMedicationForm"
            :title="medicationFormTitle"
            @close="closeMedicationForm"
        >
            <MedicationForm
                :medication="selectedMedication"
                @save="saveMedication"
            />
        </BaseSideDrawer>

        <BaseConfirmDialog
            :is-open="isConfirmingRemoval"
            title="Remove medication?"
            :description="removalConfirmationDescription"
            confirm-label="Remove"
            @confirm="removeSelectedMedication"
            @cancel="cancelRemoval"
        />
    </div>
</template>
