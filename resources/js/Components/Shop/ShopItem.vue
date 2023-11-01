<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { computed, ref } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import type { User } from "@/Types/User";
import { Link, router } from "@inertiajs/vue3";
import route from "ziggy-js";
import { type BuyableProduct } from "@/Types/ShopPage";

const modalOpen = ref(false);

interface Props {
    product: BuyableProduct;
    user?: User;
    isEnrolled?: boolean;
    isParticipant?: boolean;
}

const props = defineProps<Props>();

const product = computed(() => props.product);

const buyProduct = () => {
    router.post(
        route("shop.product.buy", { product: product.value }),
        undefined,
        {
            preserveState: true,
            onFinish: () => (modalOpen.value = false),
        },
    );
};
</script>

<template>
    <div class="flex max-w-[20em] flex-col border-2 border-black">
        <div
            class="flex h-[20em] max-w-[20em] items-center border-b-2 border-black p-4"
        >
            <img :src="product.image_product_url" />
        </div>
        <button
            class="flex flex-1 flex-col justify-between bg-2023-orange px-4 py-2 text-white disabled:opacity-50"
            :disabled="!product.canBeBought && !product.enrollments"
            @click="modalOpen = true"
        >
            <h2 class="text-start text-xl font-bold">{{ product.name }}</h2>
            <div class="flex flex-row gap-2 self-end text-xl">
                <p>{{ product.price }}</p>
                <img
                    class="w-5"
                    title="SINFrão"
                    src="/images/cy-sinf-small.svg"
                />
            </div>
        </button>
    </div>
    <VueFinalModal
        v-model="modalOpen"
        class="flex items-center justify-center overflow-clip"
        content-class="max-w-xl max-h-[80%] mx-4 p-4 bg-2023-bg border border-black border-solid flex relative justify-center items-center flex-col gap-8"
    >
        <template v-if="isEnrolled">
            <img
                :src="product.image_product_url"
                class="object-fit w-48 justify-center"
            />
            <p class="text-2023-teal-dark">
                Confirmar compra de <b>{{ product.name }}</b> por
                {{ product.price }}?
            </p>
            <PrimaryButton @click="buyProduct">Comprar</PrimaryButton>
        </template>
        <template v-else>
            <p class="text-2023-teal-dark">
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
                >Inscrever-me</PrimaryButton
            >
        </template>

        <template
            v-if="
                product.enrollments !== null &&
                product.enrollments !== undefined
            "
        >
            <div
                v-if="product.enrollments.length > 0"
                class="flex flex-col items-center overflow-y-auto border border-black bg-2023-bg shadow-lg shadow-2023-teal"
            >
                <div
                    v-for="enrollment in product.enrollments"
                    :key="enrollment.id"
                    class="flex w-full items-center justify-between gap-3 p-3 even:bg-2023-orange even:bg-opacity-20"
                >
                    {{ enrollment.participant?.user?.name ?? enrollment.id }} -
                    {{ enrollment.participant?.user?.email ?? enrollment.id }}
                    <Link
                        v-if="!enrollment.pivot.redeemed"
                        :href="
                            route('shop.product.redeem', {
                                product,
                                enrollment,
                            })
                        "
                        method="post"
                        >Entregar</Link
                    >
                </div>
            </div>
            <div v-else class="flex h-full flex-1 items-center justify-center">
                <p class="text-center text-2xl font-bold text-2023-teal">
                    Ainda nenhum participante comprou este produto.
                </p>
            </div>
        </template>
    </VueFinalModal>
</template>
