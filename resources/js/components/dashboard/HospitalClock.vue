<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'HospitalClock',

    props: {
        timeZone: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            now: new Date(),
            tickTimer: null as ReturnType<typeof setInterval> | null,
        }
    },

    computed: {
        hoursAndMinutes(): string {
            return new Intl.DateTimeFormat('en-GB', {
                timeZone: this.timeZone,
                hour: '2-digit',
                minute: '2-digit',
                hourCycle: 'h23',
            }).format(this.now)
        },

        seconds(): string {
            return new Intl.DateTimeFormat('en-GB', {
                timeZone: this.timeZone,
                second: '2-digit',
            })
                .format(this.now)
                .padStart(2, '0')
        },

        longDate(): string {
            return new Intl.DateTimeFormat('en-US', {
                timeZone: this.timeZone,
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            }).format(this.now)
        },

        timeZoneName(): string {
            const parts = new Intl.DateTimeFormat('en-US', {
                timeZone: this.timeZone,
                timeZoneName: 'long',
            }).formatToParts(this.now)

            return (
                parts.find((part) => part.type === 'timeZoneName')?.value ??
                this.timeZone
            )
        },
    },

    mounted() {
        this.tickTimer = setInterval(() => {
            this.now = new Date()
        }, 1000)
    },

    beforeUnmount() {
        if (this.tickTimer) {
            clearInterval(this.tickTimer)
        }
    },
})
</script>

<template>
    <section
        class="flex flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white p-6 text-center dark:border-neutral-800 dark:bg-neutral-950"
    >
        <p
            class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
        >
            Hospital Time
        </p>

        <p
            class="mt-2 font-mono text-6xl font-semibold tracking-tight text-neutral-900 tabular-nums dark:text-neutral-100"
            role="timer"
            :aria-label="`${hoursAndMinutes}:${seconds} in ${timeZone}`"
        >
            {{ hoursAndMinutes
            }}<span class="text-brand-600 dark:text-brand-400"
                >:{{ seconds }}</span
            >
        </p>

        <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">
            {{ longDate }}
        </p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">
            {{ timeZoneName }} ({{ timeZone }})
        </p>
    </section>
</template>
