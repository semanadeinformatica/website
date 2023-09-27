<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ShopItem from "@/Components/Shop/ShopItem.vue";
</script>

<template>
    <AppLayout title="Loja">
        <div
            class="flex content-center items-center justify-center self-center py-10"
        >
            <h1
                class="w-fit border border-black bg-2023-red p-2 px-5 text-2xl font-bold text-white shadow-md shadow-2023-teal"
            >
                Loja
            </h1>
        </div>
        <section
            class="align-center grid justify-center gap-10 px-32"
            style="grid-template-columns: repeat(auto-fill, minmax(18em, 20em))"
        >
            <ShopItem
                v-for="product in $page.props.products"
                :key="product"
                :product="product"
            >
            </ShopItem>
        </section>
    </AppLayout>
</template>
