<script setup lang="ts">
interface Props {
    state: "used" | "acquired" | "available";
}

defineProps<Props>();
</script>

<template>
    <div
        class="@container grid aspect-[5/2] place-content-stretch place-items-stretch"
        :class="[state == 'available' ? 'opacity-50 grayscale' : '']"
    >
        <svg
            class="col-start-1 row-start-1 overflow-visible"
            viewBox="0 0 500 200"
        >
            <clipPath id="ticket-clip-path" clipPathUnits="objectBoundingBox">
                <path
                    clip-rule="evenodd"
                    d="M0.032,0 C0.032,0.04,0.016,0.08,0,0.08 V0.12 C0.008,0.13,0.008,0.15,0,0.16 V0.2 C0.008,0.21,0.008,0.23,0,0.24 V0.28 C0.008,0.29,0.008,0.31,0,0.32 V0.36 C0.008,0.37,0.008,0.39,0,0.4 V0.44 C0.008,0.45,0.008,0.47,0,0.48 V0.52 C0.008,0.53,0.008,0.55,0,0.56 V0.6 C0.008,0.61,0.008,0.63,0,0.64 V0.68 C0.008,0.69,0.008,0.71,0,0.72 V0.76 C0.008,0.77,0.008,0.79,0,0.8 V0.84 C0.008,0.85,0.008,0.87,0,0.88 V0.92 C0.016,0.92,0.032,0.96,0.032,1 H0.288 C0.304,1,0.32,0.96,0.32,0.92 V1 H0.968 C0.968,0.96,0.984,0.92,1,0.92 V0.88 C0.992,0.87,0.992,0.85,1,0.84 V0.8 C0.992,0.79,0.992,0.77,1,0.76 V0.72 C0.992,0.71,0.992,0.69,1,0.68 V0.64 C0.992,0.63,0.992,0.61,1,0.6 V0.56 C0.992,0.55,0.992,0.53,1,0.52 V0.48 C0.992,0.47,0.992,0.45,1,0.44 V0.4 C0.992,0.39,0.992,0.37,1,0.36 V0.32 C0.992,0.31,0.992,0.29,1,0.28 V0.24 C0.992,0.23,0.992,0.21,1,0.2 V0.16 C0.992,0.15,0.992,0.13,1,0.12 V0.08 C0.984,0.08,0.968,0.04,0.968,0 H0.32 V0.08 C0.32,0.04,0.304,0,0.288,0 H0.032 M0.324,0.88 V0.795 H0.316 V0.88 H0.324 M0.316,0.745 H0.324 V0.66 H0.316 V0.745 M0.324,0.61 V0.525 H0.316 V0.61 H0.324 M0.316,0.475 H0.324 V0.39 H0.316 V0.475 M0.324,0.34 V0.255 H0.316 V0.34 H0.324 M0.316,0.205 H0.324 V0.12 H0.316 V0.205"
                />
            </clipPath>
            <clipPath
                id="ticket-left-clip-path"
                clipPathUnits="objectBoundingBox"
            >
                <path
                    d="M0,0.08 C0.049,0.08,0.099,0.04,0.099,0 H0.889 C0.938,0,0.988,0.04,0.988,0.08 V0.12 H0.975 V0.34 H1 V0.39 H0.975 V0.61 H1 V0.66 H0.975 V0.88 H0.988 V0.92 C0.988,0.96,0.938,1,0.889,1 H0.099 C0.099,0.96,0.049,0.92,0,0.92 V0.88 C0.025,0.87,0.025,0.85,0,0.84 V0.8 C0.025,0.79,0.025,0.77,0,0.76 V0.72 C0.025,0.71,0.025,0.69,0,0.68 V0.64 C0.025,0.63,0.025,0.61,0,0.6 V0.56 C0.025,0.55,0.025,0.53,0,0.52 V0.48 C0.025,0.47,0.025,0.45,0,0.44 V0.4 C0.025,0.39,0.025,0.37,0,0.36 V0.32 C0.025,0.31,0.025,0.29,0,0.28 V0.24 C0.025,0.23,0.025,0.21,0,0.2 V0.16 C0.025,0.15,0.025,0.13,0,0.12 V0.08"
                />
            </clipPath>
            <clipPath
                id="ticket-right-clip-path"
                clipPathUnits="objectBoundingBox"
            >
                <path
                    d="M0.006,0.88 H0.012 V0.795 H0 V0.745 H0.012 V0.525 H0 V0.475 H0.012 V0.255 H0 V0.205 H0.012 V0.12 H0.006 V0 H0.953 C0.953,0.04,0.977,0.08,1,0.08 V0.12 C0.988,0.13,0.988,0.15,1,0.16 V0.2 C0.988,0.21,0.988,0.23,1,0.24 V0.28 C0.988,0.29,0.988,0.31,1,0.32 V0.36 C0.988,0.37,0.988,0.39,1,0.4 V0.44 C0.988,0.45,0.988,0.47,1,0.48 V0.52 C0.988,0.53,0.988,0.55,1,0.56 V0.6 C0.988,0.61,0.988,0.63,1,0.64 V0.68 C0.988,0.69,0.988,0.71,1,0.72 V0.76 C0.988,0.77,0.988,0.79,1,0.8 V0.84 C0.988,0.85,0.988,0.87,1,0.88 V0.92 C0.977,0.92,0.953,0.96,0.953,1 H0.006 V0.88"
                />
            </clipPath>

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
            class="col-start-1 row-start-1 flex max-h-full text-[3.2cqi] font-black text-white"
            :class="[state == 'used' ? '' : 'ticket-clip-path']"
        >
            <div
                class="flex w-[32.4%] flex-col bg-2023-orange py-[3.2%]"
                :class="[
                    state == 'used'
                        ? 'ticket-left-clip-path -mr-[.4%] origin-left -translate-x-[3.7%] -rotate-6'
                        : '',
                ]"
            >
                <span class="flex bg-2023-teal-dark px-[1em] py-[.5em]">
                    NR: 000000001
                </span>

                <div
                    class="my-auto flex flex-col gap-[.25em] px-[1em] py-[.5em]"
                >
                    <span>SALA: B208</span>
                    <span>HORA: 16:10</span>
                    <span>DATA: 1/10/2023</span>
                </div>
            </div>
            <div
                class="flex w-[68.4%] flex-col gap-[1em] bg-2023-orange py-[3.2%]"
                :class="[
                    state == 'used'
                        ? 'ticket-right-clip-path -ml-[.4%] origin-right translate-x-[3.7%] rotate-6'
                        : '',
                ]"
            >
                <span
                    class="flex justify-end bg-2023-teal-dark px-[1em] py-[.5em] text-right"
                >
                    WORKSHOP
                </span>

                <span class="px-[1em]">Capture the Flag 101</span>
            </div>
        </div>
    </div>
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
