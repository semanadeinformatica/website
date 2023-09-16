<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

</script>

<template>
    <AppLayout title="Event">
        <!-- speaker/event intro -->
        <section class="flex flex-row w-fit self-center pt-10 mx-9 gap-16">
            <img class="shadow-lg shadow-2023-red border-2 border-black" src="https://picsum.photos/300/300" alt="">
            <div class="flex text-2023-teal-dark flex-col justify-center gap-10">
                <div class="flex flex-row w-fit gap-6 justify-center">
                    <h2 class="font-bold text-3xl uppercase">Name</h2>
                    <div class="flex gap-1 items-center">
                        <v-icon name="io-globe-outline"></v-icon>
                        <v-icon name="io-globe-outline"></v-icon>
                        <v-icon name="io-globe-outline"></v-icon>
                        <v-icon name="io-globe-outline"></v-icon>
                    </div>
                </div>
                <p class="row-start-2 col-span-2 max-w-xl text-justify">Lorem ipsum dolor sit amet consectetur adipisicing elit. Libero error ullam, quisquam soluta corrupti labore nihil eos iusto. Inventore, incidunt? Commodi eum consequatur rem quasi amet in rerum perferendis minus?</p>
            </div>
        </section>
        <!-- details -->
        <section class="flex flex-wrap justify-between flex-row px-16 py-24 relative bg-2023-teal-dark mt-20">
            <h1 class="shadow-md shadow-2023-bg border text-3xl p-2 px-3 bg-2023-red text-white font-bold border-black absolute -top-7 left-20">TITLE</h1>
            <div class="max-w-xl text-justify font-bold text-white">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Omnis modi atque odit ad, minima sed? Non quaerat dolorem obcaecati pariatur, a ipsum aperiam dignissimos quo cumque. Vel neque officiis repudiandae?
            </div>
            <div>
                date<br/>
                time
            </div>
        </section>
        <!-- sign up -->
        <section class="flex place-self-center w-full mt-10 items-center gap-4 flex-col">
            <p class="text-3xl text-2023-red font-bold w-fit">Vamos a isto?</p>
            <PrimaryButton class="w-fit"><span class="font-bold text-xl">Participar</span></PrimaryButton>
        </section>

    </AppLayout>
</template>

<style>
</style>