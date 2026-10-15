<script lang="ts">
import { Search } from '@lucide/vue'
import { defineComponent } from 'vue'
import { Input } from '@/components/ui/input'

const typingPauseInMilliseconds = 400

export default defineComponent({
    name: 'BaseSearchInput',

    components: {
        SearchIcon: Search,
        UiInput: Input,
    },

    props: {
        modelValue: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: 'Search',
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            typedSearchTerm: this.modelValue,
            typingPauseTimer: 0,
        }
    },

    watch: {
        typedSearchTerm(typedSearchTerm: string): void {
            window.clearTimeout(this.typingPauseTimer)

            this.typingPauseTimer = window.setTimeout(
                () => this.$emit('update:modelValue', typedSearchTerm.trim()),
                typingPauseInMilliseconds,
            )
        },
    },

    beforeUnmount() {
        window.clearTimeout(this.typingPauseTimer)
    },
})
</script>

<template>
    <div class="relative w-full max-w-xs">
        <SearchIcon
            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400"
        />
        <UiInput
            v-model="typedSearchTerm"
            type="search"
            :placeholder="placeholder"
            :aria-label="placeholder"
            class="pl-9"
        />
    </div>
</template>
