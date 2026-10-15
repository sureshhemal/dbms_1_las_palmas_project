<script lang="ts">
import type { PropType } from 'vue'
import { Form } from 'vee-validate'
import { defineComponent } from 'vue'
import BaseInput from '@/components/base/BaseInput.vue'
import BaseSelect from '@/components/base/BaseSelect.vue'
import { Button } from '@/components/ui/button'
import type { RoomFormData } from '@/types'
import { roomTypes } from '@/types'

export default defineComponent({
    name: 'RoomForm',

    components: {
        BaseInput,
        BaseSelect,
        UiButton: Button,
        VeeForm: Form,
    },

    props: {
        room: {
            type: Object as PropType<RoomFormData>,
            required: true,
        },
    },

    emits: ['save'],

    data() {
        return {
            form: { ...this.room },
            roomTypes,
        }
    },

    computed: {
        isEditingExistingRoom(): boolean {
            return this.form.id !== null
        },

        roomIdPlaceholder(): string {
            return this.isEditingExistingRoom
                ? 'Leave blank to keep the current ID'
                : 'Leave blank to assign one automatically'
        },

        saveButtonLabel(): string {
            return this.isEditingExistingRoom ? 'Save changes' : 'Add room'
        },
    },

    watch: {
        room(): void {
            this.form = { ...this.room }
        },
    },
})
</script>

<template>
    <VeeForm v-slot="{ meta }" as="div" class="space-y-4">
        <BaseInput
            v-model="form.roomID"
            label="Room ID"
            name="roomID"
            rules="integer"
            :placeholder="roomIdPlaceholder"
        />

        <BaseSelect
            v-model="form.roomType"
            label="Room Type"
            name="roomType"
            rules="required"
            :options="roomTypes"
        />

        <UiButton
            class="w-full"
            :disabled="$wait.any() > 0 || !meta.valid"
            @click="$emit('save', { ...form })"
        >
            {{ saveButtonLabel }}
        </UiButton>
    </VeeForm>
</template>
