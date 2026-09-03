<script setup lang="ts">
import { ref } from "vue";

interface Orb {
    id: number;
    className: string;
    style: {
        top: string;
        left: string;
        animationDelay: string;
    };
}

interface Zone {
    minTop: number;
    maxTop: number;
    minLeft: number;
    maxLeft: number;
}

const getRandomInRange = (min: number, max: number): number => {
    return Math.round(min + Math.random() * (max - min));
};

const shuffleArray = <T,>(array: T[]): T[] => {
    const arr = [...array];
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        const current = arr[i];
        const randomItem = arr[j];
        if (current !== undefined && randomItem !== undefined) {
            arr[i] = randomItem;
            arr[j] = current;
        }
    }
    return arr;
};

const zones: Zone[] = [
    { minTop: -30, maxTop: 15, minLeft: -25, maxLeft: 20 },
    { minTop: -30, maxTop: 15, minLeft: 45, maxLeft: 85 },
    { minTop: 45, maxTop: 85, minLeft: -25, maxLeft: 20 },
    { minTop: 45, maxTop: 85, minLeft: 45, maxLeft: 85 },
];

const orbDefinitions = [
    {
        className:
            "glow-orb-primary-1 absolute h-[85vh] w-[80vw] rounded-[100%] blur-[70px] will-change-transform sm:blur-[100px]",
        maxDelay: 10,
    },
    {
        className:
            "glow-orb-secondary-1 absolute h-[85vh] w-[80vw] rounded-[100%] blur-[70px] will-change-transform sm:blur-[100px]",
        maxDelay: 12,
    },
    {
        className:
            "glow-orb-primary-2 absolute h-[65vh] w-[60vw] rounded-[100%] blur-[60px] will-change-transform sm:blur-[90px]",
        maxDelay: 9,
    },
    {
        className:
            "glow-orb-secondary-2 absolute h-[65vh] w-[60vw] rounded-[100%] blur-[60px] will-change-transform sm:blur-[90px]",
        maxDelay: 11,
    },
];

const shuffledZones = shuffleArray(zones);

const orbs = ref<Orb[]>(
    orbDefinitions.map((orb, index) => {
        const zone = shuffledZones[index] ?? zones[index];
        const top = getRandomInRange(zone.minTop, zone.maxTop);
        const left = getRandomInRange(zone.minLeft, zone.maxLeft);
        const delay = (-(Math.random() * orb.maxDelay)).toFixed(2);

        return {
            id: index,
            className: orb.className,
            style: {
                top: `${top}%`,
                left: `${left}%`,
                animationDelay: `${delay}s`,
            },
        };
    }),
);
</script>

<template>
    <div
        class="pointer-events-none fixed inset-0 z-0 overflow-hidden"
        aria-hidden="true"
    >
        <div
            v-for="orb in orbs"
            :key="orb.id"
            :class="orb.className"
            :style="orb.style"
        />
    </div>
</template>
