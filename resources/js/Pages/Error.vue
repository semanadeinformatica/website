<script setup lang="ts">
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import GlowingOrbs from "@/Components/UI/GlowingOrbs.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

interface Props {
    status: number;
}

const props = defineProps<Props>();

const goBack = () => {
    window.history.back();
};

const descriptions: Record<number, string[]> = {
    500: [
        "Algo correu mal nos nossos servidores.",
        "Podes tentar novamente daqui a uns minutos.",
    ],
    404: [
        "Não encontrámos a página que procuras.",
        "O endereço pode estar errado ou a página pode ter sido movida.",
    ],
    403: [
        "Não tens permissão para aceder a esta página.",
        "Se acreditas que isto é um erro, contacta-nos.",
    ],
};

const messages = computed(
    () => descriptions[props.status] ?? ["Ocorreu um erro inesperado."],
);
</script>

<template>
    <Head :title="props.status.toString()" />

    <div class="relative min-h-screen bg-olive-950 text-white">
        <GlowingOrbs />

        <main
            class="relative z-10 flex min-h-screen flex-col items-center justify-center px-4 py-16"
        >
            <div class="relative flex flex-col items-center text-center">
                <img
                    class="h-24 p-2 select-none sm:h-32"
                    src="/images/sinf2026.svg"
                    alt="SINF 2026 logo"
                />

                <h1
                    class="mt-10 bg-linear-to-r from-white to-white/40 bg-clip-text text-[7rem] leading-none font-bold text-transparent select-none sm:text-[10rem]"
                >
                    {{ props.status }}
                </h1>

                <div class="mt-6 max-w-md space-y-2">
                    <p
                        v-for="(line, idx) in messages"
                        :key="idx"
                        class="text-sm text-neutral-400"
                        :class="
                            idx === 0
                                ? 'text-lg font-medium text-neutral-200'
                                : ''
                        "
                    >
                        {{ line }}
                    </p>
                </div>

                <div
                    class="mt-10 flex flex-wrap items-center justify-center gap-4"
                >
                    <PrimaryButton color="pill" class="w-fit" @click="goBack">
                        Regressar
                    </PrimaryButton>
                </div>

                <div
                    class="mt-14 flex items-center justify-center gap-2 text-sm text-neutral-400"
                >
                    <span>Mais informações:</span>
                    <a
                        href="mailto:geral@sinf.pt"
                        class="text-neutral-200 underline-offset-4 transition-colors hover:text-white hover:underline"
                    >
                        geral@sinf.pt
                    </a>
                </div>
            </div>
        </main>
    </div>
</template>
