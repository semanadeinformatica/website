<script setup lang="ts">
    import TextInput from "@/Components/TextInput.vue";
    import route from "ziggy-js";
    import { ref, onUnmounted } from "vue";

    const pdfUrl = ref<string | null>(null);

    async function onSlotChange(event: Event) {
        const value = (event.target as HTMLSelectElement).value
        if (!value) return

        const response = await fetch(route('certificate.generate', {
            participant_name: "Tomás",
            event_title: "Talk Title",
            event_type: "Palestra",
            date: "2023-10-10",
            time: "10:00"
        }))

        if (response.ok) {
            const blob = await response.blob()

            if (pdfUrl.value) {
                window.URL.revokeObjectURL(pdfUrl.value)
            }
            
            // Create new blob URL and store it
            pdfUrl.value = window.URL.createObjectURL(blob)
        }
    }

    onUnmounted(() => {
        if (pdfUrl.value) {
            window.URL.revokeObjectURL(pdfUrl.value)
        }
    })
</script>

<template class="my-20">
    <TextInput
        type="select"
        required
        label="Evento para gerar certificado"
        @change="onSlotChange"
    >
        <option value="" disabled selected>Seleciona uma opção para gerar certificado</option>
        <template v-for="slot in $page.props.slots" :key="slot.id">
            <option :value="slot.id">
                {{ slot.name }}
            </option>
        </template>
    </TextInput>
    <object
        v-if="pdfUrl"
        :data="pdfUrl + '#toolbar=0&view=FitH'"
        width="100%"
        type="application/pdf"
        style="aspect-ratio: 1 / 1.41"
        class="max-md:hidden"
    >
        <p>Your browser doesn't support PDFs. <a :href="pdfUrl" target="_blank">Download the PDF</a>.</p>
    </object>
</template>

