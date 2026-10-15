<script lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import DepartmentHeadCard from '@/components/hospital/DepartmentHeadCard.vue'
import DepartmentPhysiciansTable from '@/components/hospital/DepartmentPhysiciansTable.vue'
import Heading from '@/components/Heading.vue'
import { hospital } from '@/routes'
import { show } from '@/routes/hospital/departments'
import type { Department, DepartmentPhysician } from '@/types'

export default defineComponent({
    name: 'DepartmentPage',

    components: {
        DepartmentHeadCard,
        DepartmentPhysiciansTable,
        Heading,
        InertiaHead: Head,
    },

    layout: {
        breadcrumbs: [
            {
                title: 'Hospital',
                href: hospital(),
            },
        ],
    },

    props: {
        departmentId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            department: null as Department | null,
            physicians: [] as DepartmentPhysician[],
        }
    },

    computed: {
        title(): string {
            return this.department?.name ?? 'Department'
        },

        description(): string {
            return this.department
                ? `Department ${this.department.deptID} of Las Palmas Medical Center.`
                : 'Loading department…'
        },
    },

    created() {
        Promise.all([this.fetchDepartment(), this.fetchPhysicians()])
    },

    methods: {
        async fetchDepartment(): Promise<void> {
            this.$wait.start('fetching-department')

            const { data } = await this.$http
                .get<Department>(`departments/${this.departmentId}`)
                .finally(() => this.$wait.end('fetching-department'))

            this.department = data

            setLayoutProps({
                breadcrumbs: [
                    {
                        title: 'Hospital',
                        href: hospital(),
                    },
                    {
                        title: data.name,
                        href: show(this.departmentId),
                    },
                ],
            })
        },

        async fetchPhysicians(): Promise<void> {
            this.$wait.start('fetching-department-physicians')

            const { data } = await this.$http
                .get<DepartmentPhysician[]>(
                    `departments/${this.departmentId}/physicians`,
                )
                .finally(() => this.$wait.end('fetching-department-physicians'))

            this.physicians = data
        },
    },
})
</script>

<template>
    <div class="flex h-full flex-1 flex-col p-4">
        <InertiaHead :title="title" />

        <Heading :title="title" :description="description" />

        <div class="space-y-6">
            <DepartmentHeadCard
                :head="department?.head ?? null"
                :is-loading="$wait.is('fetching-department')"
            />

            <section class="space-y-3">
                <h3
                    class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                >
                    Physicians
                </h3>

                <DepartmentPhysiciansTable
                    :physicians="physicians"
                    :is-loading="$wait.is('fetching-department-physicians')"
                />
            </section>
        </div>
    </div>
</template>
