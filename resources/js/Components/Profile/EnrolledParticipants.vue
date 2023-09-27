<script setup lang="ts">
import type Participant from "@/Types/Participant";

interface Props {
    participants: Participant[] | undefined;
}

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col items-center bg-2023-bg pt-6 sm:pt-0">
        <div
            class="relative m-6 min-h-screen w-full flex-col items-center border border-black p-6 md:max-w-[85vw]"
        >
            <div class="flex w-full justify-around max-md:flex-col">
                <table class="table-fixed">
                    <thead>
                        <tr>
                            <th class="w-1/4 px-4 py-2">Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="participant in participants"
                            :key="participant.id"
                        >
                            <td class="border px-4 py-2">
                                {{ participant.user?.name }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
