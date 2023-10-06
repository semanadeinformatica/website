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
    <main class="min-h-screen bg-2023-bg">
        <div
            class="flex min-h-screen flex-col content-center items-center justify-center gap-44 py-16"
        >
            <img
                id="svg-1"
                class="absolute left-[7%] w-36 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="/../images/cy-sinf.svg"
                :alt="$t('general.sinfLogoAlt')"
            />
            <img
                id="svg-2"
                class="absolute right-[7%] top-16 w-32 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="/../images/rc-sinf.svg"
                :alt="$t('general.sinfLogoAlt')"
            />
            <img
                id="svg-3"
                class="absolute bottom-28 right-[15%] w-20 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="/../images/oc-sinf.svg"
                :alt="$t('general.sinfLogoAlt')"
            />

            <div class="relative">
                <img
                    class="w-96 max-ml:w-72"
                    src="/../images/sinf logo.png"
                    :alt="$t('general.sinfLogoAlt')"
                />
                <span
                    class="margin-0 absolute -bottom-5 right-0 text-xl font-bold text-2023-teal"
                    >2023</span
                >
            </div>

            <div
                class="flex w-fit flex-col items-center gap-7 text-center text-2023-teal-dark"
            >
                <h1 class="text-8xl">{{ props.status }}</h1>
                <p>{{ description }}</p>
                <PrimaryButton
                    class="w-fit"
                    text-size="text-xl"
                    @click="goBack()"
                >
                    {{ $t("pages.error.back") }}
                </PrimaryButton>
            </div>
            <div class="text-center text-lg text-2023-teal-dark">
                <p class="font-bold">{{ $t("pages.error.moreInfo") }}</p>
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
