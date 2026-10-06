<script setup lang="ts">
import { ref, computed, watch } from "vue";
import type EventDay from "@/Types/EventDay";
import { useScrollReveal } from "@/Composables/useScrollReveal";

interface Props {
    days: EventDay[];
    standCount: number;
    talkCount: number;
    activityCount: number;
    competitionCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
    competitionCount: 0,
});

const { targetRef, isVisible } = useScrollReveal({
    threshold: 0.15,
    once: false,
});

const displayedDays = ref(0);
const displayedStands = ref(0);
const displayedTalks = ref(0);
const displayedActivities = ref(0);
const displayedCompetitions = ref(0);
let activeRafs: number[] = [];

function animateNumber(
    targetVal: number,
    duration: number,
    onUpdate: (v: number) => void,
) {
    if (!targetVal || targetVal <= 0) {
        onUpdate(0);
        return;
    }
    const startTime = performance.now();
    function step(now: number) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Smooth cubic ease-in-out curve
        const ease =
            progress < 0.5
                ? 4 * progress * progress * progress
                : 1 - Math.pow(-2 * progress + 2, 3) / 2;

        onUpdate(Math.round(ease * targetVal));
        if (progress < 1) {
            const id = requestAnimationFrame(step);
            activeRafs.push(id);
        } else {
            onUpdate(targetVal);
        }
    }
    const id = requestAnimationFrame(step);
    activeRafs.push(id);
}

function cancelAnimations() {
    activeRafs.forEach((id) => cancelAnimationFrame(id));
    activeRafs = [];
}

function triggerAnimation() {
    cancelAnimations();

    animateNumber(
        props.days?.length ?? 0,
        1200,
        (v) => (displayedDays.value = v),
    );
    animateNumber(props.standCount, 1400, (v) => (displayedStands.value = v));
    animateNumber(props.talkCount, 1300, (v) => (displayedTalks.value = v));
    animateNumber(
        props.activityCount,
        1300,
        (v) => (displayedActivities.value = v),
    );
    animateNumber(
        props.competitionCount ?? 0,
        1100,
        (v) => (displayedCompetitions.value = v),
    );
}

watch(
    isVisible,
    (visible) => {
        if (visible) {
            triggerAnimation();
        } else {
            cancelAnimations();
            displayedDays.value = 0;
            displayedStands.value = 0;
            displayedTalks.value = 0;
            displayedActivities.value = 0;
            displayedCompetitions.value = 0;
        }
    },
    { immediate: true },
);

const statsList = computed(() => {
    const list = [];
    if (props.days?.length) {
        list.push({
            value: displayedDays.value,
            label: "dias de imersão",
        });
    }
    if (props.standCount) {
        list.push({
            value: displayedStands.value,
            label: "empresas em bancas",
        });
    }
    if (props.talkCount) {
        list.push({
            value: displayedTalks.value,
            label: "palestras",
        });
    }
    if (props.activityCount) {
        list.push({
            value: displayedActivities.value,
            label: "workshops",
        });
    }
    if (props.competitionCount) {
        list.push({
            value: displayedCompetitions.value,
            label:
                props.competitionCount === 1
                    ? "competição técnica"
                    : "competições técnicas",
        });
    }
    return list;
});
</script>

<template>
    <div ref="targetRef" class="relative mx-auto w-full max-w-6xl">
        <div
            class="mb-8 max-w-3xl transition-all duration-700 ease-in-out sm:mb-10"
            :class="[
                isVisible
                    ? 'translate-y-0 opacity-100'
                    : 'translate-y-12 opacity-0',
            ]"
        >
            <h2 class="text-3xl tracking-tight text-white sm:text-5xl">
                A dimensão da edição
                <span class="font-black">2026</span>
            </h2>
        </div>

        <div
            class="grid grid-cols-2 gap-6 border-t border-white/10 pt-6 transition-all delay-150 duration-700 ease-in-out sm:grid-cols-3 sm:pt-8 lg:grid-cols-5 lg:gap-8"
            :class="[
                isVisible
                    ? 'border-white/10 opacity-100'
                    : 'border-transparent opacity-0',
            ]"
        >
            <div
                v-for="(item, idx) in statsList"
                :key="idx"
                class="flex flex-col transition-all duration-700 ease-in-out"
                :style="{ transitionDelay: `${200 + idx * 100}ms` }"
                :class="[
                    isVisible
                        ? 'translate-y-0 scale-100 opacity-100'
                        : 'translate-y-12 scale-95 opacity-0',
                ]"
            >
                <div class="flex items-baseline">
                    <span
                        class="text-5xl font-black tracking-tighter text-white sm:text-6xl lg:text-7xl"
                    >
                        {{ item.value }}
                    </span>
                </div>
                <span
                    class="mt-2 text-xs font-semibold tracking-wider text-neutral-400 uppercase sm:text-sm"
                >
                    {{ item.label }}
                </span>
            </div>
        </div>
    </div>
</template>
