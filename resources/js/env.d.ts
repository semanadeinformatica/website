/// <reference types="vite/client" />

import type MyPageProps from "./Types/PageProps";
import type { Config } from "ziggy-js";

// We need to use var in declare global blocks
/* eslint-disable no-var */
declare global {
    var Ziggy: Config;
}
/* eslint-enable no-var */

declare module "@inertiajs/core" {
    interface PageProps extends MyPageProps {}
}

export {};
