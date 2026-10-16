<script lang="ts">
import type { CalendarDate, DateValue } from '@internationalized/date'
import { parseDate } from '@internationalized/date'
import { CalendarDays, ChevronLeft, ChevronRight } from '@lucide/vue'
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarNext,
    CalendarPrev,
    CalendarRoot,
    PopoverContent,
    PopoverPortal,
    PopoverRoot,
    PopoverTrigger,
} from 'reka-ui'
import { Field } from 'vee-validate'
import { defineComponent } from 'vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'

const yearsOfferedWithoutLimits = 120

const monthNames = Array.from({ length: 12 }, (_, monthIndex) =>
    new Intl.DateTimeFormat('en-US', { month: 'long', timeZone: 'UTC' }).format(
        new Date(Date.UTC(2000, monthIndex, 1)),
    ),
)

function toCalendarDate(value: string): CalendarDate | undefined {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return undefined
    }

    try {
        return parseDate(value)
    } catch {
        return undefined
    }
}

export default defineComponent({
    name: 'BaseDatePicker',

    components: {
        CalendarCell,
        CalendarCellTrigger,
        CalendarDaysIcon: CalendarDays,
        CalendarGrid,
        CalendarGridBody,
        CalendarGridHead,
        CalendarGridRow,
        CalendarHeadCell,
        CalendarNext,
        CalendarPrev,
        CalendarRoot,
        ChevronLeft,
        ChevronRight,
        Field,
        InputError,
        PopoverContent,
        PopoverPortal,
        PopoverRoot,
        PopoverTrigger,
        UiButton: Button,
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
        rules: {
            type: String,
            default: '',
        },
        min: {
            type: String,
            default: '',
        },
        max: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: 'Pick a date',
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            isOpen: false,
            displayedMonth: '',
            monthNames,
        }
    },

    computed: {
        hasValue(): boolean {
            return this.modelValue !== ''
        },

        isRequired(): boolean {
            return this.rules.split('|').includes('required')
        },

        selectedDate(): CalendarDate | undefined {
            return toCalendarDate(this.modelValue)
        },

        earliestDate(): CalendarDate | undefined {
            return toCalendarDate(this.min)
        },

        latestDate(): CalendarDate | undefined {
            return toCalendarDate(this.max)
        },

        displayedMonthDate(): CalendarDate | undefined {
            return toCalendarDate(this.displayedMonth)
        },

        formattedValue(): string {
            if (!this.selectedDate) {
                return this.placeholder
            }

            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                timeZone: 'UTC',
            }).format(new Date(`${this.selectedDate.toString()}T00:00:00Z`))
        },

        offeredYears(): number[] {
            const currentYear = new Date().getUTCFullYear()
            const lastYear = this.latestDate?.year ?? currentYear + 10
            const firstYear =
                this.earliestDate?.year ?? lastYear - yearsOfferedWithoutLimits

            return Array.from(
                { length: lastYear - firstYear + 1 },
                (_, offset) => lastYear - offset,
            )
        },
    },

    methods: {
        changeOpenState(isOpen: boolean, markAsTouched: () => void): void {
            if (isOpen) {
                this.displayedMonth = (
                    this.selectedDate ??
                    this.latestDate ??
                    ''
                ).toString()
            } else {
                markAsTouched()
            }

            this.isOpen = isOpen
        },

        choose(date: DateValue | DateValue[] | null | undefined): void {
            if (!date || Array.isArray(date)) {
                return
            }

            this.$emit('update:modelValue', date.toString())
            this.isOpen = false
        },

        clear(): void {
            this.$emit('update:modelValue', '')
            this.isOpen = false
        },

        showMonthOf(shownDate: DateValue | undefined): void {
            this.displayedMonth = shownDate?.toString().slice(0, 10) ?? ''
        },

        showMonth(event: Event, shownDate: DateValue): void {
            this.showMonthOf(
                shownDate.set({
                    month: Number((event.target as HTMLSelectElement).value),
                }),
            )
        },

        showYear(event: Event, shownDate: DateValue): void {
            this.showMonthOf(
                shownDate.set({
                    year: Number((event.target as HTMLSelectElement).value),
                }),
            )
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
        <PopoverRoot
            :open="isOpen"
            @update:open="
                (isOpen: boolean) => changeOpenState(isOpen, handleBlur)
            "
        >
            <PopoverTrigger as-child>
                <button
                    :id="name"
                    type="button"
                    class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-neutral-200 bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-brand-700 focus-visible:ring-3 focus-visible:ring-brand-700/30 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:border-brand-400 dark:focus-visible:ring-brand-400/30"
                >
                    <span
                        :class="{
                            'text-neutral-500 dark:text-neutral-400':
                                !selectedDate,
                        }"
                    >
                        {{ formattedValue }}
                    </span>
                    <CalendarDaysIcon class="size-4 shrink-0 opacity-50" />
                </button>
            </PopoverTrigger>

            <PopoverPortal>
                <PopoverContent
                    align="start"
                    :side-offset="4"
                    class="z-50 rounded-md border border-neutral-200 bg-white p-3 text-neutral-900 shadow-md dark:border-neutral-800 dark:bg-neutral-950 dark:text-neutral-100"
                >
                    <CalendarRoot
                        v-slot="{ grid, weekDays, date: shownDate }"
                        :placeholder="displayedMonthDate"
                        @update:placeholder="showMonthOf"
                        :model-value="selectedDate"
                        :min-value="earliestDate"
                        :max-value="latestDate"
                        :week-starts-on="1"
                        fixed-weeks
                        prevent-deselect
                        initial-focus
                        @update:model-value="choose"
                    >
                        <div
                            class="mb-2 flex items-center justify-between gap-1"
                        >
                            <CalendarPrev
                                class="inline-flex size-7 items-center justify-center rounded-md hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-neutral-800"
                            >
                                <ChevronLeft class="size-4" />
                            </CalendarPrev>

                            <div class="flex items-center gap-1">
                                <select
                                    :value="shownDate.month"
                                    aria-label="Month"
                                    class="rounded-md border border-neutral-200 bg-transparent px-1.5 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                    @change="showMonth($event, shownDate)"
                                >
                                    <option
                                        v-for="(
                                            monthName, monthIndex
                                        ) in monthNames"
                                        :key="monthName"
                                        :value="monthIndex + 1"
                                    >
                                        {{ monthName }}
                                    </option>
                                </select>
                                <select
                                    :value="shownDate.year"
                                    aria-label="Year"
                                    class="rounded-md border border-neutral-200 bg-transparent px-1.5 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                    @change="showYear($event, shownDate)"
                                >
                                    <option
                                        v-for="year in offeredYears"
                                        :key="year"
                                        :value="year"
                                    >
                                        {{ year }}
                                    </option>
                                </select>
                            </div>

                            <CalendarNext
                                class="inline-flex size-7 items-center justify-center rounded-md hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-neutral-800"
                            >
                                <ChevronRight class="size-4" />
                            </CalendarNext>
                        </div>

                        <CalendarGrid
                            v-for="month in grid"
                            :key="month.value.toString()"
                            class="w-full border-collapse"
                        >
                            <CalendarGridHead>
                                <CalendarGridRow class="flex">
                                    <CalendarHeadCell
                                        v-for="weekDay in weekDays"
                                        :key="weekDay"
                                        class="w-8 text-center text-xs font-normal text-neutral-500 dark:text-neutral-400"
                                    >
                                        {{ weekDay }}
                                    </CalendarHeadCell>
                                </CalendarGridRow>
                            </CalendarGridHead>
                            <CalendarGridBody>
                                <CalendarGridRow
                                    v-for="(weekDates, weekIndex) in month.rows"
                                    :key="`week-${weekIndex}`"
                                    class="mt-1 flex"
                                >
                                    <CalendarCell
                                        v-for="weekDate in weekDates"
                                        :key="weekDate.toString()"
                                        :date="weekDate"
                                        class="p-0"
                                    >
                                        <CalendarCellTrigger
                                            :day="weekDate"
                                            :month="month.value"
                                            class="inline-flex size-8 items-center justify-center rounded-md text-sm tabular-nums outline-none hover:bg-neutral-100 focus-visible:ring-2 focus-visible:ring-brand-700/40 data-[disabled]:pointer-events-none data-[disabled]:text-neutral-300 data-[outside-view]:text-neutral-400 data-[selected]:bg-brand-700 data-[selected]:text-white data-[today]:font-semibold data-[today]:text-brand-700 data-[selected]:data-[today]:text-white dark:hover:bg-neutral-800 dark:data-[disabled]:text-neutral-700 dark:data-[outside-view]:text-neutral-600 dark:data-[selected]:bg-brand-400 dark:data-[selected]:text-brand-950 dark:data-[today]:text-brand-400 dark:data-[selected]:data-[today]:text-brand-950"
                                        />
                                    </CalendarCell>
                                </CalendarGridRow>
                            </CalendarGridBody>
                        </CalendarGrid>
                    </CalendarRoot>

                    <div
                        v-if="!isRequired && hasValue"
                        class="mt-2 flex justify-end"
                    >
                        <UiButton size="sm" variant="outline" @click="clear">
                            Clear
                        </UiButton>
                    </div>
                </PopoverContent>
            </PopoverPortal>
        </PopoverRoot>
        <InputError
            :message="meta.touched || hasValue ? errorMessage : undefined"
        />
    </Field>
</template>
