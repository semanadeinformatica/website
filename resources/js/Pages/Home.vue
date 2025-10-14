<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import SpeakersCarousel from "@/Components/Home/SpeakersCarousel.vue";
import SponsorBanner from "@/Components/Home/SponsorBanner.vue";
import EnrollSection from "@/Components/Home/EnrollSection.vue";
import InfoPopup from "@/Components/Home/InfoPopup.vue";
import type Edition from "@/Types/Edition";
import type EventDay from "@/Types/EventDay";
import type { User } from "@/Types/User";
import { OhVueIcon } from "oh-vue-icons";
import type SponsorTier from "@/Types/SponsorTier";
import { default as MapComponent } from "@/Components/Home/Map.vue";
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from "vue";

const sections = ref<HTMLElement[]>([]);
const currentIdx = ref(0);

const atBottom = computed(() => {
  return sections.value.length > 0 && currentIdx.value >= sections.value.length - 1;
});

function getSortedSections(): HTMLElement[] {
  // Grab all top-level sections inside this page. If your layout wraps content,
  // this still works because we’re querying the rendered DOM.
  const list = Array.from(document.querySelectorAll<HTMLElement>("section"));
  // Sort by vertical position to be safe
  return list.sort((a, b) => (a.offsetTop ?? 0) - (b.offsetTop ?? 0));
}

function scrollToSection(idx: number) {
  const el = sections.value[idx];
  if (!el) return;
  el.scrollIntoView({ behavior: "smooth", block: "start" });
}

function goNextOrTop() {
  if (atBottom.value) {
    window.scrollTo({ top: 0, behavior: "smooth" });
    return;
  }
  const next = Math.min(currentIdx.value + 1, sections.value.length - 1);
  scrollToSection(next);
}

let observer: IntersectionObserver | null = null;

function setupObserver() {
  if (observer) observer.disconnect();

  observer = new IntersectionObserver(
    (entries) => {
      // Choose the section most centered in the viewport
      const visible = entries
        .filter(e => e.isIntersecting)
        .sort((a, b) => (b.intersectionRatio ?? 0) - (a.intersectionRatio ?? 0));

      if (visible[0]) {
        const idx = sections.value.findIndex(s => s === visible[0].target);
        if (idx >= 0) currentIdx.value = idx;
      } else {
        // Fallback: find closest by scrollY
        const y = window.scrollY + window.innerHeight * 0.35;
        let nearest = 0;
        let minDelta = Number.POSITIVE_INFINITY;
        sections.value.forEach((s, i) => {
          const top = s.getBoundingClientRect().top + window.scrollY;
          const delta = Math.abs(top - y);
          if (delta < minDelta) { minDelta = delta; nearest = i; }
        });
        currentIdx.value = nearest;
      }
    },
    {
      root: null,
      threshold: [0.25, 0.5, 0.75], // helps pick the "most visible" section
    }
  );

  sections.value.forEach((s) => observer!.observe(s));
}


onMounted(async () => {
  await nextTick();
  sections.value = getSortedSections();
  setupObserver();

  // Keep list fresh on resize/content shifts
  const onResize = () => {
    sections.value = getSortedSections();
    setupObserver();
  };
  window.addEventListener("resize", onResize);
  window.addEventListener("orientationchange", onResize);

  // Store cleanup on instance for removal later
  (onMounted as any)._onResize = onResize;
});

onBeforeUnmount(() => {
  if (observer) observer.disconnect();
  const onResize = (onMounted as any)._onResize;
  if (onResize) {
    window.removeEventListener("resize", onResize);
    window.removeEventListener("orientationchange", onResize);
  }
});

interface Props {
    edition: Edition;
    sponsorTiers: SponsorTier[];
    speakers: User[];
    days: EventDay[];
    activityCount: number;
    talkCount: number;
    standCount: number;
    canEnroll: boolean;
}

defineProps<Props>();

const formattedDate = (
    startDate: string,
    separator: string,
    endDate: string,
) => {
    const startDateArray = startDate.split(" ");
    const endDateArray = endDate.split(" ");

    let pointer = 0;

    while (
        startDateArray[startDateArray.length - pointer - 1] ===
        endDateArray[endDateArray.length - pointer - 1]
    )
        pointer++;

    startDate = startDateArray
        .slice(0, startDateArray.length - pointer)
        .join(" ");

    return `${startDate} ${separator} ${endDate}`;
};
</script>

