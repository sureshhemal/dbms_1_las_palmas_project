<script lang="ts">
import type { Config, Data, Layout } from 'plotly.js'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { formatCount } from '@/lib/formatCount'
import type { ShareSlice } from '@/types'

const sliceColors = [
    '#0ea5e9',
    '#10b981',
    '#f59e0b',
    '#f43f5e',
    '#8b5cf6',
    '#14b8a6',
    '#f97316',
    '#6366f1',
    '#84cc16',
    '#ec4899',
    '#06b6d4',
    '#d946ef',
    '#eab308',
    '#3b82f6',
    '#22c55e',
    '#ef4444',
]

const otherSliceColor = '#a3a3a3'

const mostSlicesShown = 10

export default defineComponent({
    name: 'BaseShareChart',

    props: {
        slices: {
            type: Array as PropType<ShareSlice[]>,
            required: true,
        },
        countLabel: {
            type: String,
            required: true,
        },
        itemLabel: {
            type: String,
            required: true,
        },
        emptyMessage: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            themeObserver: null as MutationObserver | null,
        }
    },

    computed: {
        chartSlices(): ShareSlice[] {
            if (this.slices.length <= mostSlicesShown) {
                return this.slices
            }

            const shownSlices = this.slices.slice(0, mostSlicesShown - 1)
            const otherSlices = this.slices.slice(mostSlicesShown - 1)

            return [
                ...shownSlices,
                {
                    key: 'other',
                    name: `Other (${formatCount(otherSlices.length)} ${this.itemLabel})`,
                    isRemoved: false,
                    count: otherSlices.reduce(
                        (total, slice) => total + slice.count,
                        0,
                    ),
                    percentage: Number(
                        otherSlices
                            .reduce(
                                (total, slice) => total + slice.percentage,
                                0,
                            )
                            .toFixed(1),
                    ),
                },
            ]
        },

        totalLabel(): string {
            const totalCount = this.slices.reduce(
                (total, slice) => total + slice.count,
                0,
            )

            return `${formatCount(totalCount)} ${this.countLabel} in total`
        },
    },

    watch: {
        slices(): void {
            this.$nextTick(() => this.drawChart())
        },

        isLoading(): void {
            this.$nextTick(() => this.drawChart())
        },
    },

    mounted() {
        this.themeObserver = new MutationObserver(() => this.drawChart())
        this.themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        })

        this.drawChart()
    },

    async beforeUnmount() {
        this.themeObserver?.disconnect()

        const chartElement = this.$refs.chart as HTMLElement | undefined

        if (chartElement) {
            const { default: Plotly } = await import('plotly.js-basic-dist-min')

            Plotly.purge(chartElement)
        }
    },

    methods: {
        async drawChart(): Promise<void> {
            const chartElement = this.$refs.chart as HTMLElement | undefined

            if (!chartElement) {
                return
            }

            const { default: Plotly } = await import('plotly.js-basic-dist-min')
            const isDarkMode =
                document.documentElement.classList.contains('dark')
            const textColor = isDarkMode ? '#d4d4d4' : '#404040'

            const data: Data[] = [
                {
                    type: 'pie',
                    labels: this.chartSlices.map((slice) =>
                        slice.isRemoved
                            ? `${slice.name} (removed)`
                            : slice.name,
                    ),
                    values: this.chartSlices.map((slice) => slice.count),
                    text: this.chartSlices.map(
                        (slice) => `${slice.percentage}%`,
                    ),
                    textinfo: 'text',
                    textposition: 'inside',
                    hovertemplate: `%{label}<br>%{value} ${this.countLabel} · %{text}<extra></extra>`,
                    sort: false,
                    direction: 'clockwise',
                    marker: {
                        colors: this.chartSlices.map((slice, index) =>
                            slice.key === 'other'
                                ? otherSliceColor
                                : sliceColors[index],
                        ),
                        line: {
                            color: isDarkMode ? '#0a0a0a' : '#ffffff',
                            width: 1,
                        },
                    },
                },
            ]

            const layout: Partial<Layout> = {
                height: 320,
                margin: { t: 10, r: 10, b: 10, l: 10 },
                paper_bgcolor: 'rgba(0, 0, 0, 0)',
                plot_bgcolor: 'rgba(0, 0, 0, 0)',
                font: {
                    family: getComputedStyle(document.body).fontFamily,
                    color: textColor,
                },
                legend: { font: { color: textColor } },
            }

            const config: Partial<Config> = {
                responsive: true,
                displayModeBar: false,
            }

            await Plotly.react(chartElement, data, layout, config)
        },
    },
})
</script>

<template>
    <section
        class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div
            v-if="isLoading"
            class="flex flex-col items-center gap-6 sm:flex-row"
        >
            <div
                class="size-48 shrink-0 animate-pulse rounded-full bg-neutral-200 dark:bg-neutral-800"
            />
            <div class="w-full flex-1 space-y-3">
                <div
                    v-for="placeholder in 4"
                    :key="placeholder"
                    class="h-4 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                    :class="placeholder % 2 === 0 ? 'w-1/2' : 'w-2/3'"
                />
            </div>
        </div>

        <p
            v-else-if="slices.length === 0"
            class="py-6 text-center text-sm text-neutral-500 dark:text-neutral-400"
        >
            {{ emptyMessage }}
        </p>

        <div v-else>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                {{ totalLabel }}
            </p>

            <div ref="chart" class="w-full" />
        </div>
    </section>
</template>
