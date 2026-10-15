<script lang="ts">
import { Head } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import Heading from '@/components/Heading.vue'
import PatientsPanel from '@/components/patient/PatientsPanel.vue'
import { patients } from '@/routes'

export default defineComponent({
    name: 'PatientsPage',

    components: {
        Heading,
        InertiaHead: Head,
        PatientsPanel,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Patients',
                href: patients(),
            },
        ],
    },
})
</script>

<template>
    <div class="flex h-full flex-1 flex-col p-4">
        <InertiaHead title="Patients" />

        <Heading
            title="Patients"
            description="Patients registered at Las Palmas Medical Center."
        />

        <PatientsPanel />
    </div>
</template>
