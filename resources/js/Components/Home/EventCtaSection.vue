<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Calendar, Users, Check, UserPlus } from "@lucide/vue";
import { useScrollReveal } from "@/Composables/useScrollReveal";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import Card from "@/Components/UI/Card.vue";

interface Props {
    canEnroll: boolean;
}

defineProps<Props>();

const { targetRef, isVisible } = useScrollReveal({
    threshold: 0.15,
    once: false,
});
</script>

<template>
    <div
        ref="targetRef"
        class="relative mx-auto my-auto flex w-full max-w-7xl flex-col justify-center px-4 sm:px-6 lg:px-8"
    >
        <div class="mb-8 flex flex-col items-center text-center sm:mb-10">
            <h2
                class="mt-4 text-3xl font-black tracking-tight text-white transition-all duration-700 ease-in-out sm:text-5xl lg:text-6xl"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-12 opacity-0',
                ]"
            >
                Junta-te a nós na SINF 2026
            </h2>
            <p
                class="mt-3 max-w-2xl text-sm leading-relaxed text-neutral-300 transition-all duration-700 ease-in-out delay-150 sm:text-base"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0',
                ]"
            >
                Entrada livre e aberta a todos os estudantes. Quatro dias de
                palestras técnicas, workshops práticos e contacto direto com as
                equipas de engenharia das empresas participantes.
            </p>
        </div>

        <div
            class="grid grid-cols-1 gap-6 border-t border-white/10 pt-8 transition-all duration-700 ease-in-out delay-250 md:grid-cols-3 md:gap-6 lg:gap-8"
            :class="[
                isVisible
                    ? 'border-white/10 opacity-100'
                    : 'border-transparent opacity-0',
            ]"
        >
            <Card
                padding="p-6 sm:p-7"
                :interactive="false"
                class="flex flex-col justify-between transition-all duration-700 ease-in-out delay-300"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100 scale-100'
                        : 'translate-y-14 opacity-0 scale-[0.96]',
                ]"
            >
                <div>
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Garante o teu lugar
                    </h3>
                    <p class="mt-2 text-xs leading-relaxed text-neutral-400 sm:text-sm">
                        Acesso total às palestras no anfiteatro, sessões de networking com empresas e kit de boas-vindas do evento.
                    </p>
                </div>

                <div class="mt-6">
                    <PrimaryButton
                        v-if="canEnroll"
                        color="pill"
                        full-width
                        padding="px-6 py-2.5"
                        text-size="text-xs sm:text-sm font-semibold"
                        @click="
                            $page.props.auth.user
                                ? router.put(route('enroll'))
                                : router.get(route('register'))
                        "
                    >
                        <Check v-if="$page.props.auth.user" :size="15" />
                        <UserPlus v-else :size="15" />
                        <span>
                            {{
                                $page.props.auth.user
                                    ? "Confirmar Inscrição"
                                    : "Inscrever-me Agora"
                            }}
                        </span>
                    </PrimaryButton>
                </div>
            </Card>

            <Card
                padding="p-6 sm:p-7"
                :interactive="false"
                class="flex flex-col justify-between transition-all duration-700 ease-in-out delay-450"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100 scale-100'
                        : 'translate-y-14 opacity-0 scale-[0.96]',
                ]"
            >
                <div>
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Explora o programa
                    </h3>
                    <p class="mt-2 text-xs leading-relaxed text-neutral-400 sm:text-sm">
                        Consulta os horários das palestras técnicas, temas das intervenções e oradores convidados para cada um dos dias.
                    </p>
                </div>

                <div class="mt-6">
                    <PrimaryButton
                        color="pill"
                        full-width
                        padding="px-6 py-2.5"
                        text-size="text-xs sm:text-sm font-semibold"
                        :href="route('program')"
                    >
                        <Calendar :size="15" />
                        <span>Ver Programa Completo</span>
                    </PrimaryButton>
                </div>
            </Card>

            <Card
                padding="p-6 sm:p-7"
                :interactive="false"
                class="flex flex-col justify-between transition-all duration-700 ease-in-out delay-600"
                :class="[
                    isVisible
                        ? 'translate-y-0 opacity-100 scale-100'
                        : 'translate-y-14 opacity-0 scale-[0.96]',
                ]"
            >
                <div>
                    <h3 class="text-lg font-bold text-white sm:text-xl">
                        Feito por estudantes
                    </h3>
                    <p class="mt-2 text-xs leading-relaxed text-neutral-400 sm:text-sm">
                        Uma iniciativa 100% pensada e executada por alunos da FEUP. Descobre quem está por trás desta edição da SINF.
                    </p>
                </div>

                <div class="mt-6">
                    <PrimaryButton
                        color="pill"
                        full-width
                        padding="px-6 py-2.5"
                        text-size="text-xs sm:text-sm font-semibold"
                        :href="route('team')"
                    >
                        <Users :size="15" />
                        <span>Conhecer a Equipa</span>
                    </PrimaryButton>
                </div>
            </Card>
        </div>
    </div>
</template>
