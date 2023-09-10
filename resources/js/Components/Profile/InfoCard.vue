<script setup lang="ts">

import type Student from "@/Types/Student"

interface Props {
    student: Student;
}

defineProps<Props>();

</script>

<template>
    <div class="mr-[5px] flex w-fit border border-solid border-black bg-2023-bg p-3 text-2023-teal shadow shadow-2023-red-dark">
        <div class="flex-col">
            <p class="font-bold">
                Name 
            </p>
            <p>
                {{ student?.social_media?.email }}
            </p>
        </div>
    </div>
</template>
