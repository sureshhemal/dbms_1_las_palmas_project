<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSearchableSelect from '@/components/base/BaseSearchableSelect.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import type { DepartmentFormData, SelectOption } from '@/types'

export default defineComponent({
    name: 'DepartmentForm',

    components: {
        BaseInput,
        BaseSearchableSelect,
        UiButton: Button,
        UiInput: Input,
        UiLabel: Label,
        VeeForm: Form,
    },

    props: {
        department: {
            type: Object as PropType<DepartmentFormData>,
            required: true,
        },
        headOptions: {
            type: Array as PropType<SelectOption[]>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.department },
        }
    },

    computed: {
        selectedHead: {
            get(): string {
                return this.form.head_id ?? ''
            },

            set(selectedHead: string): void {
                this.form.head_id = selectedHead
            },
        },
    },

    watch: {
        department(): void {
            this.form = { ...this.department }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <div class="space-y-1.5">
            <UiLabel for="departmentName">Name</UiLabel>
            <UiInput id="departmentName" :model-value="form.name" disabled />
        </div>

        <BaseInput
            v-model="form.deptID"
            label="Department ID"
            name="deptID"
            rules="required|integer"
        />

        <BaseSearchableSelect
            v-model="selectedHead"
            label="Head"
            name="head"
            rules="required"
            :options="headOptions"
            placeholder="Choose a head"
        />

        <UiButton
            class="w-full"
            :disabled="$wait.any() > 0 || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            Save changes
        </UiButton>
    </VeeForm>
</template>
