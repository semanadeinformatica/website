/** @type {import("eslint").ESLint.ConfigData} */
module.exports = {
    root: true,

    env: {
        browser: true,
        es2017: true,
        node: true,
    },
    parser: "vue-eslint-parser",
    parserOptions: {
        parser: "@typescript-eslint/parser",
        ecmaVersion: 2020,
        sourceType: "module",
    },
    extends: [
        "eslint:recommended",
        "plugin:@typescript-eslint/eslint-recommended",
        "plugin:@typescript-eslint/recommended",
        "plugin:vue/vue3-recommended",
        "prettier",
    ],
    plugins: ["@typescript-eslint"],
    rules: {
        "vue/multi-word-component-names": "off",
        "vue/component-api-style": ["warn", ["script-setup"]],
        "vue/component-name-in-template-casing": ["error", "PascalCase"],
        "vue/define-props-declaration": ["error", "type-based"],
        "vue/no-undef-components": "error",
        "vue/no-useless-mustaches": "error",
    },
};
