<script setup lang="ts">
import type { User } from "@/Types/User";
import { ref } from "vue";

interface Props {
    participants: User[];
}

const props = defineProps<Props>();

// get the participants got in the user controller
const participants = ref<User[]>(props.participants);
</script>

<template>
    <div>{{ participants }}</div>
    <table class="table-fixed">
        <thead>
            <tr>
                <th>Name</th>
            </tr>
        </thead>
        <tbody v-for="participant in participants" :key="participant.name">
            <tr>
                <td>{{ participant.name }}</td>
            </tr>
        </tbody>
    </table>
</template>
