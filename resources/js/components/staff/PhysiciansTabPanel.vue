<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseConfirmDialog from '@/components/base/BaseConfirmDialog.vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BaseSearchInput from '@/components/base/BaseSearchInput.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import PhysicianForm from '@/components/staff/PhysicianForm.vue'
import PhysiciansTable from '@/components/staff/PhysiciansTable.vue'
import { Button } from '@/components/ui/button'
import type { Physician, PhysicianFormData, PhysicianPosition } from '@/types'
import { physicianPositions } from '@/types'

export default defineComponent({
    name: 'PhysiciansTabPanel',

    components: {
        BaseConfirmDialog,
        BaseMultiSelect,
        BaseSearchInput,
        BaseSideDrawer,
        PhysicianForm,
        PhysiciansTable,
        Plus,
        UiButton: Button,
    },

    emits: ['staff-changed'],

    data() {
        return {
            physicians: [] as Physician[],
            searchTerm: '',
            selectedPositions: [...physicianPositions] as PhysicianPosition[],
            physicianPositions,
            selectedPhysician: null as PhysicianFormData | null,
            isShowingPhysicianForm: false,
            isConfirmingOffboard: false,
        }
    },

    computed: {
        offboardConfirmationDescription(): string {
            const physicianName =
                this.selectedPhysician?.name || 'This physician'

            return `${physicianName} will be removed from the staff list. Their record is kept.`
        },

        physiciansEmptyMessage(): string {
            return this.searchTerm ||
                this.selectedPositions.length < physicianPositions.length
                ? 'No physicians match your search or filter.'
                : 'No physicians yet.'
        },

        physicianFormTitle(): string {
            return this.selectedPhysician?.id
                ? 'Edit Physician'
                : 'Add Physician'
        },
    },

    watch: {
        searchTerm(): void {
            this.fetchPhysicians()
        },

        selectedPositions(): void {
            this.fetchPhysicians()
        },
    },

    created() {
        this.fetchPhysicians()
    },

    methods: {
        async fetchPhysicians(): Promise<void> {
            if (this.selectedPositions.length === 0) {
                this.physicians = []

                return
            }

            this.$wait.start('fetching-physicians')

            const { data } = await this.$http
                .get<Physician[]>('physicians', {
                    params: {
                        search: this.searchTerm || undefined,
                        positions: this.selectedPositions,
                    },
                })
                .finally(() => this.$wait.end('fetching-physicians'))

            this.physicians = data
        },

        addPhysician(): void {
            this.selectedPhysician = {
                id: null,
                physicianID: '',
                name: '',
                position: '',
                ssn: '',
            }

            this.openPhysicianForm()
        },

        toPhysicianFormData(physician: Physician): PhysicianFormData {
            return {
                id: physician.id,
                physicianID: String(physician.physicianID),
                name: physician.name,
                position: physician.position,
                ssn: physician.ssn ?? '',
            }
        },

        editPhysician(physician: Physician): void {
            this.selectedPhysician = this.toPhysicianFormData(physician)

            this.openPhysicianForm()
        },

        offboardPhysician(physician: Physician): void {
            this.selectedPhysician = this.toPhysicianFormData(physician)

            this.askToConfirmOffboard()
        },

        openPhysicianForm(): void {
            this.isShowingPhysicianForm = true
        },

        closePhysicianForm(): void {
            this.isShowingPhysicianForm = false
        },

        askToConfirmOffboard(): void {
            this.isConfirmingOffboard = true
        },

        cancelOffboard(): void {
            this.isConfirmingOffboard = false
        },

        async offboardSelectedPhysician(): Promise<void> {
            if (!this.selectedPhysician?.id) {
                return
            }

            this.$wait.start('offboarding-physician')

            await this.$http
                .delete(`physicians/${this.selectedPhysician.id}`)
                .finally(() => this.$wait.end('offboarding-physician'))

            this.notifySuccess('Physician offboarded')
            this.isConfirmingOffboard = false
            this.closePhysicianForm()
            this.$emit('staff-changed')

            await this.fetchPhysicians()
        },

        async savePhysician(physician: PhysicianFormData): Promise<void> {
            const url = physician.id
                ? `physicians/${physician.id}`
                : 'physicians'
            const method = physician.id ? 'put' : 'post'

            this.$wait.start('saving-physician')

            await this.$http[method](url, physician).finally(() =>
                this.$wait.end('saving-physician'),
            )

            this.notifySuccess('Physician saved')
            this.closePhysicianForm()
            this.$emit('staff-changed')

            await this.fetchPhysicians()
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
                    placeholder="Search physicians by name or ID"
                />

                <BaseMultiSelect
                    v-model="selectedPositions"
                    :options="physicianPositions"
                    placeholder="No positions selected"
                    all-selected-label="All positions"
                />
            </div>

            <UiButton :disabled="$wait.any() > 0" @click="addPhysician">
                <Plus />
                Add Physician
            </UiButton>
        </div>

        <PhysiciansTable
            :physicians="physicians"
            :empty-message="physiciansEmptyMessage"
            :is-loading="$wait.is('fetching-physicians')"
            @edit="editPhysician"
            @offboard="offboardPhysician"
        />

        <BaseSideDrawer
            v-if="selectedPhysician"
            :is-open="isShowingPhysicianForm"
            :title="physicianFormTitle"
            @close="closePhysicianForm"
        >
            <PhysicianForm
                :physician="selectedPhysician"
                @save="savePhysician"
            />
        </BaseSideDrawer>

        <BaseConfirmDialog
            :is-open="isConfirmingOffboard"
            title="Offboard physician?"
            :description="offboardConfirmationDescription"
            confirm-label="Offboard"
            @confirm="offboardSelectedPhysician"
            @cancel="cancelOffboard"
        />
    </div>
</template>
