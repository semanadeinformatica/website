<script setup lang="ts"></script>

<template>
    <div class="transition-all duration-500">
        <img
            src="https://picsum.photos/200"
            class="w-[200px] rounded-full border-2 border-solid border-black object-cover"
            alt=""
        />
        <p class="py-4 text-lg text-2023-teal-dark">John Doe</p>
    </div>
</template>

<style>
.carousel__slide--active div {
    transform: scale(1.1);
}
</style>
