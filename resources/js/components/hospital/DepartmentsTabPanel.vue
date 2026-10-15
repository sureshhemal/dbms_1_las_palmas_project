<script lang="ts">
import { defineComponent } from 'vue'
import BaseSideDrawer from '@/components/base/BaseSideDrawer.vue'
import DepartmentForm from '@/components/hospital/DepartmentForm.vue'
import DepartmentsTable from '@/components/hospital/DepartmentsTable.vue'
import type {
    Department,
    DepartmentFormData,
    Physician,
    SelectOption,
} from '@/types'

export default defineComponent({
    name: 'DepartmentsTabPanel',

    components: {
        BaseSideDrawer,
        DepartmentForm,
        DepartmentsTable,
    },

    data() {
        return {
            departments: [] as Department[],
            departmentHeadCandidates: [] as Physician[],
            selectedDepartment: null as DepartmentFormData | null,
            isShowingDepartmentForm: false,
        }
    },

    computed: {
        departmentHeadOptions(): SelectOption[] {
            return this.departmentHeadCandidates.map((physician) => ({
                value: physician.id,
                label: `${physician.name} (${physician.physicianID})`,
            }))
        },
    },

    created() {
        this.fetchDepartments()
        this.fetchDepartmentHeadCandidates()
    },

    methods: {
        async fetchDepartments(): Promise<void> {
            this.$wait.start('fetching-departments')

            const { data } = await this.$http
                .get<Department[]>('departments')
                .finally(() => this.$wait.end('fetching-departments'))

            this.departments = data
        },

        async fetchDepartmentHeadCandidates(): Promise<void> {
            this.$wait.start('fetching-department-head-candidates')

            const { data } = await this.$http
                .get<Physician[]>('department-head-candidates')
                .finally(() =>
                    this.$wait.end('fetching-department-head-candidates'),
                )

            this.departmentHeadCandidates = data
        },

        editDepartment(department: Department): void {
            this.selectedDepartment = {
                id: department.id,
                deptID: String(department.deptID),
                name: department.name,
                head_id: department.head_id,
            }

            this.isShowingDepartmentForm = true
        },

        closeDepartmentForm(): void {
            this.isShowingDepartmentForm = false
        },

        async saveDepartment(department: DepartmentFormData): Promise<void> {
            this.$wait.start('saving-department')

            await this.$http
                .put(`departments/${department.id}`, {
                    deptID: department.deptID,
                    head_id: department.head_id,
                })
                .finally(() => this.$wait.end('saving-department'))

            this.notifySuccess('Department saved')
            this.closeDepartmentForm()

            await this.fetchDepartments()
        },
    },
})
</script>

<template>
    <div class="space-y-4">
        <DepartmentsTable
            :departments="departments"
            :is-loading="$wait.is('fetching-departments')"
            @edit="editDepartment"
        />

        <BaseSideDrawer
            v-if="selectedDepartment"
            :is-open="isShowingDepartmentForm"
            title="Edit Department"
            @close="closeDepartmentForm"
        >
            <DepartmentForm
                :department="selectedDepartment"
                :head-options="departmentHeadOptions"
                @save="saveDepartment"
            />
        </BaseSideDrawer>
    </div>
</template>
