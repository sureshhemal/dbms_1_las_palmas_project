<script lang="ts">
import { Notification, Notivue } from 'notivue'
import { defineComponent } from 'vue'
import { notificationTheme } from '@/lib/notificationTheme'

export default defineComponent({
    name: 'AppNotifications',

    components: {
        Notification,
        Notivue,
    },

    data() {
        return {
            notificationTheme,
        }
    },
})
</script>

<template>
    <Notivue v-slot="notificationItem">
        <Notification :item="notificationItem" :theme="notificationTheme" />
    </Notivue>
</template>
