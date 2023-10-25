<script setup lang="ts">
import type Event from "@/Types/Event";
import { Link } from "@inertiajs/vue3";
import route from "ziggy-js";

interface Props {
    state: "used" | "acquired" | "available";
    event: Event;
}

const props = defineProps<Props>();

const formatTimeString = (time: string): string => {
    return `1970-01-01T${time}.000000Z`;
};

// https://stackoverflow.com/a/52171480/11571888
const hashCode = (str: string): number => {
    let hash = 0;
    if (str.length == 0) {
        return hash;
    }
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = (hash << 5) - hash + char;
    }
    return Math.abs(hash);
};

const mainColor = () => {
    if (props.event.capacity && props.event.capacity > 0) {
        return "bg-2023-teal-dark";
    } else {
        return "bg-2023-orange";
    }
};

const accentColor = () => {
    if (props.event.capacity && props.event.capacity > 0) {
        return "bg-2023-orange";
    } else {
        return "bg-2023-teal-dark";
    }
};
</script>

<template>
    <Link :href="route('event.show', event)" class="relative">
        <div
            class="grid aspect-[5/2] place-content-stretch place-items-stretch @container"
            :class="[
                state == 'available'
                    ? 'cursor-pointer select-none opacity-50 grayscale transition-all hover:blur-none [@media(hover:hover)]:blur-sm'
                    : '',
            ]"
        >
            <svg
                class="col-start-1 row-start-1 overflow-visible"
                viewBox="0 0 500 200"
            >
                <path
                    v-if="state != 'used'"
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M16 0C16 8 8 16 0 16V24C4 26 4 30 0 32V40C4 42 4 46 0 48V56C4 58 4 62 0 64V72C4 74 4 78 0 80V88C4 90 4 94 0 96V104C4 106 4 110 0 112V120C4 122 4 126 0 128V136C4 138 4 142 0 144V152C4 154 4 158 0 160V168C4 170 4 174 0 176V184C8 184 16 192 16 200H144C152 200 160 192 160 184V200H484C484 192 492 184 500 184V176C496 174 496 170 500 168V160C496 158 496 154 500 152V144C496 142 496 138 500 136V128C496 126 496 122 500 120V112C496 110 496 106 500 104V96C496 94 496 90 500 88V80C496 78 496 74 500 72V64C496 62 496 58 500 56V48C496 46 496 42 500 40V32C496 30 496 26 500 24V16C492 16 484 8 484 0H160V16C160 8 152 0 144 0H16ZM162 176V159H158V176H162ZM158 149H162V132H158V149ZM162 122V105H158V122H162ZM158 95H162V78H158V95ZM162 68V51H158V68H162ZM158 41H162V24H158V41Z"
                    stroke="black"
                    stroke-width="2px"
                    fill="none"
                />
                <template v-else>
                    <path
                        class="origin-left -translate-x-[6px] -rotate-6"
                        d="M0 16C8 16 16 8 16 0H144C152 0 160 8 160 16V24H158V68H162V78H158V122H162V132H158V176H160V184C160 192 152 200 144 200H16C16 192 8 184 0 184V176C4 174 4 170 0 168V160C4 158 4 154 0 152V144C4 142 4 138 0 136V128C4 126 4 122 0 120V112C4 110 4 106 0 104V96C4 94 4 90 0 88V80C4 78 4 74 0 72V64C4 62 4 58 0 56V48C4 46 4 42 0 40V32C4 30 4 26 0 24V16Z"
                        stroke="black"
                        stroke-width="2px"
                        fill="none"
                    />
                    <path
                        class="origin-right translate-x-[12.666px] rotate-6"
                        d="M160 176H162V159H158V149H162V105H158V95H162V51H158V41H162V24H160V0H484C484 8 492 16 500 16V24C496 26 496 30 500 32V40C496 42 496 46 500 48V56C496 58 496 62 500 64V72C496 74 496 78 500 80V88C496 90 496 94 500 96V104C496 106 496 110 500 112V120C496 122 496 126 500 128V136C496 138 496 142 500 144V152C496 154 496 158 500 160V168C496 170 496 174 500 176V184C492 184 484 192 484 200H160V176Z"
                        stroke="black"
                        stroke-width="2px"
                        fill="none"
                    />
                </template>
            </svg>

            <div
                class="col-start-1 row-start-1 flex max-h-full max-w-[350px] text-[3.2cqi] font-black text-white"
                :class="[state == 'used' ? '' : 'ticket-clip-path']"
            >
                <div
                    class="flex w-[32.4%] flex-col py-[3.2%]"
                    :class="[
                        state == 'used'
                            ? 'ticket-left-clip-path -mr-[.4%] origin-left -translate-x-[3.7%] -rotate-6'
                            : '',
                        mainColor(),
                    ]"
                >
                    <span
                        class="flex px-[.75em] py-[.5em]"
                        :class="accentColor()"
                    >
                        NR: {{ hashCode((event.id + "SINF2023").toString()) }}
                    </span>

                    <div
                        class="my-auto flex flex-col gap-[.25em] px-[.75em] py-[.5em]"
                    >
                        <span
                            v-if="event.location"
                            :title="event.location"
                            class="line-clamp-2 truncate whitespace-normal"
                            >LOCAL: {{ event.location }}</span
                        >
                        <span v-if="event.time_start"
                            >HORA:
                            {{
                                $d(
                                    new Date(
                                        formatTimeString(event.time_start),
                                    ),
                                    "hourMinute",
                                )
                            }}</span
                        >
                        <span v-if="event.event_day?.date"
                            >DATA:
                            {{ $d(event.event_day?.date, "short") }}</span
                        >
                    </div>
                </div>
                <div
                    class="flex w-[68.4%] flex-col gap-[1em] py-[3.2%]"
                    :class="[
                        state == 'used'
                            ? 'ticket-right-clip-path -ml-[.4%] origin-right translate-x-[3.7%] rotate-6'
                            : '',
                        mainColor(),
                    ]"
                >
                    <span
                        class="truncate px-[1em] py-[.5em] text-right"
                        :class="accentColor()"
                    >
                        {{ event.topic }}
                    </span>

                    <span
                        class="line-clamp-4 truncate whitespace-normal px-[1em]"
                        >{{ event.name }}</span
                    >
                </div>
            </div>
        </div>
    </Link>
</template>

<style>
.ticket-clip-path {
    clip-path: url(#ticket-clip-path);
    object-fit: cover;
}

.ticket-left-clip-path {
    clip-path: url(#ticket-left-clip-path);
    object-fit: cover;
}

.ticket-right-clip-path {
    clip-path: url(#ticket-right-clip-path);
    object-fit: cover;
}
</style>
