<script lang="ts">
import { Head } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import ClinicalTabs from '@/components/clinical/ClinicalTabs.vue'
import Heading from '@/components/Heading.vue'
import { clinical } from '@/routes'

export default defineComponent({
    name: 'ClinicalPage',

    components: {
        ClinicalTabs,
        Heading,
        InertiaHead: Head,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Clinical',
                href: clinical(),
            },
        ],
    },
})
</script>

<template>
    <div class="flex h-full flex-1 flex-col p-4">
        <InertiaHead title="Clinical" />

        <Heading
            title="Clinical"
            description="Medications used at Las Palmas Medical Center."
        />

        <ClinicalTabs />
    </div>
</template>
