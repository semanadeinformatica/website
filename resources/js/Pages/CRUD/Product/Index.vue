<script setup lang="ts">
import type Product from "@/Types/Product";
import type Paginated from "@/Types/Paginated";
import CRUDView from "@/Components/CRUD/CRUDView.vue";
import HeaderRow from "@/Components/CRUD/HeaderRow.vue";
import Row from "@/Components/CRUD/Row.vue";
import Cell from "@/Components/CRUD/Cell.vue";
import Header from "@/Components/CRUD/Header.vue";
import PrimaryButton from "@/Components/UI/PrimaryButton.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Power, Receipt } from "@lucide/vue";
import type Edition from "@/Types/Edition";

interface Props {
    items: Paginated<Product>;
    with: {
        editions: Edition[];
    };
    isSearchable?: boolean;
    shopEnabled?: boolean;
    shopEditionName?: string | null;
    shopEditionId?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
    isSearchable: false,
    shopEnabled: true,
    shopEditionName: null,
    shopEditionId: null,
});

const editions = computed<Record<number, string>>(() =>
    Object.fromEntries(
        props.with.editions.map((edition) => [edition.id, edition.name]),
    ),
);

const isTogglingShop = ref(false);

const toggleShop = () => {
    if (isTogglingShop.value) return;

    isTogglingShop.value = true;

    router.post(
        route("admin.shop.toggle"),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isTogglingShop.value = false;
            },
        },
    );
};
</script>

<template>
    <CRUDView
        title="Produtos"
        view="Product"
        :items="items"
        name="products"
        :is-searchable="isSearchable"
    >
        <template #heading>Produtos da Loja</template>

        <template #top>
            <section
                class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-black/40 p-5 shadow-[0_4px_24px_rgba(0,0,0,0.3)] backdrop-blur-md lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border"
                        :class="
                            shopEnabled
                                ? 'border-emerald-500/25 bg-emerald-500/10 text-emerald-400'
                                : 'border-amber-500/25 bg-amber-500/10 text-amber-400'
                        "
                    >
                        <Power :size="17" />
                    </div>

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-semibold text-white">
                                Disponibilidade da loja
                            </h2>
                            <span
                                class="rounded-full border px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase"
                                :class="
                                    shopEnabled
                                        ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                                        : 'border-amber-500/30 bg-amber-500/10 text-amber-300'
                                "
                            >
                                {{ shopEnabled ? "Ativa" : "Desativada" }}
                            </span>
                        </div>

                        <p class="max-w-2xl text-xs text-neutral-400">
                            Loja da edição
                            <span class="font-medium text-neutral-200">
                                {{ shopEditionName ?? "atual" }} </span
                            >. Quando desativada, os participantes não conseguem
                            ver a loja nem efetuar compras. Staff e
                            administração continuam a poder geri-la.
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center"
                >
                    <PrimaryButton
                        :href="
                            shopEditionId
                                ? route('admin.shop.purchases', {
                                      edition_id: shopEditionId,
                                  })
                                : route('admin.shop.purchases')
                        "
                        color="pill"
                        padding="px-3.5 py-1.5"
                        text-size="text-xs"
                        class="flex shrink-0 items-center gap-1.5"
                    >
                        <Receipt :size="14" />
                        <span>Gerir compras</span>
                    </PrimaryButton>

                    <button
                        type="button"
                        role="switch"
                        :aria-checked="!shopEnabled"
                        :disabled="isTogglingShop"
                        class="pill-container inline-flex h-9 items-center justify-between gap-3 px-3 text-xs font-medium transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="toggleShop"
                    >
                        <span
                            :class="
                                !shopEnabled
                                    ? 'text-amber-300'
                                    : 'text-neutral-300'
                            "
                        >
                            {{
                                isTogglingShop
                                    ? "A atualizar..."
                                    : "Desativar loja"
                            }}
                        </span>
                        <span
                            class="relative flex h-5 w-9 shrink-0 items-center rounded-full transition-colors duration-200"
                            :class="
                                !shopEnabled ? 'bg-amber-500' : 'bg-white/15'
                            "
                        >
                            <span
                                class="absolute left-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                                :class="
                                    !shopEnabled
                                        ? 'translate-x-4'
                                        : 'translate-x-0'
                                "
                            />
                        </span>
                    </button>
                </div>
            </section>
        </template>

        <template #header>
            <HeaderRow>
                <Header sort-by="name">Nome</Header>
                <Header sort-by="price">Preço</Header>
                <Header sort-by="stock">Stock Disponível</Header>
                <Header filter-by="edition_id" :filter-values="editions">
                    Edição
                </Header>
            </HeaderRow>
        </template>

        <template #row="{ item }">
            <Row :item="item" name="products">
                <Cell class="font-medium text-white">{{ item.name }}</Cell>
                <Cell>
                    <span
                        class="font-mono text-xs font-semibold text-neutral-200"
                    >
                        {{ item.price }}
                    </span>
                    <span class="ml-1 text-xs text-neutral-500">pts</span>
                </Cell>
                <Cell>
                    <span
                        v-if="item.stock === 0"
                        class="text-xs font-medium text-red-400"
                    >
                        Esgotado (0 un.)
                    </span>
                    <span v-else class="font-mono text-xs text-neutral-300">
                        {{ item.stock }}
                        <span class="font-sans text-neutral-500">un.</span>
                    </span>
                </Cell>
                <Cell class="text-xs text-neutral-400">
                    {{ editions[item.edition_id] ?? "-" }}
                </Cell>
            </Row>
        </template>
    </CRUDView>
</template>
