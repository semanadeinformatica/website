<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

type ButtonProps = {
    as: "button";
};
type AnchorProps = {
    as: "a";
    href: string;
};
type LinkProps = {
    as?: undefined;
    href: string;
    method?: "get" | "post";
    only?: string[];
};

type Props = ButtonProps | AnchorProps | LinkProps;

const props = withDefaults(defineProps<Props>(), {
    href: "#",
    as: undefined,
    method: "get",
    only: undefined,
});

const classes =
    "block w-full px-2 py-2 text-md text-2023-bg underline font-semibold bg-2023-red-dark hover:bg-2023-red focus:outline-none focus:bg-2023-red transition duration-150 ease-in-out";
</script>

<template>
    <div>
        <button v-if="as === 'button'" type="submit" :class="classes">
            <slot />
        </button>

        <a v-else-if="as === 'a'" :href="props.href" :class="classes">
            <slot />
        </a>

        <Link
            v-else
            :href="props.href"
            :method="props.method"
            :class="classes"
            :only="props.only"
            preserve-state
        >
            <slot />
        </Link>
    </div>
</template>