<template>
    <AppLayout title="Home">
        <InfoPopup v-if="$page.props.auth.user && canEnroll" />
        <button
            v-if="canEnroll"
            type="button"
            @click="goNextOrTop"
            aria-label="Scroll to next section"
            class="group fixed bottom-10 right-12 z-50 grid place-items-center
                    w-12 h-12 rounded-full text-white bg-2025-bg-green
                    transition-transform duration-300 hover:scale-105
           [filter:drop-shadow(0_0_0_rgba(0,0,0,0))]
           hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]
           focus:outline-none"
            >
            <OhVueIcon :name="atBottom ? 'io-arrow-up' : 'io-arrow-down'" fill="white" />
        </button>
        <!-- LOGO & DATE -->
        <section
            class="relative flex min-h-screen flex-col content-center items-center justify-evenly gap-16 py-16"
        >
            <img
                id="svg-1"
                class="absolute left-[7%] w-36 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="images/cy-sinf.svg"
                alt="Stylized SINF logo"
            />
            <img
                id="svg-2"
                class="absolute right-[7%] top-16 w-32 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="images/rc-sinf.svg"
                alt="Stylized SINF logo"
            />
            <img
                id="svg-3"
                class="absolute bottom-24 right-[15%] w-24 animate-2023-maintenance-jump opacity-50 max-ml:hidden"
                src="images/oc-sinf.svg"
                alt="Stylized SINF logo"
            />




            <div class="relative">
                <img
                    class="w-96 max-ml:w-[300px]"
                    src="images/sinf logo.png"
                    alt="Stylized SINF logo"
                />
                <!-- <span
                    class="margin-0 absolute -bottom-5 right-0 text-xl font-bold text-2023-teal"
                    >{{ edition.year }}</span
                > -->
            </div>



            <!-- <p
                class="text-text-color mr-2 border border-solid border-white rounded-md p-2.5 px-8 text-lg font-bold text-2023-teal shadow-md shadow-black/80"
            >
                semana_de_informática
            </p> -->
            <p class="margin-0 text-2xl font-bold text-text-color">
                21 a 23 de outubro
            </p>

            <!-- CALL TO ACTION -->
            <EnrollSection v-if="canEnroll" id="enroll-wrapper" />
        </section>

        <!-- ABOUT US -->
        <section
            class="relative my-8 grid grid-flow-row grid-cols-2 text-justify max-lg:grid-flow-col max-lg:grid-cols-1 max-lg:grid-rows-2 bg-white/5 backdrop-blur-sm"
        >
            <h2
                id="aboutus"
                class="absolute -top-9 left-[calc(50%-88.2415px)] w-fit rounded-md bg-2025-blue p-3 text-3xl font-bold text-white"
            >
                Sobre nós
            </h2>
            <p class="p-20 text-lg text-white max-lg:pb-10">
                {{ $t("homePage.aboutUsText1") }}
            </p>
            <p class="p-20 text-lg text-white max-lg:py-10">
                {{ $t("homePage.aboutUsText2") }}
            </p>
        </section>

        <!-- GENERAL INFO -->
        <section class="relative flex flex-col py-24 max-xs:items-center ">
             <p
                class="bg-2025-blue-dark mr-[5px] flex w-fit place-self-center p-3 rounded-md text-2xl font-bold text-white"
            >
                Este ano temos
            </p>
                
            <template
                v-if="
                    days.length !== 0 ||
                    standCount !== 0 ||
                    talkCount !== 0 ||
                    activityCount !== 0
                "
            >
                <div
                    class="mt-10 mx-[10%] grid gap-4 rounded-md p-12 text-xl font-bold bg-white/5 backdrop-blur-sm shadow-[0_0_40px_-12px_rgba(255,255,255,0.18)] text-text-color max-lg:grid-cols-2 max-xs:grid-cols-1 md:flex md:flex-row md:items-center md:justify-around transition-transform duration-300 hover:scale-105
           [filter:drop-shadow(0_0_0_rgba(0,0,0,0))] border-0 ring-1 ring-white/10 
           hover:drop-shadow-[0_8px_20px_rgba(255,255,255,0.28)]
           focus:outline-none"
                >
                    <span v-if="days.length !== 0" class="text-center"
                        >{{ days.length }} dias</span
                    >
                    <span v-if="standCount !== 0" class="text-center"
                        >{{ standCount }} bancas</span
                    >
                    <span v-if="talkCount !== 0" class="text-center"
                        >{{ talkCount }} palestras</span
                    >
                    <span v-if="activityCount !== 0" class="text-center"
                        >{{ activityCount }} atividades</span
                    >
                </div>
            </template>
            <template v-else>
                <div
                    class="mx-[10%] flex items-center justify-center gap-4 p-12 text-xl font-bold text-2023-teal shadow-2xl max-lg:grid-cols-2 max-xs:grid-cols-1
                   bg-white/5 backdrop-blur-sm border-[3px] shadow-[0_0_40px_-12px_rgba(255,255,255,0.18)]"
                >
                    Muitas novidades para ti! Está quase...
                </div>
            </template>
        </section>
        <!-- SPEAKERS -->
        <section
            id="speakers"
            class="grid-rows-[repeat(3, 1fr)] my-10 mb-5 grid grid-cols-1 gap-10"
        >
            <p
                class="bg-2025-blue-dark mr-[5px] flex w-fit place-self-center p-3 rounded-md text-2xl font-bold text-white"
            >
                Oradores
            </p>
            <template v-if="speakers.length != 0">
                <SpeakersCarousel :speakers="speakers ?? []"></SpeakersCarousel>
            </template>
            <template v-else>
                <p
                    class="flex w-fit place-self-center text-2xl font-bold text-white"
                >
                    Em breve...
                </p>
            </template>
        </section>
        <!-- SPONSORS -->
        <section id="sponsors" class="flex flex-col gap-10 px-20 py-20">
            <p
                class=" mr-[5px] flex w-min place-self-center rounded-md bg-2025-blue-dark p-3 text-2xl font-bold text-white "
            >
                Patrocínios
            </p>
            <SponsorBanner
                v-for="(tier, idx) in sponsorTiers"
                :key="tier.id"
                :title="tier.name"
                :sponsors="tier.sponsors ?? []"
                :color="tier.color"
                :idx="idx"
            ></SponsorBanner>
        </section>

        <!-- MAP -->
        <!-- <section class="bg-2023-orange p-10">
            <MapComponent />
        </section> -->
    </AppLayout>
</template>

<style>
#svg-1 {
    animation-delay: -0.5s;
}

#svg-2 {
    animation-delay: -1s;
}

#svg-3 {
    animation-delay: -0.8s;
}
</style>
