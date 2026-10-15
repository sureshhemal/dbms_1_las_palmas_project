<script lang="ts">
import { Head } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import Heading from '@/components/Heading.vue'
import HospitalTabs from '@/components/hospital/HospitalTabs.vue'
import { hospital } from '@/routes'

export default defineComponent({
    name: 'HospitalPage',

    components: {
        InertiaHead: Head,
        Heading,
        HospitalTabs,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Hospital',
                href: hospital(),
            },
        ],
    },
})
</script>

<template>
    <div class="flex h-full flex-1 flex-col p-4">
        <InertiaHead title="Hospital" />

        <Heading
            title="Hospital"
            description="Departments and rooms of Las Palmas Medical Center."
        />

        <HospitalTabs />
    </div>
</template>
