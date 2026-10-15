<script lang="ts">
import { defineComponent } from 'vue'
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet'

export default defineComponent({
    name: 'BaseSideDrawer',

    components: {
        Sheet,
        SheetContent,
        SheetDescription,
        SheetHeader,
        SheetTitle,
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
            default: '',
        },
    },

    emits: ['close'],

    methods: {
        closeWhenDismissed(isOpen: boolean): void {
            if (!isOpen) {
                this.$emit('close')
            }
        },
    },
})
</script>

<template>
    <Sheet :open="isOpen" @update:open="closeWhenDismissed">
        <SheetContent side="right" class="w-full sm:max-w-md">
            <SheetHeader>
                <SheetTitle>{{ title }}</SheetTitle>
                <SheetDescription :class="{ 'sr-only': !description }">
                    {{ description || title }}
                </SheetDescription>
            </SheetHeader>

            <div class="px-4 pb-4">
                <slot />
            </div>
        </SheetContent>
    </Sheet>
</template>
