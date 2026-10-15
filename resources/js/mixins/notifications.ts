import { push } from 'notivue'
import type { ComponentOptions } from 'vue'

const notifications: ComponentOptions = {
    methods: {
        notifySuccess(message: string, title: string = 'Success'): void {
            push.success({ title, message })
        },

        notifyWarning(message: string, title: string = 'Warning'): void {
            push.warning({ title, message })
        },

        notifyError(message: string, title: string = 'Oops...'): void {
            push.error({ title, message })
        },
    },
}

export default notifications
