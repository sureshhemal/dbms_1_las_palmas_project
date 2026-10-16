<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PatientsTableRow from '@/components/patient/PatientsTableRow.vue'
import type { Patient } from '@/types'

export default defineComponent({
    name: 'PatientsTable',

    components: {
        BaseTable,
        PatientsTableRow,
    },

    props: {
        patients: {
            type: Array as PropType<Patient[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No patients yet.',
        },
    },

    emits: ['edit', 'deactivate'],

    data() {
        return {
            columnHeadings: [
                'Patient ID',
                'Name',
                'Date of Birth (Age)',
                'Phone',
                'Insurance Number',
                'Primary Physician',
                'SSN',
                '',
            ],
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="patients.length === 0"
        :empty-message="emptyMessage"
    >
        <PatientsTableRow
            v-for="patient in patients"
            :key="patient.id"
            :patient="patient"
            @edit="$emit('edit', $event)"
            @deactivate="$emit('deactivate', $event)"
        />
    </BaseTable>
</template>
