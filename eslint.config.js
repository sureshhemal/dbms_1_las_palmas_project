import js from '@eslint/js'
import { defineConfig } from 'eslint/config'
import pluginVue from 'eslint-plugin-vue'
import globals from 'globals'
import tseslint from 'typescript-eslint'

export default defineConfig([
    {
        ignores: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'resources/js/actions/**',
            'resources/js/components/ui/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
    },
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        files: ['**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: tseslint.parser,
            },
        },
    },
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        rules: {
            semi: ['error', 'never'],
            indent: 'off',
            quotes: ['error', 'single', { avoidEscape: true }],
            'linebreak-style': ['error', 'unix'],
            'object-curly-spacing': ['error', 'always'],
            'comma-dangle': ['error', 'always-multiline'],
            'arrow-parens': ['error', 'always'],
            'vue/component-name-in-template-casing': ['error', 'PascalCase'],
            'prefer-arrow-callback': ['error', { allowNamedFunctions: false }],
            'arrow-spacing': ['error', { before: true, after: true }],
            'vue/html-indent': 'off',
            'vue/max-attributes-per-line': 'off',
            'vue/singleline-html-element-content-newline': 'off',
            'vue/multiline-html-element-content-newline': 'off',
            'vue/html-closing-bracket-newline': 'off',
            'vue/html-self-closing': 'off',
        },
    },
])
