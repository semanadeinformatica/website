/// <reference types="vite/client" />

import type MyPageProps from "./Types/PageProps";
import type { Config } from "ziggy-js";

declare global {
    var Ziggy: Config;
}

declare module "@inertiajs/core" {
    // eslint-disable-next-line @typescript-eslint/no-empty-object-type
    interface PageProps extends MyPageProps {}
}

export {};
