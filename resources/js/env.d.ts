/// <reference types="vite/client" />

import type MyPageProps from "./Types/PageProps";
import type { Config } from "ziggy-js";

declare global {
    var Ziggy: Config;
}

declare module "@inertiajs/core" {
    type PageProps = MyPageProps;
}

export {};
