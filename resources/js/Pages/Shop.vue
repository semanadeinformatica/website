<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import ShopItem from '@/Components/Shop/ShopItem.vue';

const mockItems = [{
    name: 'Item 1',
    price: 100,
    description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, voluptatum.',
    image: 'https://picsum.photos/200/200'
},{
    name: 'Item 2',
    price: 100,
    description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, voluptatum.',
    image: 'https://picsum.photos/200/200'
},{
    name: 'Item 1',
    price: 100,
    description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, voluptatum.',
    image: 'https://picsum.photos/200/200'
},{
    name: 'Item 1',
    price: 100,
    description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, voluptatum.',
    image: 'https://picsum.photos/200/200'
},{
    name: 'Item 1',
    price: 100,
    description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, voluptatum.',
    image: 'https://picsum.photos/200/200'
},]

</script>

<template>
    <AppLayout title="Loja">
        <div class="flex self-center justify-center content-center items-center py-10">
            <h1 class="w-fit border border-black shadow-2023-teal shadow-md bg-2023-red text-white text-2xl font-bold p-2 px-5">Loja</h1>
        </div>
        <section class="flex flex-wrap gap-10 align-center justify-center" style="grid-template-columns: repeat(auto-fill, 250px);">
            <ShopItem v-for="item in mockItems" :key="item.name" v-bind="item">
            </ShopItem>
        </section>
    </AppLayout>
</template>