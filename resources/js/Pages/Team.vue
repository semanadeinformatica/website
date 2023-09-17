<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import SINFTeam from "@/Components/Team/SINFTeam.vue";

const mockTeam: {
    name: string;
    src: string;
    linkedin?: string;
    github?: string;
}[] = [
    {
        name: "person 1",
        src: "https://picsum.photos/200",
        linkedin: "google.com",
    },
    {
        name: "person 2",
        src: "https://picsum.photos/200",
        linkedin: "google.com",
    },
    {
        name: "person 3",
        src: "https://picsum.photos/200",
        linkedin: "google.com",
    },
    {
        name: "person 4",
        src: "https://picsum.photos/200",
        linkedin: "google.com",
    },
    {
        name: "person 5",
        src: "https://picsum.photos/200",
        linkedin: "google.com",
        github: "google.com",
    },
];
</script>

<template>
    <AppLayout title="Team">
        <div class="flex flex-col gap-6 border border-black p-10">
            <SINFTeam
                title="Coordenação Geral"
                color="orange"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Informática"
                color="teal-dark"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Imagem e Comunicação"
                color="red-dark"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Operações"
                color="teal"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Tesouraria"
                color="teal"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Programa"
                color="teal"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Competição de Programação"
                color="teal"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Competição de CTF"
                color="teal"
                :people="mockTeam"
            ></SINFTeam>
            <SINFTeam
                title="Relações Externas"
                color="teal"
                :people="mockTeam"
            ></SINFTeam>
        </div>
    </AppLayout>
</template>

<style>
main {
    padding: 2rem;
    padding-inline: 6rem;
}
</style>
