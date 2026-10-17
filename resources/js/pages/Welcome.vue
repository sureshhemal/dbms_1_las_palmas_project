<script lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowRight,
    BedDouble,
    BookOpen,
    Building2,
    CalendarDays,
    Database,
    FolderGit2,
    UserRound,
    Users,
} from '@lucide/vue'
import type { Component } from 'vue'
import { defineComponent, markRaw } from 'vue'
import PublicHeader from '@/components/PublicHeader.vue'
import { Button } from '@/components/ui/button'
import { dashboard, documentation, login, register } from '@/routes'

type Feature = {
    title: string
    description: string
    icon: Component
}

const repositoryUrl = 'https://github.com/sureshhemal/dbms_1_las_palmas_project'

const features: Feature[] = [
    {
        title: 'Staff & on-call',
        description:
            'Physicians and nurses, the departments they work in, and a drag-and-drop calendar for on-call shifts.',
        icon: markRaw(Users),
    },
    {
        title: 'Hospital',
        description:
            'Departments with their heads, and rooms with live bed availability and the patients in them today.',
        icon: markRaw(Building2),
    },
    {
        title: 'Patients',
        description:
            'Searchable patient records, each with a profile of medications, procedures, prescriptions and appointments.',
        icon: markRaw(UserRound),
    },
    {
        title: 'Stays & procedures',
        description:
            'Record a procedure and admit the patient in one step, then extend or discharge, with room capacity checked.',
        icon: markRaw(BedDouble),
    },
    {
        title: 'Appointments',
        description:
            'Book, extend and cancel appointments in hospital time, with physician and status filters.',
        icon: markRaw(CalendarDays),
    },
    {
        title: 'SQL insight',
        description:
            'Shares and averages are computed in MySQL, and Clockwork shows the exact queries behind every page.',
        icon: markRaw(Database),
    },
]

export default defineComponent({
    name: 'WelcomePage',

    components: {
        ArrowRight,
        BookOpen,
        FolderGit2,
        InertiaHead: Head,
        InertiaLink: Link,
        PublicHeader,
        UiButton: Button,
    },

    data() {
        return {
            features,
            repositoryUrl,
        }
    },

    computed: {
        isSignedIn(): boolean {
            return Boolean(this.$page.props.auth.user)
        },
    },

    methods: {
        dashboard,
        documentation,
        login,
        register,
    },
})
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-white text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100"
    >
        <InertiaHead title="Welcome" />

        <PublicHeader />

        <main class="flex-1">
            <section class="relative overflow-hidden">
                <div
                    class="pointer-events-none absolute inset-x-0 -top-24 -z-0 mx-auto h-72 max-w-3xl rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-900/30"
                />

                <div
                    class="relative mx-auto max-w-3xl px-4 pt-20 pb-16 text-center sm:px-6 sm:pt-28"
                >
                    <span
                        class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-medium text-brand-800 dark:border-brand-900 dark:bg-brand-950 dark:text-brand-300"
                    >
                        Hospital management · MSc database project
                    </span>

                    <h1
                        class="mt-6 text-4xl font-semibold tracking-tight sm:text-5xl"
                    >
                        Run Las Palmas Medical Center from one place
                    </h1>

                    <p
                        class="mx-auto mt-5 max-w-2xl text-lg text-neutral-600 dark:text-neutral-300"
                    >
                        Staff, rooms, patients, stays, procedures, prescriptions
                        and appointments in one application, backed by a MySQL
                        database.
                    </p>

                    <div
                        class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <UiButton v-if="isSignedIn" size="lg" as-child>
                            <InertiaLink :href="dashboard()">
                                Open dashboard
                                <ArrowRight />
                            </InertiaLink>
                        </UiButton>
                        <template v-else>
                            <UiButton size="lg" as-child>
                                <InertiaLink :href="login()">
                                    Log in
                                    <ArrowRight />
                                </InertiaLink>
                            </UiButton>
                            <UiButton size="lg" variant="outline" as-child>
                                <InertiaLink :href="register()">
                                    Create an account
                                </InertiaLink>
                            </UiButton>
                        </template>
                    </div>

                    <InertiaLink
                        :href="documentation()"
                        class="mt-6 inline-flex items-center gap-1.5 text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-neutral-100"
                    >
                        <BookOpen class="size-4" />
                        Read the documentation
                    </InertiaLink>
                </div>
            </section>

            <section class="mx-auto max-w-6xl px-4 pb-24 sm:px-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="feature in features"
                        :key="feature.title"
                        class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300"
                        >
                            <component :is="feature.icon" class="size-5" />
                        </div>
                        <h2 class="mt-4 font-semibold">{{ feature.title }}</h2>
                        <p
                            class="mt-1.5 text-sm text-neutral-600 dark:text-neutral-300"
                        >
                            {{ feature.description }}
                        </p>
                    </article>
                </div>
            </section>
        </main>

        <footer class="border-t border-neutral-200 dark:border-neutral-800">
            <div
                class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-neutral-500 sm:flex-row sm:px-6 dark:text-neutral-400"
            >
                <p>
                    Las Palmas Medical Center · Built with Laravel, Vue, Inertia
                    and MySQL
                </p>
                <a
                    :href="repositoryUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 hover:text-neutral-900 dark:hover:text-neutral-100"
                >
                    <FolderGit2 class="size-4" />
                    Source on GitHub
                </a>
            </div>
        </footer>
    </div>
</template>
