<script lang="ts">
import { Check, ChevronDown, Search } from '@lucide/vue'
import type { PropType } from 'vue'
import { Field } from 'vee-validate'
import { defineComponent } from 'vue'
import InputError from '@/components/InputError.vue'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import type { SelectOption } from '@/types'

const typingPauseInMilliseconds = 300

export default defineComponent({
    name: 'BaseSearchableSelect',

    components: {
        CheckIcon: Check,
        ChevronDown,
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
        Field,
        InputError,
        SearchIcon: Search,
        UiInput: Input,
        UiLabel: Label,
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
            type: Array as PropType<SelectOption[]>,
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
        searchesRemotely: {
            type: Boolean,
            default: false,
        },
        isSearching: {
            type: Boolean,
            default: false,
        },
    },

    emits: ['update:modelValue', 'search'],

    data() {
        return {
            isOpen: false,
            optionSearchTerm: '',
            typingPauseTimer: 0,
            chosenOption: null as SelectOption | null,
        }
    },

    computed: {
        hasValue(): boolean {
            return this.modelValue !== ''
        },

        selectedOption(): SelectOption | undefined {
            return (
                this.options.find(
                    (option) => option.value === this.modelValue,
                ) ??
                (this.chosenOption?.value === this.modelValue
                    ? this.chosenOption
                    : undefined)
            )
        },

        selectedLabel(): string {
            return this.selectedOption?.label ?? this.placeholder
        },

        hasSelectedOption(): boolean {
            return this.selectedOption !== undefined
        },

        visibleOptions(): SelectOption[] {
            if (this.searchesRemotely) {
                return this.options
            }

            const optionSearchTerm = this.optionSearchTerm.trim().toLowerCase()

            return this.options.filter((option) =>
                option.label.toLowerCase().includes(optionSearchTerm),
            )
        },
    },

    watch: {
        optionSearchTerm(optionSearchTerm: string): void {
            if (!this.searchesRemotely) {
                return
            }

            window.clearTimeout(this.typingPauseTimer)

            this.typingPauseTimer = window.setTimeout(
                () => this.$emit('search', optionSearchTerm.trim()),
                typingPauseInMilliseconds,
            )
        },
    },

    beforeUnmount() {
        window.clearTimeout(this.typingPauseTimer)
    },

    methods: {
        changeOpenState(isOpen: boolean, markAsTouched: () => void): void {
            if (isOpen) {
                this.optionSearchTerm = ''
            } else {
                markAsTouched()
            }

            this.isOpen = isOpen
        },

        focusOptionSearch(event: Event): void {
            event.preventDefault()

            this.$nextTick(() => {
                const optionSearchInput = this.$refs.optionSearchInput as
                    | { $el: HTMLInputElement }
                    | undefined

                optionSearchInput?.$el.focus()
            })
        },

        keepTypingInsideOptionSearch(event: KeyboardEvent): void {
            if (event.key !== 'Escape') {
                event.stopPropagation()
            }
        },

        choose(option: SelectOption): void {
            this.chosenOption = option
            this.$emit('update:modelValue', option.value)
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
        <DropdownMenu
            :open="isOpen"
            @update:open="
                (isOpen: boolean) => changeOpenState(isOpen, handleBlur)
            "
        >
            <DropdownMenuTrigger as-child>
                <button
                    :id="name"
                    type="button"
                    class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-neutral-200 bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-brand-700 focus-visible:ring-3 focus-visible:ring-brand-700/30 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:border-brand-400 dark:focus-visible:ring-brand-400/30"
                >
                    <span
                        class="truncate"
                        :class="{
                            'text-neutral-500 dark:text-neutral-400':
                                !hasSelectedOption,
                        }"
                    >
                        {{ selectedLabel }}
                    </span>
                    <ChevronDown class="size-4 shrink-0 opacity-50" />
                </button>
            </DropdownMenuTrigger>

            <DropdownMenuContent
                align="start"
                class="flex max-h-[min(20rem,var(--reka-dropdown-menu-content-available-height))] w-(--reka-dropdown-menu-trigger-width) flex-col overflow-hidden"
                @open-auto-focus="focusOptionSearch"
            >
                <div class="relative shrink-0 p-1">
                    <SearchIcon
                        class="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-neutral-400"
                    />
                    <UiInput
                        ref="optionSearchInput"
                        v-model="optionSearchTerm"
                        type="search"
                        placeholder="Search"
                        :aria-label="`Search ${label}`"
                        class="h-8 pl-7"
                        @keydown="keepTypingInsideOptionSearch"
                    />
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <p
                        v-if="isSearching"
                        class="px-2 py-1.5 text-sm text-neutral-500 dark:text-neutral-400"
                    >
                        Searching…
                    </p>

                    <p
                        v-else-if="visibleOptions.length === 0"
                        class="px-2 py-1.5 text-sm text-neutral-500 dark:text-neutral-400"
                    >
                        No matches
                    </p>

                    <DropdownMenuItem
                        v-for="option in visibleOptions"
                        :key="option.value"
                        class="justify-between"
                        @select="choose(option)"
                    >
                        <span class="truncate">{{ option.label }}</span>
                        <CheckIcon
                            v-if="option.value === modelValue"
                            class="size-4 shrink-0"
                        />
                    </DropdownMenuItem>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
        <InputError
            :message="meta.touched || hasValue ? errorMessage : undefined"
        />
    </Field>
</template>
