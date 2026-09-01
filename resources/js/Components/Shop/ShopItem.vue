<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type { BuyableProduct } from "@/Types/ShopPage";

interface Props {
    product: BuyableProduct;
    points?: number;
    isEnrolled?: boolean;
    isParticipant?: boolean;
}

const props = defineProps<Props>();
const modalOpen = ref(false);
const isBuying = ref(false);
const isRedeemingId = ref<number | null>(null);
const deliverySearch = ref("");

const product = computed(() => props.product);
const isOutOfStock = computed(() => (product.value.stock ?? 0) <= 0);
const hasEnrollments = computed(
    () =>
        product.value.enrollments !== null &&
        product.value.enrollments !== undefined,
);

const pendingDeliveriesCount = computed(() => {
    if (!product.value.enrollments) return 0;
    return product.value.enrollments.filter((e) => !e.pivot?.redeemed).length;
});

const totalDeliveriesCount = computed(() => {
    return product.value.enrollments?.length ?? 0;
});

const filteredEnrollments = computed(() => {
    const list = product.value.enrollments ?? [];
    const q = deliverySearch.value.trim().toLowerCase();
    if (!q) return list;
    return list.filter((e) => {
        const name = e.participant?.user?.name?.toLowerCase() ?? "";
        const email = e.participant?.user?.email?.toLowerCase() ?? "";
        return name.includes(q) || email.includes(q);
    });
});

const hasEnoughPoints = computed(() => {
    if (props.points === undefined || props.points === null) return true;
    return props.points >= product.value.price;
});

type ModalTab = "buy" | "deliveries";
const activeTab = ref<ModalTab>("buy");

watch(modalOpen, (isOpen) => {
    if (isOpen) {
        deliverySearch.value = "";
        activeTab.value =
            isOutOfStock.value && hasEnrollments.value ? "deliveries" : "buy";
    }
});

const buyProduct = () => {
    isBuying.value = true;
    router.post(
        route("shop.product.buy", { product: product.value.id }),
        undefined,
        {
            preserveState: true,
            onFinish: () => {
                isBuying.value = false;
                modalOpen.value = false;
            },
        },
    );
};

const redeemProduct = (enrollmentId: number) => {
    isRedeemingId.value = enrollmentId;
    router.post(
        route("shop.product.redeem", {
            product: product.value.id,
            enrollment: enrollmentId,
        }),
        undefined,
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isRedeemingId.value = null;
            },
        },
    );
};
</script>

