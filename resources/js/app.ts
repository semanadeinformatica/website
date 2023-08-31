import "../css/app.css";

import { createApp, h, type DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
// @ts-expect-error: Ziggy does not have type declarations
import { ZiggyVue } from "ziggy";
import { OhVueIcon, addIcons } from "oh-vue-icons";

import * as IoIcons from "oh-vue-icons/icons/io";
import * as FaIcons from "oh-vue-icons/icons/fa";

const Io = Object.values({ ...IoIcons });
const Fa = Object.values({ ...FaIcons });
addIcons(...Io);
addIcons(...Fa);

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

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
            .use(ZiggyVue, Ziggy)
            .component("v-icon", OhVueIcon)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
