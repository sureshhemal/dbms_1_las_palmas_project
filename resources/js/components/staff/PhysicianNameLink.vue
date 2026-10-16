<script lang="ts">
import { Link } from '@inertiajs/vue3'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { show } from '@/routes/staff/physicians'

export default defineComponent({
    name: 'PhysicianNameLink',

    components: {
        InertiaLink: Link,
    },

    props: {
        physician: {
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
    <span @click.stop>
        <span
            v-if="physician.deleted_at"
            class="text-neutral-500 line-through dark:text-neutral-400"
            title="Offboarded"
        >
            {{ physician.name }}
        </span>
        <InertiaLink
            v-else
            :href="show(physician.id)"
            class="font-medium text-brand-700 hover:underline dark:text-brand-300"
        >
            {{ physician.name }}
        </InertiaLink>
    </span>
</template>
