<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import type { TodayOnCall } from '@/types'

export default defineComponent({
    name: 'OnCallNursesTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
    },

    props: {
        onCall: {
            type: Object as PropType<TodayOnCall>,
            required: true,
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-medium">
            {{ onCall.nurse.name }}
        </BaseTableCell>
        <BaseTableCell>{{ onCall.nurse.position }}</BaseTableCell>
        <BaseTableCell align="center" class="tabular-nums">
            {{ onCall.startDate }} – {{ onCall.endDate }}
        </BaseTableCell>
    </BaseTableRow>
</template>
