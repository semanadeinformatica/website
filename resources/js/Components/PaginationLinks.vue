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
    <nav v-if="links.length > 3" class="flex flex-row">
        <template v-for="link in links" :key="link">
            <Link
                v-if="link.url"
                :href="link.url"
                class="flex h-8 w-8 items-center justify-center border border-r-0 border-black last:border-r"
                v-bind="linkProps"
            >
                {{ link.label }}
            </Link>
        </template>
    </nav>
</template>
