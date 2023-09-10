<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import ProfilePicture from "@/Components/Profile/ProfilePicture.vue";
import InfoCard from "@/Components/Profile/InfoCard.vue";
import type Student from "@/Types/Student"

interface Props {
    item: Student;
}

defineProps<Props>();
</script>

<template>
    <AppLayout title="Profile">
        <main
            class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0"
        >
            <div
                class="relative m-5 flex w-full flex-col items-center border border-black p-8 md:max-w-[90vw]"
            >
                <div class="flex justify-around w-full"> 
                    <ProfilePicture :student="item"/>
                    <InfoCard :student="item"/>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
