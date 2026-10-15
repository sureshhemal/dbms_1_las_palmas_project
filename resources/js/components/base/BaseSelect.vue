<script lang="ts">
import type { PropType } from 'vue'
import { Field } from 'vee-validate'
import { defineComponent } from 'vue'
import InputError from '@/components/InputError.vue'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

export default defineComponent({
    name: 'BaseSelect',

    components: {
        Field,
        InputError,
        UiLabel: Label,
        UiSelect: Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    },

    props: {
        modelValue: {
            type: String,
            default: '',
        },
        label: {
            type: String,
            required: true,
        },
        name: {
            type: String,
            required: true,
        },
        options: {
            type: Array as PropType<string[]>,
            required: true,
        },
        rules: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: 'Choose one',
        },
    },

    emits: ['update:modelValue'],

    computed: {
        hasValue(): boolean {
            return this.modelValue !== ''
        },
    },
})
</script>

<template>
    <Field
        v-slot="{ errorMessage, meta, handleBlur }"
        :name="name"
        :label="label"
        :rules="rules"
        :model-value="modelValue"
        validate-on-mount
        as="div"
        class="space-y-1.5"
    >
        <UiLabel :for="name">{{ label }}</UiLabel>
        <UiSelect
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
            @update:open="(isOpen: boolean) => !isOpen && handleBlur()"
        >
            <SelectTrigger :id="name" class="w-full">
                <SelectValue :placeholder="placeholder" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in options"
                    :key="option"
                    :value="option"
                >
                    {{ option }}
                </SelectItem>
            </SelectContent>
        </UiSelect>
        <InputError
            :message="meta.touched || hasValue ? errorMessage : undefined"
        />
    </Field>
</template>
