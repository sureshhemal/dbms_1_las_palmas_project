<script lang="ts">
import { Plus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseConfirmDialog from '@/components/base/BaseConfirmDialog.vue'
import BaseMultiSelect from '@/components/base/BaseMultiSelect.vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import OnCallCalendar from '@/components/staff/OnCallCalendar.vue'
import OnCallForm from '@/components/staff/OnCallForm.vue'
import { Button } from '@/components/ui/button'
import { addDays, todayIn } from '@/lib/calendarDate'
import type {
    CalendarRange,
    NurseSummary,
    OnCall,
    OnCallDates,
    OnCallDatesChange,
    OnCallFormData,
    SelectOption,
} from '@/types'

export default defineComponent({
    name: 'OnCallTabPanel',

    components: {
        BaseConfirmDialog,
        BaseMultiSelect,
        BaseSideDrawer,
        OnCallCalendar,
        OnCallForm,
        Plus,
        UiButton: Button,
    },

    emits: ['staff-changed'],

    data() {
        return {
            onCalls: [] as OnCall[],
            nurses: [] as NurseSummary[],
            visibleRange: null as CalendarRange | null,
            selectedNurseIds: [] as string[],
            selectedOnCall: null as OnCallFormData | null,
            isShowingOnCallForm: false,
            isConfirmingRemoval: false,
        }
    },

    computed: {
        hospitalTimezone(): string {
            return this.$page.props.hospitalTimezone
        },

        nurseOptions(): SelectOption[] {
            return this.nurses.map((nurse) => ({
                value: nurse.id,
                label: `${nurse.name} (${nurse.nurseID})`,
            }))
        },

        isFilteringByNurses(): boolean {
            return this.selectedNurseIds.length < this.nurses.length
        },

        onCallFormTitle(): string {
            return this.selectedOnCall?.id
                ? 'Edit On-Call Period'
                : 'Add On-Call Period'
        },

        removalConfirmationDescription(): string {
            const nurseName =
                this.nurses.find(
                    (nurse) => nurse.id === this.selectedOnCall?.nurse_id,
                )?.name ?? 'The nurse'

            return `${nurseName} will no longer be on call from ${this.selectedOnCall?.startDate} to ${this.selectedOnCall?.endDate}.`
        },
    },

    created() {
        this.fetchNurses()
    },

    methods: {
        async fetchNurses(): Promise<void> {
            this.$wait.start('fetching-on-call-nurses')

            const { data } = await this.$http
                .get<NurseSummary[]>('on-call-nurse-candidates')
                .finally(() => this.$wait.end('fetching-on-call-nurses'))

            this.nurses = data
            this.selectedNurseIds = data.map((nurse) => nurse.id)
        },

        async fetchOnCalls(): Promise<void> {
            if (!this.visibleRange) {
                return
            }

            if (this.nurses.length > 0 && this.selectedNurseIds.length === 0) {
                this.onCalls = []

                return
            }

            this.$wait.start('fetching-on-calls')

            const { data } = await this.$http
                .get<OnCall[]>('on-calls', {
                    params: {
                        from: this.visibleRange.from,
                        to: this.visibleRange.to,
                        nurse_ids: this.isFilteringByNurses
                            ? this.selectedNurseIds
                            : undefined,
                    },
                })
                .finally(() => this.$wait.end('fetching-on-calls'))

            this.onCalls = data
        },

        showRange(range: CalendarRange): void {
            this.visibleRange = range

            this.fetchOnCalls()
        },

        filterByNurses(nurseIds: string[]): void {
            this.selectedNurseIds = nurseIds

            this.fetchOnCalls()
        },

        addOnCall(): void {
            const today = todayIn(this.hospitalTimezone)

            this.addOnCallBetween({
                startDate: today,
                endDate: addDays(today, 1),
            })
        },

        addOnCallBetween(dates: OnCallDates): void {
            this.selectedOnCall = {
                id: null,
                nurse_id: '',
                startDate: dates.startDate,
                endDate: dates.endDate,
            }

            this.openOnCallForm()
        },

        editOnCall(onCall: OnCall): void {
            this.selectedOnCall = {
                id: onCall.id,
                nurse_id: onCall.nurse_id,
                startDate: onCall.startDate,
                endDate: onCall.endDate,
            }

            this.openOnCallForm()
        },

        openOnCallForm(): void {
            this.isShowingOnCallForm = true
        },

        closeOnCallForm(): void {
            this.isShowingOnCallForm = false
        },

        async saveOnCall(onCall: OnCallFormData): Promise<void> {
            const url = onCall.id ? `on-calls/${onCall.id}` : 'on-calls'
            const method = onCall.id ? 'put' : 'post'

            this.$wait.start('saving-on-call')

            await this.$http[method](url, onCall).finally(() =>
                this.$wait.end('saving-on-call'),
            )

            this.notifySuccess('On-call period saved')
            this.closeOnCallForm()
            this.$emit('staff-changed')

            await this.fetchOnCalls()
        },

        async changeOnCallDates(change: OnCallDatesChange): Promise<void> {
            this.$wait.start('saving-on-call')

            try {
                await this.$http.put(`on-calls/${change.onCall.id}`, {
                    nurse_id: change.onCall.nurse_id,
                    startDate: change.startDate,
                    endDate: change.endDate,
                })
            } catch {
                change.revert()

                return
            } finally {
                this.$wait.end('saving-on-call')
            }

            this.notifySuccess('On-call period moved')
            this.$emit('staff-changed')

            await this.fetchOnCalls()
        },

        askToConfirmRemoval(onCall: OnCallFormData): void {
            this.selectedOnCall = onCall
            this.closeOnCallForm()
            this.isConfirmingRemoval = true
        },

        cancelRemoval(): void {
            this.isConfirmingRemoval = false
            this.openOnCallForm()
        },

        async removeSelectedOnCall(): Promise<void> {
            if (!this.selectedOnCall?.id) {
                return
            }

            this.$wait.start('removing-on-call')

            await this.$http
                .delete(`on-calls/${this.selectedOnCall.id}`)
                .finally(() => this.$wait.end('removing-on-call'))

            this.notifySuccess('On-call period removed')
            this.isConfirmingRemoval = false
            this.$emit('staff-changed')

            await this.fetchOnCalls()
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <BaseMultiSelect
                :model-value="selectedNurseIds"
                :options="nurseOptions"
                placeholder="No nurses selected"
                all-selected-label="All nurses"
                trigger-class="w-64"
                @update:model-value="filterByNurses"
            />

            <UiButton :disabled="$wait.any() > 0" @click="addOnCall">
                <Plus />
                Add On-Call Period
            </UiButton>
        </div>

        <p class="text-sm text-neutral-500 dark:text-neutral-400">
            Drag across days to schedule a nurse. Drag a period to move it, or
            drag its edges to change the dates. Days follow hospital time ({{
                hospitalTimezone
            }}).
        </p>

        <OnCallCalendar
            :on-calls="onCalls"
            :is-loading="$wait.is('fetching-on-calls')"
            :time-zone="hospitalTimezone"
            @range-change="showRange"
            @select-dates="addOnCallBetween"
            @open="editOnCall"
            @change-dates="changeOnCallDates"
        />

        <BaseSideDrawer
            v-if="selectedOnCall"
            :is-open="isShowingOnCallForm"
            :title="onCallFormTitle"
            @close="closeOnCallForm"
        >
            <OnCallForm
                :on-call="selectedOnCall"
                :nurse-options="nurseOptions"
                @save="saveOnCall"
                @remove="askToConfirmRemoval"
            />
        </BaseSideDrawer>

        <BaseConfirmDialog
            :is-open="isConfirmingRemoval"
            title="Remove on-call period?"
            :description="removalConfirmationDescription"
            confirm-label="Remove"
            @confirm="removeSelectedOnCall"
            @cancel="cancelRemoval"
        />
    </div>
</template>
