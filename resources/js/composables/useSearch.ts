import { ref, unref, watch } from "vue";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";

const useSearch = (param: string = "query", only?: string[]) => {
    const searchQuery = ref(
        new URLSearchParams(location.search).get(param) ?? "",
    );

    watch(searchQuery, () => {
        const ziggy = route();
        const name = ziggy.current();
        if (!name) return;

        router.visit(
            route(name as "home", {
                ...ziggy.params,
                query: unref(searchQuery),
                page: 1,
            }) as string,
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only,
            },
        );
    });

    return searchQuery;
};

export default useSearch;
