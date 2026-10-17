<script lang="ts">
import { Head } from '@inertiajs/vue3'
import { ExternalLink } from '@lucide/vue'
import type { Component } from 'vue'
import { defineComponent, markRaw } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PublicLayout from '@/layouts/PublicLayout.vue'
import { documentation } from '@/routes'

const repositoryReadmeUrl =
    'https://github.com/sureshhemal/dbms_1_las_palmas_project#readme'

export default defineComponent({
    name: 'DocumentationPage',

    components: {
        ExternalLink,
        InertiaHead: Head,
    },

    props: {
        html: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            breadcrumbs: [
                {
                    title: 'Documentation',
                    href: documentation(),
                },
            ],
            repositoryReadmeUrl,
        }
    },

    computed: {
        isSignedIn(): boolean {
            return Boolean(this.$page.props.auth.user)
        },

        layout(): Component {
            return this.isSignedIn ? markRaw(AppLayout) : markRaw(PublicLayout)
        },

        layoutProps(): Record<string, unknown> {
            return this.isSignedIn ? { breadcrumbs: this.breadcrumbs } : {}
        },
    },
})
</script>

<template>
    <component :is="layout" v-bind="layoutProps">
        <div class="flex h-full flex-1 flex-col p-4">
            <InertiaHead title="Documentation" />

            <div class="mb-4 flex justify-end">
                <a
                    :href="repositoryReadmeUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-neutral-100"
                >
                    <ExternalLink class="size-4" />
                    View on GitHub
                </a>
            </div>

            <article
                class="prose max-w-4xl prose-neutral dark:prose-invert prose-headings:scroll-mt-20 prose-a:text-brand-700 dark:prose-a:text-brand-300 prose-code:before:content-none prose-code:after:content-none prose-pre:bg-neutral-900 prose-table:block prose-table:overflow-x-auto"
                v-html="html"
            />
        </div>
    </component>
</template>
