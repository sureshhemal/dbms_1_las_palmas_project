<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseDatePicker from '@/components/base/BaseDatePicker.vue'
import BaseSearchableSelect from '@/components/base/BaseSearchableSelect.vue'
import type {
    NurseSummary,
    PaginatedResponse,
    Prescriber,
    Procedure,
    ProcedureDetailsFormData,
    SelectOption,
} from '@/types'

const optionsPerSearch = 20

export default defineComponent({
    name: 'ProcedureDetailsFields',

    components: {
        BaseDatePicker,
        BaseSearchableSelect,
    },

    props: {
        modelValue: {
            type: Object as PropType<ProcedureDetailsFormData>,
            required: true,
        },
        earliestDate: {
            type: String,
            default: '',
        },
        latestDate: {
            type: String,
            default: '',
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            procedureOptions: [] as SelectOption[],
            physicians: [] as Prescriber[],
            nurses: [] as NurseSummary[],
        }
    },

    computed: {
        physicianOptions(): SelectOption[] {
            return this.physicians.map((physician) => ({
                value: physician.id,
                label: `${physician.name} (${physician.physicianID})`,
            }))
        },

        nurseOptions(): SelectOption[] {
            return this.nurses.map((nurse) => ({
                value: nurse.id,
                label: `${nurse.name} (${nurse.nurseID})`,
            }))
        },
    },

    created() {
        Promise.all([
            this.searchProcedures(''),
            this.fetchPhysicians(),
            this.fetchNurses(),
        ])
    },

    methods: {
        update(field: keyof ProcedureDetailsFormData, value: string): void {
            this.$emit('update:modelValue', {
                ...this.modelValue,
                [field]: value,
            })
        },

        async searchProcedures(searchTerm: string): Promise<void> {
            this.$wait.start('searching-procedures')

            const { data } = await this.$http
                .get<PaginatedResponse<Procedure>>('procedures', {
                    params: {
                        search: searchTerm || undefined,
                        per_page: optionsPerSearch,
                    },
                })
                .finally(() => this.$wait.end('searching-procedures'))

            this.procedureOptions = data.data.map((procedure) => ({
                value: procedure.id,
                label: procedure.name,
            }))
        },

        async fetchPhysicians(): Promise<void> {
            this.$wait.start('fetching-physicians')

            const { data } = await this.$http
                .get<Prescriber[]>('prescriber-candidates')
                .finally(() => this.$wait.end('fetching-physicians'))

            this.physicians = data
        },

        async fetchNurses(): Promise<void> {
            this.$wait.start('fetching-nurses')

            const { data } = await this.$http
                .get<NurseSummary[]>('on-call-nurse-candidates')
                .finally(() => this.$wait.end('fetching-nurses'))

            this.nurses = data
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <BaseSearchableSelect
            :model-value="modelValue.procedure_id"
            label="Procedure"
            name="procedure_id"
            rules="required"
            placeholder="Search procedures"
            :options="procedureOptions"
            searches-remotely
            :is-searching="$wait.is('searching-procedures')"
            @search="searchProcedures"
            @update:model-value="update('procedure_id', $event)"
        />

        <BaseSearchableSelect
            :model-value="modelValue.physician_id"
            label="Physician"
            name="physician_id"
            rules="required"
            placeholder="Choose a physician"
            :options="physicianOptions"
            @update:model-value="update('physician_id', $event)"
        />

        <BaseSearchableSelect
            :model-value="modelValue.nurse_id"
            label="Nurse"
            name="nurse_id"
            rules="required"
            placeholder="Choose a nurse"
            :options="nurseOptions"
            @update:model-value="update('nurse_id', $event)"
        />

        <BaseDatePicker
            :model-value="modelValue.procDate"
            label="Procedure Date"
            name="procDate"
            rules="required"
            :min="earliestDate"
            :max="latestDate"
            @update:model-value="update('procDate', $event)"
        />
    </div>
</template>
