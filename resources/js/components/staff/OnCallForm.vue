<script lang="ts">
import { Trash2 } from '@lucide/vue'
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import { Button } from '@/components/ui/button'
import type { OnCallFormData, SelectOption } from '@/types'

export default defineComponent({
    name: 'OnCallForm',

    components: {
        BaseInput,
        BaseSelect,
        Trash2,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        onCall: {
            type: Object as PropType<OnCallFormData>,
            required: true,
        },
        nurseOptions: {
            type: Array as PropType<SelectOption[]>,
            required: true,
        },
    },

    emits: ['save', 'remove'],

    data() {
        return {
            form: { ...this.onCall },
        }
    },

    computed: {
        isEditingExistingOnCall(): boolean {
            return this.form.id !== null
        },

        saveButtonLabel(): string {
            return this.isEditingExistingOnCall
                ? 'Save changes'
                : 'Add on-call period'
        },
    },

    watch: {
        onCall(): void {
            this.form = { ...this.onCall }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <BaseSelect
            v-model="form.nurse_id"
            label="Nurse"
            name="nurse_id"
            rules="required"
            placeholder="Choose a nurse"
            :options="nurseOptions"
        />

        <BaseInput
            v-model="form.startDate"
            label="Start date"
            name="startDate"
            type="date"
            rules="required"
        />

        <div class="space-y-1.5">
            <BaseInput
                v-model="form.endDate"
                label="End date"
                name="endDate"
                type="date"
                rules="required"
            />
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                The last day the nurse is on call. A period lasts at least two
                days.
            </p>
        </div>

        <UiButton
            class="w-full"
            :disabled="$wait.any() > 0 || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            {{ saveButtonLabel }}
        </UiButton>

        <UiButton
            v-if="isEditingExistingOnCall"
            variant="outline"
            class="w-full text-danger-700 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300"
            :disabled="$wait.any() > 0"
            @click="$emit('remove', { ...form })"
        >
            <Trash2 />
            Remove on-call period
        </UiButton>
    </VeeForm>
</template>
