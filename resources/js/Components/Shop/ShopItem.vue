<script setup lang="ts">
import { ref, computed, watch } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import type { BuyableProduct } from "@/Types/ShopPage";
import type { User } from "@/Types/User";
import Card from "@/Components/UI/Card.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ShoppingBag, Check, Search, Package, Sparkles } from "@lucide/vue";

interface Props {
    product: BuyableProduct;
    points?: number;
    isEnrolled?: boolean;
    isParticipant?: boolean;
}

const props = defineProps<Props>();
const page = usePage();
const user = computed(() => page.props.auth?.user as User | undefined);

const modalOpen = ref(false);
const isBuying = ref(false);
const isRedeemingId = ref<number | null>(null);
const deliverySearch = ref("");

const product = computed(() => props.product);
const isOutOfStock = computed(() => (product.value.stock ?? 0) <= 0);
const isAlreadyBought = computed(() => Boolean(product.value.alreadyBought));
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
    { id: "buy", label: "Comprar Artigo" },
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
    if (
        !user.value ||
        !hasEnoughPoints.value ||
        isBuying.value ||
        isOutOfStock.value ||
        isAlreadyBought.value
    ) {
        return;
    }

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
                <span
                    v-if="isAlreadyBought"
                    class="font-medium text-emerald-400"
                >
                    Artigo já adquirido por ti
                </span>
                <span v-else-if="!isOutOfStock">
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

            <PrimaryButton
                v-if="user"
                padding="px-3.5 py-1"
                text-size="text-xs font-semibold"
                :disabled="isOutOfStock && !hasEnrollments && !isAlreadyBought"
                @click="modalOpen = true"
            >
                <span v-if="isAlreadyBought">Adquirido</span>
                <span v-else-if="hasEnrollments && isOutOfStock">Gerir</span>
                <span v-else-if="isOutOfStock">Esgotado</span>
                <span v-else>Comprar</span>
            </PrimaryButton>
        </template>
    </Card>

    <Modal
        v-model="modalOpen"
        :max-width="activeTab === 'deliveries' ? 'xl' : 'lg'"
    >
        <!-- Modal Custom Header -->
        <template #header>
            <div class="space-y-4 p-6 pb-2 sm:p-7 sm:pb-2">
                <!-- Delivery / Buy Tabs for Staff / Admin -->
                <div v-if="hasEnrollments" class="flex justify-center">
                    <PillSelector
                        v-model="activeTab"
                        :items="shopTabs"
                        size="sm"
                    />
                </div>

                <!-- Product Card Summary inside Modal -->
                <div
                    class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/4 p-3.5 sm:p-4"
                >
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-neutral-900/90 p-2 ring-1 ring-white/10 sm:h-20 sm:w-20"
                    >
                        <img
                            v-if="product.image_product_url"
                            :src="product.image_product_url"
                            :alt="product.name"
                            class="max-h-full max-w-full object-contain"
                        />
                        <ShoppingBag
                            v-else
                            class="h-8 w-8 text-neutral-600 opacity-40"
                        />
                    </div>

                    <div class="min-w-0 flex-1 space-y-1.5">
                        <h3
                            class="truncate text-base font-bold tracking-tight text-white sm:text-lg"
                        >
                            {{ product.name }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-2">
                            <div
                                class="pill-container gap-1 px-2.5 py-0.5 text-xs text-white shadow-none"
                            >
                                <span class="font-bold">{{
                                    product.price
                                }}</span>
                                <img
                                    class="h-3.5 w-3.5 object-contain"
                                    alt="SINFrão"
                                    src="/images/sinf-2026-sm.svg"
                                />
                            </div>

                            <span
                                v-if="isAlreadyBought"
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-400 ring-1 ring-emerald-500/25"
                            >
                                <Check :size="12" />
                                Já Adquirido
                            </span>
                            <span
                                v-else-if="isOutOfStock"
                                class="rounded-full bg-red-500/15 px-2.5 py-0.5 text-[11px] font-semibold text-red-400 ring-1 ring-red-500/25"
                            >
                                Esgotado
                            </span>
                            <span
                                v-else
                                class="rounded-full bg-white/5 px-2.5 py-0.5 text-xs text-neutral-400 ring-1 ring-white/10"
                            >
                                {{ product.stock }} disponíveis
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal Body Content -->
        <div v-if="activeTab === 'buy'" class="w-full space-y-4">
            <!-- Already bought state -->
            <div
                v-if="isAlreadyBought"
                class="space-y-2 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-center"
            >
                <div
                    class="flex items-center justify-center gap-2 text-emerald-400"
                >
                    <Check :size="18" />
                    <span class="text-sm font-semibold"
                        >Artigo já adquirido!</span
                    >
                </div>
                <p class="text-xs leading-relaxed text-neutral-300">
                    Já compraste este produto nesta edição da SINF 2026. Podes
                    levantá-lo presencialmente no secretariado ou banca da
                    organização durante o evento.
                </p>
            </div>

            <!-- Out of stock state -->
            <div
                v-else-if="isOutOfStock"
                class="space-y-2 rounded-2xl border border-white/10 bg-white/5 p-4 text-center"
            >
                <p class="text-sm font-medium text-neutral-300">
                    Este produto está temporariamente esgotado.
                </p>
                <p class="text-xs text-neutral-500">
                    Fica atento a eventuais reposições de stock durante o
                    evento.
                </p>
            </div>

            <!-- Enrolled participant flow -->
            <template v-else-if="isEnrolled">
                <div
                    v-if="points !== null && points !== undefined"
                    class="space-y-2.5 rounded-2xl border border-white/10 bg-white/5 p-4 text-xs"
                >
                    <div
                        class="flex items-center justify-between text-neutral-400"
                    >
                        <span>O teu saldo atual</span>
                        <div
                            class="flex items-center gap-1 font-mono font-bold text-white"
                        >
                            <span>{{ points }}</span>
                            <img
                                class="h-3 w-3 object-contain"
                                alt="SINFrão"
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
                                src="/images/sinf-2026-sm.svg"
                            />
                        </div>
                    </div>
                </div>

                <!-- Insufficient points alert -->
                <div
                    v-if="
                        !hasEnoughPoints &&
                        points !== undefined &&
                        points !== null
                    "
                    class="space-y-1.5 rounded-2xl border border-red-500/20 bg-red-500/10 p-3.5 text-center text-xs text-red-300"
                >
                    <p>
                        Precisas de mais
                        <span class="font-bold text-white">
                            {{ product.price - points }} SINFrões
                        </span>
                        para efetuar esta compra.
                    </p>
                    <p class="text-[11px] text-red-300/80">
                        Participa nas palestras, visita as bancas e conclui
                        quests para acumular mais pontos!
                    </p>
                </div>
            </template>

            <!-- Not enrolled participant / Guest flow -->
            <template v-else>
                <div
                    class="flex flex-col items-center justify-center space-y-4 rounded-2xl border border-white/10 bg-white/5 p-6 text-center"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/6 text-neutral-300 ring-1 ring-white/10"
                    >
                        <Sparkles :size="24" />
                    </div>

                    <div class="space-y-1">
                        <h4 class="text-sm font-semibold text-white">
                            Inscrição Necessária
                        </h4>
                        <p class="text-xs leading-relaxed text-neutral-400">
                            Para comprares produtos na loja oficial com
                            SINFrões, precisas de te inscrever na SINF 2026.
                        </p>
                    </div>

                    <PrimaryButton
                        padding="px-6 py-2"
                        text-size="text-xs"
                        @click="
                            $page.props.auth?.user
                                ? router.put(route('enroll'))
                                : router.get(route('register'))
                        "
                    >
                        Inscrever-me na SINF
                    </PrimaryButton>
                </div>
            </template>
        </div>

        <!-- Deliveries Tab for Staff / Admin -->
        <div
            v-else-if="activeTab === 'deliveries'"
            class="flex min-h-0 w-full flex-1 flex-col space-y-3.5"
        >
            <div
                class="flex items-center justify-between rounded-xl border border-white/6 bg-white/3 px-3 py-2 text-xs text-neutral-400"
            >
                <span class="font-medium text-white">
                    {{ totalDeliveriesCount }}
                    {{
                        totalDeliveriesCount === 1
                            ? "compra registada"
                            : "compras registadas"
                    }}
                </span>
                <span
                    class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                    :class="
                        pendingDeliveriesCount > 0
                            ? 'bg-amber-500/15 text-amber-400 ring-1 ring-amber-500/25'
                            : 'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/25'
                    "
                >
                    {{ pendingDeliveriesCount }} por entregar
                </span>
            </div>

            <!-- Search input -->
            <div class="relative">
                <Search
                    :size="14"
                    class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-neutral-500"
                />
                <input
                    v-model="deliverySearch"
                    type="text"
                    placeholder="Pesquisar participante por nome ou email..."
                    class="w-full rounded-2xl border border-white/10 bg-white/5 py-2 pr-3.5 pl-9 text-xs text-white placeholder-neutral-500 transition-colors focus:border-white/20 focus:ring-1 focus:ring-white/20 focus:outline-hidden"
                />
            </div>

            <!-- Enrollments list -->
            <div
                v-if="filteredEnrollments.length > 0"
                class="max-h-64 space-y-2 overflow-y-auto pr-1"
            >
                <div
                    v-for="enrollment in filteredEnrollments"
                    :key="enrollment.id"
                    class="flex items-center justify-between gap-3 rounded-2xl border border-white/8 bg-white/4 p-3 transition-colors hover:border-white/15"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/8 text-xs font-bold text-neutral-300 ring-1 ring-white/10"
                        >
                            {{
                                (
                                    enrollment.participant?.user?.name ?? "P"
                                ).charAt(0)
                            }}
                        </div>
                        <div class="flex min-w-0 flex-col">
                            <span
                                class="truncate text-xs font-semibold text-white"
                            >
                                {{
                                    enrollment.participant?.user?.name ??
                                    `Inscrição #${enrollment.id}`
                                }}
                            </span>
                            <span class="truncate text-[11px] text-neutral-400">
                                {{ enrollment.participant?.user?.email }}
                            </span>
                        </div>
                    </div>

                    <span
                        v-if="enrollment.pivot?.redeemed"
                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[11px] font-semibold text-emerald-400 ring-1 ring-emerald-500/25"
                    >
                        <Check :size="12" />
                        Entregue
                    </span>
                    <PrimaryButton
                        v-else
                        padding="px-3.5 py-1"
                        text-size="text-[11px] font-semibold"
                        :disabled="isRedeemingId === enrollment.id"
                        @click="redeemProduct(enrollment.id)"
                    >
                        <span v-if="isRedeemingId === enrollment.id"
                            >A registar...</span
                        >
                        <span v-else>Entregar Artigo</span>
                    </PrimaryButton>
                </div>
            </div>

            <!-- Empty state for search -->
            <div
                v-else
                class="flex flex-col items-center justify-center rounded-2xl border border-white/6 bg-white/2 py-8 text-center"
            >
                <Package :size="28" class="mb-2 text-neutral-600 opacity-50" />
                <p class="text-xs text-neutral-400">
                    {{
                        deliverySearch
                            ? "Nenhum participante encontrado com esse termo."
                            : "Ainda nenhum participante adquiriu este artigo."
                    }}
                </p>
            </div>
        </div>

        <!-- Modal Footer -->
        <template #footer>
            <div class="flex w-full items-center justify-end gap-3">
                <PrimaryButton
                    padding="px-4 py-1.5"
                    text-size="text-xs"
                    @click="modalOpen = false"
                >
                    {{ activeTab === "deliveries" ? "Fechar" : "Cancelar" }}
                </PrimaryButton>

                <template v-if="activeTab === 'buy' && user && isEnrolled">
                    <PrimaryButton
                        v-if="!isAlreadyBought && !isOutOfStock"
                        padding="px-5 py-1.5"
                        text-size="text-xs"
                        :disabled="!hasEnoughPoints || isBuying"
                        @click="buyProduct"
                    >
                        <span v-if="isBuying">A processar...</span>
                        <span v-else>Confirmar Compra</span>
                    </PrimaryButton>
                </template>
            </div>
        </template>
    </Modal>
</template>
