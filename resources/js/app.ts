import "../css/app.css";

import { createApp, h, type DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createVfm } from "vue-final-modal";
import VueCookies from "vue-cookies";
import { ZiggyVue } from "ziggy-js";
import { createI18n } from "vue-i18n";
import messages from "./messages";
import { VueQrcodeReader } from "vue-qrcode-reader";

const vfm = createVfm();

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale: "pt",
    fallbackLocale: "en",
    messages,
    datetimeFormats: {
        en: {
            short: {
                year: "numeric",
                month: "numeric",
                day: "numeric",
                timeZone: "Europe/Lisbon",
            },
            long: {
                year: "numeric",
                month: "long",
                day: "numeric",
                timeZone: "Europe/Lisbon",
            },
            day: {
                day: "numeric",
                timeZone: "Europe/Lisbon",
            },
            hourMinute: {
                hour: "numeric",
                minute: "numeric",
                timeZone: "Europe/Lisbon",
            },
            fullTime: {
                month: "numeric",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
                timeZone: "Europe/Lisbon",
            },
        },
        pt: {
            short: {
                year: "numeric",
                month: "numeric",
                day: "numeric",
                timeZone: "Europe/Lisbon",
            },
            long: {
                year: "numeric",
                month: "long",
                day: "numeric",
                timeZone: "Europe/Lisbon",
            },
            day: {
                day: "numeric",
                timeZone: "Europe/Lisbon",
            },
            hourMinute: {
                hour: "numeric",
                minute: "numeric",
                timeZone: "Europe/Lisbon",
            },
            fullTime: {
                month: "numeric",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
                timeZone: "Europe/Lisbon",
            },
        },
    },
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        ),
    setup: ({ el, App, props, plugin }) => {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vfm)
            .use(i18n)
            .use(ZiggyVue, Ziggy)
            .use(VueCookies, { expires: "7d" })
            .use(VueQrcodeReader)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
