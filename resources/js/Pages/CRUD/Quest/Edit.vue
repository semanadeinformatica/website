<script setup lang="ts">
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import CardLayout from "@/Layouts/CardLayout.vue";
import type Edition from "@/Types/Edition";
import type Event from "@/Types/Event";
import type Quest from "@/Types/Quest";
import type Stand from "@/Types/Stand";
import { useForm } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    item: Quest;
    with: {
        editions: Edition[];
        stands: Stand[];
        events: Event[];
    };
}

const { item: quest } = defineProps<Props>();

const form = useForm({
    name: quest.name,
    category: quest.category.toLowerCase(),
    requirement:
        (quest.requirement_type.split("\\").pop() ?? "").toLowerCase() +
        ";" +
        quest.requirement_id.toString(),
    edition_id: quest.edition_id.toString(),
});

const submit = () => {
    form.put(route("admin.quests.update", quest));
};
</script>

<template>
    <CardLayout title="Editar quest">
        <form class="contents" @submit.prevent="submit">
            <TextInput
                id="name"
                v-model="form.name"
                label="Nome"
                type="text"
                required
                autofocus
                autocomplete="name"
                :error-message="form.errors.name"
            />

            <TextInput
                v-model="form.category"
                type="select"
                required
                label="Categoria"
                :error-message="form.errors.category"
            >
                <option value="company">Empresa</option>
                <option value="talk">Palestra</option>
                <option value="workshop">Workshop</option>
                <option value="milestone">Milestone</option>
                <option value="teambuilding">Team Building</option>
            </TextInput>

            <TextInput
                v-model="form.requirement"
                type="select"
                required
                label="Requisito"
                :error-message="form.errors.requirement"
            >
                <optgroup label="Bancas">
                    <option
                        v-for="stand in $props.with.stands"
                        :key="'stand;' + stand.id"
                        :value="'stand;' + stand.id"
                    >
                        {{ stand.sponsor?.company?.user?.name ?? stand.id }}
                    </option>
                </optgroup>
                <optgroup label="Eventos">
                    <option
                        v-for="event in $props.with.events"
                        :key="'event;' + event.id"
                        :value="'event;' + event.id"
                    >
                        {{ event.name }}
                    </option>
                </optgroup>
            </TextInput>

            <TextInput
                v-model="form.edition_id"
                type="select"
                required
                label="Edição"
                :error-message="form.errors.edition_id"
            >
                <option
                    v-for="edition in $props.with.editions"
                    :key="edition.id"
                    :value="edition.id"
                >
                    {{ edition.name }}
                </option>
            </TextInput>

            <PrimaryButton type="submit">Editar</PrimaryButton>
        </form>
    </CardLayout>
</template>
