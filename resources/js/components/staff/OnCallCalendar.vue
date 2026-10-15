<script lang="ts">
import FullCalendar from '@fullcalendar/vue3'
import type {
    CalendarOptions,
    DateSelectInfo,
    DatesSetInfo,
    EventChangeInfo,
    EventClickInfo,
    EventInput,
} from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/vue3/daygrid'
import interactionPlugin from '@fullcalendar/vue3/interaction'
import classicThemePlugin from '@fullcalendar/vue3/themes/classic'
import '@fullcalendar/vue3/skeleton.css'
import '@fullcalendar/vue3/themes/classic/theme.css'
import '@fullcalendar/vue3/themes/classic/palette.css'
import '../../../css/calendar.css'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { addDays } from '@/lib/calendarDate'
import { nurseColor } from '@/lib/nurseColor'
import type { OnCall } from '@/types'

const mostOnCallsShownPerDay = 4

export default defineComponent({
    name: 'OnCallCalendar',

    components: {
        FullCalendar,
    },

    props: {
        onCalls: {
            type: Array as PropType<OnCall[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        timeZone: {
            type: String,
            required: true,
        },
    },

    emits: ['range-change', 'select-dates', 'open', 'change-dates'],

    computed: {
        calendarEvents(): EventInput[] {
            return this.onCalls.map((onCall) => ({
                id: onCall.id,
                title: `${onCall.nurse.name} (${onCall.nurse.nurseID})`,
                start: onCall.startDate,
                end: addDays(onCall.endDate, 1),
                allDay: true,
                color: nurseColor(onCall.nurse_id),
                extendedProps: { onCall },
            }))
        },

        calendarOptions(): CalendarOptions {
            return {
                plugins: [classicThemePlugin, dayGridPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                timeZone: this.timeZone,
                headerToolbar: {
                    start: 'title',
                    end: 'today prev,next',
                },
                height: 'auto',
                firstDay: 1,
                selectable: true,
                editable: true,
                eventResizableFromStart: true,
                displayEventTime: false,
                dayMaxEvents: mostOnCallsShownPerDay,
                events: this.calendarEvents,
                datesSet: this.changeVisibleRange,
                select: this.selectDates,
                eventClick: this.openOnCall,
                eventDrop: this.changeOnCallDates,
                eventResize: this.changeOnCallDates,
            }
        },
    },

    methods: {
        changeVisibleRange(range: DatesSetInfo): void {
            this.$emit('range-change', {
                from: range.startStr.slice(0, 10),
                to: addDays(range.endStr.slice(0, 10), -1),
            })
        },

        selectDates(selection: DateSelectInfo): void {
            const startDate = selection.startStr.slice(0, 10)
            const lastSelectedDay = addDays(selection.endStr.slice(0, 10), -1)

            this.$emit('select-dates', {
                startDate,
                endDate:
                    lastSelectedDay > startDate
                        ? lastSelectedDay
                        : addDays(startDate, 1),
            })
        },

        openOnCall(click: EventClickInfo): void {
            this.$emit('open', click.event.extendedProps.onCall as OnCall)
        },

        changeOnCallDates(change: EventChangeInfo): void {
            this.$emit('change-dates', {
                onCall: change.event.extendedProps.onCall as OnCall,
                startDate: change.event.startStr.slice(0, 10),
                endDate: addDays(change.event.endStr.slice(0, 10), -1),
                revert: change.revert,
            })
        },
    },
})
</script>

<template>
    <div
        class="rounded-xl border border-neutral-200 bg-white p-4 transition-opacity dark:border-neutral-800 dark:bg-neutral-950"
        :class="{ 'opacity-60': isLoading }"
        :aria-busy="isLoading"
    >
        <FullCalendar :options="calendarOptions" />
    </div>
</template>