<template>
    <div
        class="group relative flex h-full w-full flex-col justify-between rounded-3xl border border-white/8 bg-black/50 p-4 text-left shadow-[0_2px_10px_rgba(0,0,0,0.08),inset_0_1px_1px_rgba(255,255,255,0.05)] backdrop-blur-md transition-all duration-300 ease-out select-none hover:scale-[1.015] hover:border-white/15 hover:shadow-[0_6px_20px_rgba(0,0,0,0.18),inset_0_1px_1px_rgba(255,255,255,0.08)] sm:p-5"
    >
        <div
            class="relative aspect-square w-full overflow-hidden rounded-2xl bg-neutral-900 ring-1 ring-white/10"
        >
            <img
                v-if="product.image_product_url"
                :src="product.image_product_url"
                :alt="product.name"
                class="h-full w-full object-contain p-4 transition-transform duration-500 ease-out group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-neutral-600"
            >
                <svg
                    class="h-10 w-10 opacity-30"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                    />
                </svg>
            </div>

            <div
                class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            />
        </div>

        <div class="mt-4 flex flex-1 flex-col justify-between">
            <div>
                <h4
                    class="truncate text-base font-bold tracking-tight text-white transition-colors group-hover:text-neutral-200"
                >
                    {{ product.name }}
                </h4>
                <p
                    class="mt-1 line-clamp-2 min-h-9 text-xs leading-relaxed font-light text-neutral-400"
                >
                    <span v-if="!isOutOfStock">
                        {{ product.stock }}
                        {{
                            product.stock === 1
                                ? "unidade disponível"
                                : "unidades disponíveis"
                        }}
                    </span>
                    <span v-else class="text-neutral-500">
                        Item atualmente fora de stock
                    </span>
                </p>
            </div>

            <div
                class="mt-4 flex min-h-9 items-center justify-between border-t border-white/5 pt-3"
            >
                <div
                    class="flex items-center gap-1.5 font-mono text-sm font-bold text-white"
                >
                    <span>{{ product.price }}</span>
                    <img
                        class="h-3.5 w-3.5 object-contain"
                        alt="SINFrão"
                        title="SINFrão"
                        src="/images/sinf-2026-sm.svg"
                    />
                </div>

                <button
                    type="button"
                    class="pill-container pill-item cursor-pointer gap-1 px-3.5 py-1 text-xs font-semibold transition-all"
                    :class="[
                        isOutOfStock && !hasEnrollments
                            ? 'cursor-not-allowed text-neutral-500 opacity-40 hover:bg-transparent'
                            : 'text-white hover:bg-white/15 active:scale-95',
                    ]"
                    :disabled="isOutOfStock && !hasEnrollments"
                    @click="modalOpen = true"
                >
                    <span v-if="hasEnrollments && isOutOfStock">Gerir</span>
                    <span v-else-if="isOutOfStock">Esgotado</span>
                    <span v-else>Comprar</span>
                </button>
            </div>
        </div>
    </div>

    <VueFinalModal
        v-model="modalOpen"
        class="z-50 flex items-center justify-center p-4"
        overlay-class="bg-black/80 backdrop-blur-sm"
        content-class="
            relative w-full max-w-lg rounded-3xl border border-white/10
            bg-[#111215] p-6 sm:p-7 text-white
            flex flex-col gap-6 max-h-[90vh] overflow-hidden
        "
    >
        <button
            type="button"
            class="pill-container absolute top-4 right-4 z-10 h-8 w-8 cursor-pointer justify-center text-neutral-400 transition-colors hover:text-white"
            aria-label="Fechar"
            @click="modalOpen = false"
        >
            <svg
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>

        <div
            class="flex items-center gap-4 rounded-2xl border border-white/8 bg-white/5 p-3.5 pr-10 sm:p-4"
        >
            <div
                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-neutral-900/80 p-2 ring-1 ring-white/10 sm:h-20 sm:w-20"
            >
                <img
                    v-if="product.image_product_url"
                    :src="product.image_product_url"
                    :alt="product.name"
                    class="max-h-full max-w-full object-contain"
                />
                <svg
                    v-else
                    class="h-7 w-7 text-neutral-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                    />
                </svg>
            </div>

            <div class="min-w-0 flex-1 space-y-1.5">
                <h3 class="truncate text-base font-bold text-white sm:text-lg">
                    {{ product.name }}
                </h3>
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="pill-container gap-1 px-2.5 py-0.5 text-xs text-white shadow-none"
                    >
                        <span class="font-bold">{{ product.price }}</span>
                        <img
                            class="h-3.5 w-3.5 object-contain"
                            alt="SINFrão"
                            src="/images/sinf-2026-sm.svg"
                        />
                    </div>
                    <span
                        v-if="isOutOfStock"
                        class="rounded-full bg-red-500/15 px-2.5 py-0.5 text-[11px] text-red-400 ring-1 ring-red-500/20"
                    >
                        Esgotado
                    </span>
                    <span v-else class="text-xs text-neutral-400">
                        {{ product.stock }} disponíveis
                    </span>
                </div>
            </div>
        </div>

        <div v-if="hasEnrollments" class="-my-2 flex justify-center">
            <div
                class="pill-container w-full justify-center gap-1 p-1 sm:w-auto"
            >
                <button
                    type="button"
                    class="pill-item flex-1 cursor-pointer justify-center gap-1.5 text-xs font-medium sm:flex-initial sm:text-sm"
                    :class="{ 'pill-item-active': activeTab === 'buy' }"
                    @click="activeTab = 'buy'"
                >
                    <span>Comprar</span>
                </button>
                <button
                    type="button"
                    class="pill-item flex-1 cursor-pointer justify-center gap-2 text-xs font-medium sm:flex-initial sm:text-sm"
                    :class="{ 'pill-item-active': activeTab === 'deliveries' }"
                    @click="activeTab = 'deliveries'"
                >
                    <span>Entregas</span>
                    <span
                        class="py-0.2 rounded-full px-1.5 text-[10px]"
                        :class="
                            pendingDeliveriesCount > 0
                                ? 'bg-amber-500/25 text-amber-300 ring-1 ring-amber-500/30'
                                : 'bg-white/10 text-neutral-400'
                        "
                    >
                        {{ pendingDeliveriesCount }}
                    </span>
                </button>
            </div>
        </div>

        <div v-if="activeTab === 'buy'" class="w-full space-y-4">
            <div
                v-if="isOutOfStock"
                class="space-y-2 rounded-2xl border border-white/8 bg-white/5 p-4 text-center"
            >
                <p class="text-sm font-medium text-neutral-300">
                    Este produto está temporariamente esgotado.
                </p>
                <p class="text-xs text-neutral-500">
                    Fica atento a eventuais reposições de stock durante o
                    evento.
                </p>
            </div>

            <template v-else-if="isEnrolled">
                <div
                    v-if="points !== null && points !== undefined"
                    class="space-y-2.5 rounded-2xl border border-white/8 bg-white/5 p-4 text-xs"
                >
                    <div
                        class="flex items-center justify-between text-neutral-400"
                    >
                        <span>O teu saldo atual:</span>
                        <span class="font-bold text-white"
                            >{{ points }} SINFrões</span
                        >
                    </div>
                    <div
                        class="flex items-center justify-between text-neutral-400"
                    >
                        <span>Preço do produto:</span>
                        <span class="font-bold text-red-400"
                            >-{{ product.price }} SINFrões</span
                        >
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-white/8 pt-2 font-bold"
                        :class="
                            hasEnoughPoints
                                ? 'text-emerald-400'
                                : 'text-red-400'
                        "
                    >
                        <span>Saldo após compra:</span>
                        <span>{{ points - product.price }} SINFrões</span>
                    </div>
                </div>

                <div
                    v-if="
                        !hasEnoughPoints &&
                        points !== undefined &&
                        points !== null
                    "
                    class="rounded-2xl border border-red-500/20 bg-red-500/10 p-3.5 text-center text-xs text-red-300"
                >
                    Precisas de mais
                    <span class="font-bold"
                        >{{ product.price - points }} SINFrões</span
                    >
                    para efetuar esta compra.
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="pill-container pill-item cursor-pointer px-5 py-2 text-xs font-medium text-neutral-400 hover:text-white"
                        @click="modalOpen = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="pill-container pill-item bg-sinf-crimson/80 hover:bg-sinf-crimson cursor-pointer px-6 py-2 text-xs font-semibold text-white transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!hasEnoughPoints || isBuying"
                        @click="buyProduct"
                    >
                        <span v-if="isBuying">A processar...</span>
                        <span v-else>Confirmar Compra</span>
                    </button>
                </div>
            </template>

            <template v-else>
                <div
                    class="flex flex-col items-center justify-center space-y-4 rounded-2xl border border-white/8 bg-white/5 p-5 text-center"
                >
                    <p class="text-sm leading-relaxed text-neutral-300">
                        Para comprares produtos na loja oficial, precisas de te
                        inscrever na SINF 2026.
                    </p>
                    <button
                        type="button"
                        class="pill-container pill-item bg-sinf-purple/80 hover:bg-sinf-purple cursor-pointer px-6 py-2.5 text-xs font-semibold text-white transition-all active:scale-95"
                        @click="
                            $page.props.auth.user
                                ? router.put(route('enroll'))
                                : router.get(route('register'))
                        "
                    >
                        Inscrever-me
                    </button>
                </div>
            </template>
        </div>

        <div
            v-else-if="activeTab === 'deliveries'"
            class="flex min-h-0 w-full flex-1 flex-col space-y-3"
        >
            <div
                class="flex items-center justify-between text-xs text-neutral-400"
            >
                <span>{{ totalDeliveriesCount }} compras registadas</span>
                <span
                    :class="
                        pendingDeliveriesCount > 0
                            ? 'font-semibold text-amber-400'
                            : 'text-emerald-400'
                    "
                >
                    {{ pendingDeliveriesCount }} por entregar
                </span>
            </div>

            <div v-if="totalDeliveriesCount > 3" class="relative">
                <input
                    v-model="deliverySearch"
                    type="text"
                    placeholder="Pesquisar participante ou email..."
                    class="w-full rounded-2xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs text-white placeholder-neutral-500 focus:border-white/20 focus:ring-1 focus:ring-white/20 focus:outline-hidden"
                />
            </div>

            <div
                v-if="filteredEnrollments.length > 0"
                class="max-h-64 space-y-2 overflow-y-auto pr-1"
            >
                <div
                    v-for="enrollment in filteredEnrollments"
                    :key="enrollment.id"
                    class="flex items-center justify-between gap-3 rounded-2xl border border-white/8 bg-white/5 p-3 text-xs"
                >
                    <div class="flex min-w-0 flex-col">
                        <span class="truncate font-semibold text-white">
                            {{
                                enrollment.participant?.user?.name ??
                                `Inscrição #${enrollment.id}`
                            }}
                        </span>
                        <span class="truncate text-[11px] text-neutral-400">
                            {{ enrollment.participant?.user?.email }}
                        </span>
                    </div>

                    <span
                        v-if="enrollment.pivot?.redeemed"
                        class="shrink-0 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[10px] text-emerald-400 ring-1 ring-emerald-500/25"
                    >
                        Entregue
                    </span>
                    <button
                        v-else
                        type="button"
                        class="pill-container pill-item shrink-0 cursor-pointer bg-white/10 px-3 py-1 text-[11px] font-semibold text-white transition-colors hover:bg-white/20 active:scale-95 disabled:opacity-50"
                        :disabled="isRedeemingId === enrollment.id"
                        @click="redeemProduct(enrollment.id)"
                    >
                        <span v-if="isRedeemingId === enrollment.id">...</span>
                        <span v-else>Entregar</span>
                    </button>
                </div>
            </div>

            <div
                v-else
                class="rounded-2xl border border-white/5 bg-white/2 py-6 text-center text-xs text-neutral-500"
            >
                {{
                    deliverySearch
                        ? "Nenhum participante encontrado com esse termo."
                        : "Ainda nenhum participante comprou este produto."
                }}
            </div>
        </div>
    </VueFinalModal>
</template>
