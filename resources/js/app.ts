import 'notivue/notification.css'
import 'notivue/animations.css'
import { createInertiaApp } from '@inertiajs/vue3'
import waitPlugin from '@meforma/vue-wait-for'
import { createNotivue } from 'notivue'
import { createPinia } from 'pinia'
import { initializeTheme } from '@/composables/useAppearance'
import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { initializeFlashToast } from '@/lib/flashToast'
import { http } from '@/lib/http'
import notifications from '@/mixins/notifications'
import '@/validation/rules'

const appName = import.meta.env.VITE_APP_NAME || 'Las Palmas'

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null
            case name.startsWith('auth/'):
                return AuthLayout
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout]
            default:
                return AppLayout
        }
    },
    withApp: (app) => {
        app.use(createPinia())
        app.use(createNotivue({ position: 'bottom-left' }))
        app.use(waitPlugin)
        app.mixin(notifications)
        app.config.globalProperties.$http = http

        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus()
                }
            },
        })
    },
    progress: {
        color: '#4B5563',
    },
})

// This will set light / dark mode on page load...
initializeTheme()

// This will listen for flash toast data from the server...
initializeFlashToast()
