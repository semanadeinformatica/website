<script setup lang="ts">
import type { User } from "@/Types/User";

interface Props {
    item: User | undefined;
}

defineProps<Props>();
</script>

<template>
    <div
        class="mr-[5px] flex w-fit border border-solid border-black bg-2023-bg p-3 text-2023-teal shadow shadow-2023-red-dark"
    >
        <div class="flex-col">
            <p class="font-bold">{{ item?.name }}</p>
            <p>
                {{ item?.email }}
            </p>
        </div>
        <button v-if="$page.props.auth.user.id == item?.id">
            <v-icon name="io-create" fill="2023-teal" scale="1.3"></v-icon>
        </button>
    </div>
</template>
