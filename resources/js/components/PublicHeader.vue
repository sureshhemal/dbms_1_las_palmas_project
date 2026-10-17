<script lang="ts">
import { Link } from '@inertiajs/vue3'
import { defineComponent } from 'vue'
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { Button } from '@/components/ui/button'
import { dashboard, home, login, register } from '@/routes'

export default defineComponent({
    name: 'PublicHeader',

    components: {
        AppLogoIcon,
        InertiaLink: Link,
        UiButton: Button,
    },

    computed: {
        isSignedIn(): boolean {
            return Boolean(this.$page.props.auth.user)
        },
    },

    methods: {
        dashboard,
        home,
        login,
        register,
    },
})
</script>

<template>
    <header class="border-b border-neutral-200 dark:border-neutral-800">
        <div
            class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6"
        >
            <InertiaLink :href="home()" class="flex items-center gap-3">
                <div
                    class="flex size-9 items-center justify-center rounded-lg bg-brand-600 text-white"
                >
                    <AppLogoIcon class="size-5 fill-current" />
                </div>
                <span class="font-semibold">Las Palmas Medical Center</span>
            </InertiaLink>

            <nav class="flex items-center gap-2">
                <UiButton v-if="isSignedIn" as-child>
                    <InertiaLink :href="dashboard()">Dashboard</InertiaLink>
                </UiButton>
                <template v-else>
                    <UiButton variant="ghost" as-child>
                        <InertiaLink :href="login()">Log in</InertiaLink>
                    </UiButton>
                    <UiButton variant="outline" as-child>
                        <InertiaLink :href="register()">Register</InertiaLink>
                    </UiButton>
                </template>
            </nav>
        </div>
    </header>
</template>
