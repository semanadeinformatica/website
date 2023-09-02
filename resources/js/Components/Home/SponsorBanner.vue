<script setup lang="ts">
defineProps<{
    tier: string;
}>();
</script>

<template>
    <div v-if="tier == 'plat'">
        <p class="text-2xl font-bold text-2023-orange">Platinum</p>
        <div
            class="grid grid-cols-3 justify-around gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-orange"
        >
            <img
                class="ml-auto mr-auto"
                src="https://picsum.photos/300/200"
                alt="Company"
            />
            <img
                class="ml-auto mr-auto"
                src="https://picsum.photos/300/200"
                alt="Company"
            />
            <img
                class="ml-auto mr-auto"
                src="https://picsum.photos/300/200"
                alt="Company"
            />
        </div>
    </div>
    <div v-else-if="tier == 'gold'">
        <p class="text-2xl font-bold text-2023-teal-dark">Gold</p>
        <div
            class="grid grid-cols-8 grid-rows-2 gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-teal-dark"
        >
            <img
                class="col-start-1 col-end-3 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-3 col-end-5 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-5 col-end-7 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-7 col-end-9 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-2 col-end-4 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-4 col-end-6 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-6 col-end-8 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
        </div>
    </div>
    <div v-else-if="tier == 'silver'">
        <p class="text-2xl font-bold text-2023-red-dark">Bronze</p>
        <div
            class="align-center grid grid-cols-8 grid-rows-2 justify-center gap-4 border border-solid border-black p-10 shadow-2xl shadow-2023-red-dark"
        >
            <img
                class="col-start-1 col-end-3 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-3 col-end-5 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-5 col-end-7 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-7 col-end-9 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-2 col-end-4 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-4 col-end-6 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
            <img
                class="col-start-6 col-end-8 row-start-2 ml-auto mr-auto"
                src="https://picsum.photos/200/100"
                alt="Company"
            />
        </div>
    </div>
</template>
