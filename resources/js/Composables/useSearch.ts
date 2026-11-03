import { onBeforeUnmount, ref, unref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const useSearch = (param: string = "query", only?: string[]) => {
    const searchQuery = ref(
        typeof window !== "undefined"
            ? (new URLSearchParams(window.location.search).get(param) ?? "")
            : "",
    );

    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    watch(searchQuery, () => {
        if (debounceTimer) clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            if (typeof window === "undefined") return;

            const url = new URL(window.location.href);
            const val = unref(searchQuery)?.trim();
            const currentValue = url.searchParams.get(param) ?? "";

            if (val === currentValue) return;

            if (val) {
                url.searchParams.set(param, val);
            } else {
                url.searchParams.delete(param);
            }

            url.searchParams.set("page", "1");

            router.visit(url.toString(), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only,
            });
        }, 400);
    });

    onBeforeUnmount(() => {
        if (debounceTimer) clearTimeout(debounceTimer);
    });

    return searchQuery;
};

export default useSearch;
