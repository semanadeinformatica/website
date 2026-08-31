import globals from "globals";
import pluginVue from "eslint-plugin-vue";
import tseslint from "typescript-eslint";
import eslintConfigPrettier from "eslint-config-prettier";

export default tseslint.config(
    {
        ignores: ["vendor/**", "node_modules/**", "public/build/**"],
    },
    ...tseslint.configs.recommended,
    ...pluginVue.configs["flat/recommended"],
    eslintConfigPrettier,
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        rules: {
            "vue/multi-word-component-names": "off",
            "vue/component-api-style": ["warn", ["script-setup"]],
            "vue/component-name-in-template-casing": ["error", "PascalCase"],
            "vue/define-props-declaration": ["error", "type-based"],
            "vue/no-undef-components": "error",
            "vue/no-useless-mustaches": "error",
        },
    },
    {
        files: ["**/*.vue"],
        languageOptions: {
            parserOptions: {
                parser: tseslint.parser,
            },
        },
    },
);
