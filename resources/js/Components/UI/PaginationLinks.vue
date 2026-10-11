<script setup lang="ts">
import type { PaginationLink } from "@/Types/Paginated";
import { Link, type InertiaLinkProps } from "@inertiajs/vue3";

interface Props {
    links: PaginationLink[];
    linkProps?: Partial<InertiaLinkProps>;
}

defineProps<Props>();
</script>

<template>
    <nav
        v-if="links && links.length > 3"
        class="flex flex-wrap items-center justify-center gap-1.5 py-4"
        aria-label="Paginação"
    >
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                v-bind="linkProps"
                class="pill-item min-w-9 justify-center px-3 py-1.5 text-xs font-medium transition-all"
                :class="[
                    link.active
                        ? 'pill-item-active text-white'
                        : 'text-neutral-400 hover:bg-white/10 hover:text-white',
                ]"
            >
                <span v-html="link.label" />
            </Link>
            <span
                v-else
                class="pill-item min-w-9 cursor-not-allowed justify-center px-3 py-1.5 text-xs font-medium text-neutral-600 opacity-40 select-none"
            >
                <span v-html="link.label" />
            </span>
        </template>
    </nav>
</template>
