declare module '@meforma/vue-wait-for' {
    import type { Plugin } from 'vue'

    export type WaitTracker = {
        is(name: string): boolean
        start(name: string): number | false
        end(name: string): number
        any(): number
        clear(): Record<string, number>
    }

    export const $wait: WaitTracker

    const waitPlugin: Plugin
    export default waitPlugin
}
