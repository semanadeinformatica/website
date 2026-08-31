<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

interface Props {
    status: number;
}

const props = defineProps<Props>();

const goBack = () => {
    window.history.back();
};

const description = computed(() => {
    return {
        500: "Whoops, something went wrong on our servers.",
        404: "Sorry, the page you are looking for could not be found.",
        403: "Sorry, you are forbidden from accessing this page.",
    }[props.status];
});
</script>

<template>
    <Head :title="props.status.toString()" />
    <main class="bg-2023-bg min-h-screen">
        <div
            class="flex min-h-screen flex-col content-center items-center justify-center gap-44 py-16"
        >
            <img
                id="svg-1"
                class="animate-2023-maintenance-jump max-ml:hidden absolute left-[7%] w-36 opacity-50"
                src="/../images/cy-sinf.svg"
                alt="Stylized SINF logo"
            />
            <img
                id="svg-2"
                class="animate-2023-maintenance-jump max-ml:hidden absolute top-16 right-[7%] w-32 opacity-50"
                src="/../images/rc-sinf.svg"
                alt="Stylized SINF logo"
            />
            <img
                id="svg-3"
                class="animate-2023-maintenance-jump max-ml:hidden absolute right-[15%] bottom-28 w-20 opacity-50"
                src="/../images/oc-sinf.svg"
                alt="Stylized SINF logo"
            />

            <div class="relative">
                <img
                    class="max-ml:w-72 w-96"
                    src="/../images/sinf logo.png"
                    alt="Stylized SINF logo"
                />
                <span
                    class="margin-0 text-2023-teal absolute right-0 -bottom-5 text-xl font-bold"
                    >2023</span
                >
            </div>

            <div
                class="text-2023-teal-dark flex w-fit flex-col items-center gap-7 text-center"
            >
                <h1 class="text-8xl">{{ props.status }}</h1>
                <p>{{ description }}</p>
                <PrimaryButton
                    class="w-fit"
                    text-size="text-xl"
                    @click="goBack()"
                >
                    Regressar
                </PrimaryButton>
            </div>
            <div class="text-2023-teal-dark text-center text-lg">
                <p class="font-bold">Mais informações</p>
                <p>geral@sinf.pt</p>
            </div>
        </div>
    </main>
</template>

<style>
body {
    margin-bottom: 0px !important;
}

#svg-1 {
    animation-delay: -0.5s;
}

#svg-2 {
    animation-delay: -1s;
}

#svg-3 {
    animation-delay: -0.8s;
}
</style>
