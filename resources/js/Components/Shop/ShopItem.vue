<script setup lang="ts">
import { ref, computed, watch } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type { BuyableProduct } from "@/Types/ShopPage";
import Card from "@/Components/UI/Card.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { ShoppingBag } from "@lucide/vue";

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

const shopTabs = computed<PillOption[]>(() => [
    { id: "buy", label: "Comprar" },
    {
        id: "deliveries",
        label: "Entregas",
        count: pendingDeliveriesCount.value,
    },
]);

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
    <Card
        :image-src="product.image_product_url ?? undefined"
        :image-alt="product.name"
        image-fit="object-contain p-4"
        class="h-full w-full"
    >
        <template v-if="!product.image_product_url" #image>
            <div
                class="flex h-full w-full items-center justify-center text-neutral-600"
            >
                <ShoppingBag :size="36" class="opacity-30" />
            </div>
        </template>

        <template #header>
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
        </template>

        <template #footer>
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
        </template>
    </Card>

    <Modal
        v-model="modalOpen"
        max-width="lg"
    >

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
            <PillSelector
                v-model="activeTab"
                :items="shopTabs"
                size="sm"
                container-class="w-full sm:w-auto"
            />
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
                        <span>Saldo atual</span>
                        <div
                            class="flex items-center gap-1 font-mono font-bold text-white"
                        >
                            <span>{{ points }}</span>
                            <img
                                class="h-3 w-3 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between text-neutral-400"
                    >
                        <span>Custo do artigo</span>
                        <div
                            class="flex items-center gap-1 font-mono font-bold text-white"
                        >
                            <span>-{{ product.price }}</span>
                            <img
                                class="h-3 w-3 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-white/10 pt-2 font-medium"
                    >
                        <span class="text-neutral-300">Saldo após compra</span>
                        <div
                            class="flex items-center gap-1 font-mono font-bold"
                            :class="
                                hasEnoughPoints
                                    ? 'text-emerald-400'
                                    : 'text-red-400'
                            "
                        >
                            <span>{{ points - product.price }}</span>
                            <img
                                class="h-3 w-3 object-contain"
                                alt="SINFrão"
                                title="SINFrão"
                                src="/images/sinf-2026-sm.svg"
                            />
                        </div>
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
                        class="pill-container pill-item bg-sinf-primary/80 hover:bg-sinf-primary cursor-pointer px-6 py-2 text-xs font-semibold text-white transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
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
                        class="pill-container pill-item bg-sinf-secondary/80 hover:bg-sinf-secondary cursor-pointer px-6 py-2.5 text-xs font-semibold text-white transition-all active:scale-95"
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
    </Modal>
</template>
