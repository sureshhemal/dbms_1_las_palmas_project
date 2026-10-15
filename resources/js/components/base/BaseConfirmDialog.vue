<script lang="ts">
import { defineComponent } from 'vue'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

export default defineComponent({
    name: 'BaseConfirmDialog',

    components: {
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogHeader,
        DialogTitle,
        UiButton: Button,
        UiDialog: Dialog,
    },

    props: {
        isOpen: {
            type: Boolean,
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        description: {
            type: String,
            required: true,
        },
        confirmLabel: {
            type: String,
            required: true,
        },
    },

    emits: ['confirm', 'cancel'],

    methods: {
        cancelWhenDismissed(isOpen: boolean): void {
            if (!isOpen) {
                this.$emit('cancel')
            }
        },
    },
})
</script>

<template>
    <UiDialog :open="isOpen" @update:open="cancelWhenDismissed">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <UiButton
                    variant="outline"
                    :disabled="$wait.any() > 0"
                    @click="$emit('cancel')"
                >
                    Cancel
                </UiButton>
                <UiButton
                    variant="destructive"
                    :disabled="$wait.any() > 0"
                    @click="$emit('confirm')"
                >
                    {{ confirmLabel }}
                </UiButton>
            </DialogFooter>
        </DialogContent>
    </UiDialog>
</template>
