<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { reactive, ref, watch } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import type { User } from "@/Types/User";
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { type BuyableProduct } from "@/Types/ShopPage";

const modalOpen = ref(false);

interface Props {
    product: BuyableProduct;
    user?: User;
    isEnrolled?: boolean;
    isParticipant?: boolean;
}

const props = defineProps<Props>();

const product = reactive<BuyableProduct>({ ...props.product });

watch(
    () => props.product,
    (newVal) => Object.assign(product, newVal),
);

const buyProduct = () => {
    router.post(route("shop.product.buy", { product: product.id }), undefined, {
        preserveState: true,
        onFinish: () => (modalOpen.value = false),
    });
};
</script>

<template>
    <!-- CARD (glass style + hover) -->
    <div
        class="group relative flex max-w-[20em] flex-col rounded-2xl border-0 bg-white/5 p-10 shadow-[0_0_40px_-12px_rgba(255,255,255,0.18)] ring-1 ring-white/10 backdrop-blur-xs transition-all duration-300 select-none hover:scale-[1.02] hover:shadow-[0_0_40px_-6px_rgba(29,155,240,0.45)] max-lg:p-8"
    >
        <!-- IMAGE -->
        <div
            class="mb-6 flex h-[16em] items-center justify-center overflow-hidden rounded-xl bg-black/10"
        >
            <img
                :src="product.image_product_url"
                alt=""
                class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
            />
        </div>

        <!-- NAME + PRICE + CTA -->
        <div class="flex flex-1 flex-col justify-between gap-4 text-white">
            <h2 class="mb-4 text-start text-xl font-bold">
                {{ product.name }}
            </h2>
            <p>Quantidade: {{ product.stock }}</p>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xl">
                    <p>{{ product.price }}</p>
                    <img
                        class="w-5"
                        title="SINFrão"
                        src="/images/cy-sinf-small.svg"
                    />
                </div>

                <button
                    class="bg-2025-blue hover:text-2025-blue rounded-md px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:bg-white focus:ring-2 focus:ring-white/60 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!product.canBeBought && !product.enrollments"
                    @click="modalOpen = true"
                >
                    Comprar
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <VueFinalModal
        v-model="modalOpen"
        class="flex items-center justify-center overflow-clip"
        content-class="max-w-xl max-h-[80%] mx-4 p-6
                    rounded-xl border-[3px] border-2025-blue
                    bg-2025-blue
                    flex relative justify-center items-center flex-col gap-8"
    >
        <template v-if="isEnrolled">
            <img :src="product.image_product_url" class="w-48 object-contain" />
            <p class="text-text-color">
                Confirmar compra de <b>{{ product.name }}</b> por
                {{ product.price }}?
            </p>
            <PrimaryButton @click="buyProduct">Comprar</PrimaryButton>
        </template>

        <template v-else>
            <p class="text-text-color">
                Antes de comprares este produto, inscreve-te!
            </p>
            <PrimaryButton
                color="orange"
                shadow="teal"
                text-size="text-xl"
                padding="sm:px-8"
                @click="
                    $page.props.auth.user
                        ? router.put(route('enroll'))
                        : router.get(route('register'))
                "
            >
                Inscrever-me
            </PrimaryButton>
        </template>

        <template
            v-if="
                product.enrollments !== null &&
                product.enrollments !== undefined
            "
        >
            <div
                v-if="product.enrollments.length > 0"
                class="border-2025-blue flex max-h-72 w-full flex-col items-center overflow-y-auto rounded-lg border-[3px] bg-white/5 shadow-[0_0_20px_-8px_rgba(255,255,255,0.2)] backdrop-blur-xs"
            >
                <div
                    v-for="enrollment in product.enrollments"
                    :key="enrollment.id"
                    class="even:bg-2025-blue/15 flex w-full items-center justify-between gap-3 p-3"
                >
                    {{ enrollment.participant?.user?.name ?? enrollment.id }} -
                    {{ enrollment.participant?.user?.email ?? enrollment.id }}
                    <Link
                        v-if="!enrollment.pivot.redeemed"
                        :href="
                            route('shop.product.redeem', {
                                product: product.id,
                                enrollment: enrollment.id,
                            })
                        "
                        method="post"
                        class="font-semibold text-white hover:underline"
                    >
                        Entregar
                    </Link>
                </div>
            </div>

            <div v-else class="flex h-full flex-1 items-center justify-center">
                <p class="text-2025-blue text-center text-2xl font-bold">
                    Ainda nenhum participante comprou este produto.
                </p>
            </div>
        </template>
    </VueFinalModal>
</template>
