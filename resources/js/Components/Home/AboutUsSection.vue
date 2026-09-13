<script setup lang="ts">
import { useScrollReveal } from "@/Composables/useScrollReveal";

const { targetRef, isVisible } = useScrollReveal({
    threshold: 0.15,
    once: false,
});
</script>

<template>
    <div
        ref="targetRef"
        class="relative mx-auto my-auto flex w-full max-w-7xl flex-col justify-center px-4 py-4 sm:px-6 sm:py-0 lg:px-8"
    >
        <div
            class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12 lg:gap-12"
        >
            <div
                class="self-center transition-all duration-700 ease-in-out lg:col-span-6 xl:col-span-7"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0',
                ]"
            >
                <h2
                    class="mt-2 text-3xl tracking-tight text-white sm:text-5xl lg:text-6xl"
                >
                    Feita por estudantes,<br />
                    <span class="font-black">para estudantes</span>
                </h2>
            </div>

            <div
                class="text-base leading-relaxed text-neutral-300 transition-all delay-150 duration-700 ease-in-out sm:text-lg lg:col-span-6 lg:pt-6 xl:col-span-5"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0',
                ]"
            >
                <p>
                    {{ $t("homePage.aboutUsDescription") }}
                </p>
            </div>
        </div>

        <div
            class="mt-8 grid grid-cols-1 gap-6 border-t border-white/10 pt-6 transition-all delay-250 duration-700 ease-in-out sm:mt-10 sm:pt-8 md:grid-cols-3 md:gap-8 lg:gap-12"
            :class="[
                isVisible
                    ? 'border-white/10 opacity-100'
                    : 'border-transparent opacity-0',
            ]"
        >
            <div
                class="space-y-1.5 transition-all delay-300 duration-700 ease-in-out"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                <h3 class="text-base font-bold text-white sm:text-lg">
                    {{ $t("homePage.pillar1Title") }}
                </h3>
                <p class="text-xs leading-relaxed text-neutral-400 sm:text-sm">
                    {{ $t("homePage.pillar1Desc") }}
                </p>
            </div>

            <div
                class="space-y-1.5 transition-all delay-400 duration-700 ease-in-out"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                <h3 class="text-base font-bold text-white sm:text-lg">
                    {{ $t("homePage.pillar2Title") }}
                </h3>
                <p class="text-xs leading-relaxed text-neutral-400 sm:text-sm">
                    {{ $t("homePage.pillar2Desc") }}
                </p>
            </div>

            <div
                class="space-y-1.5 transition-all delay-500 duration-700 ease-in-out"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                <h3 class="text-base font-bold text-white sm:text-lg">
                    {{ $t("homePage.pillar3Title") }}
                </h3>
                <p class="text-xs leading-relaxed text-neutral-400 sm:text-sm">
                    {{ $t("homePage.pillar3Desc") }}
                </p>
            </div>
        </div>
    </div>
</template>
