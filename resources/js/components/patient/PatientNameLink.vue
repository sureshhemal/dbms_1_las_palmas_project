<script lang="ts">
import { Link } from '@inertiajs/vue3'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { show } from '@/routes/patients'

export default defineComponent({
    name: 'PatientNameLink',

    components: {
        InertiaLink: Link,
    },

    props: {
        patient: {
            type: Object as PropType<{
                id: string
                name: string
                deleted_at?: string | null
            }>,
            required: true,
        },
    },

    methods: {
        show,
    },
})
</script>

<template>
    <span
        v-if="patient.deleted_at"
        class="text-neutral-500 line-through dark:text-neutral-400"
        title="Inactive"
    >
        {{ patient.name }}
    </span>
    <InertiaLink
        v-else
        :href="show(patient.id)"
        class="font-medium text-brand-700 hover:underline dark:text-brand-300"
    >
        {{ patient.name }}
    </InertiaLink>
</template>
