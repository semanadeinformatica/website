<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import ShopItem from '@/Components/Shop/ShopItem.vue';

</script>

<template>
    <AppLayout title="Loja">
        <div class="flex self-center justify-center content-center items-center py-10">
            <h1 class="w-fit border border-black shadow-2023-teal shadow-md bg-2023-red text-white text-2xl font-bold p-2 px-5">Loja</h1>
        </div>
        <section class="grid gap-10 align-center justify-center px-32" style="grid-template-columns: repeat(auto-fill, minmax(18em, 20em));">
            <ShopItem v-for="product in $page.props.products" :key="product" :product="product">
            </ShopItem>
        </section>
    </AppLayout>
</template>