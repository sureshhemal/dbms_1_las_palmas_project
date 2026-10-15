<script lang="ts">
import { Field } from 'vee-validate'
import { defineComponent } from 'vue'
import InputError from '@/components/InputError.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

export default defineComponent({
    name: 'BaseInput',

    components: {
        Field,
        UiInput: Input,
        InputError,
        UiLabel: Label,
    },

    props: {
        modelValue: {
            type: [String, Number],
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
        rules: {
            type: String,
            default: '',
        },
        type: {
            type: String,
            default: 'text',
        },
        placeholder: {
            type: String,
            default: '',
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
        <UiInput
            :id="name"
            :type="type"
            :model-value="modelValue"
            :placeholder="placeholder"
            @update:model-value="$emit('update:modelValue', $event)"
            @blur="handleBlur"
        />
        <InputError
            :message="meta.touched || hasValue ? errorMessage : undefined"
        />
    </Field>
</template>
