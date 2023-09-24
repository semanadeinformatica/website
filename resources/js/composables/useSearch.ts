import { ref, watchEffect, unref } from "vue";
import route from "ziggy-js";
import { router } from "@inertiajs/vue3";

const useSearch = (param: string = 'query', only?: string[]) => {
    const searchQuery = ref(new URLSearchParams(location.search).get(param) ?? "");

    watchEffect(() => {
        const ziggy = route();

        router.replace(
            route(ziggy.current() ?? "#", { ...ziggy.params, query: unref(searchQuery), page: 1 }),
            {
                preserveState: true,
                preserveScroll: true,
                only,
            },
        );
    });

    return searchQuery;
};

export default useSearch;
