<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import type MyPageProps from "@/Types/PageProps";
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from "@lucide/vue";

interface FlashProps {
    banner?: string;
    bannerStyle?: string;
    bannerId?: string;
    message?: string;
}

const page = usePage();
const pageProps = computed(() => page.props as unknown as MyPageProps);

const show = ref(true);

const flash = computed<FlashProps>(
    () =>
        pageProps.value.jetstream?.flash ||
        ((page.props as { flash?: FlashProps }).flash ?? {}),
);

const style = computed(() => flash.value.bannerStyle || "success");
const message = computed(() => flash.value.banner || flash.value.message || "");
const id = computed(() => flash.value.bannerId || "");

watch(
    () => [id.value, message.value],
    ([newId, newMsg], [oldId, oldMsg]) => {
        if (newMsg && (newId !== oldId || newMsg !== oldMsg)) {
            show.value = true;
        }
    },
);

const bannerConfig = computed(() => {
    switch (style.value) {
        case "danger":
        case "error":
            return {
                wrapperClass:
                    "border-red-500/30 bg-[#140608]/85 text-red-100 shadow-[0_8px_32px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.12),0_0_20px_rgba(239,68,68,0.15)]",
                iconWrapperClass: "border border-red-500/25 bg-red-500/15",
                iconClass: "text-red-400",
                icon: AlertCircle,
            };
        case "warning":
            return {
                wrapperClass:
                    "border-amber-500/30 bg-[#160f04]/85 text-amber-100 shadow-[0_8px_32px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.12),0_0_20px_rgba(245,158,11,0.15)]",
                iconWrapperClass: "border border-amber-500/25 bg-amber-500/15",
                iconClass: "text-amber-400",
                icon: AlertTriangle,
            };
        case "info":
            return {
                wrapperClass:
                    "border-sky-500/30 bg-[#05111b]/85 text-sky-100 shadow-[0_8px_32px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.12),0_0_20px_rgba(14,165,233,0.15)]",
                iconWrapperClass: "border border-sky-500/25 bg-sky-500/15",
                iconClass: "text-sky-400",
                icon: Info,
            };
        case "success":
        default:
            return {
                wrapperClass:
                    "border-emerald-500/30 bg-[#06150e]/85 text-emerald-100 shadow-[0_8px_32px_rgba(0,0,0,0.5),inset_0_1px_0_rgba(255,255,255,0.12),0_0_20px_rgba(16,185,129,0.15)]",
                iconWrapperClass:
                    "border border-emerald-500/25 bg-emerald-500/15",
                iconClass: "text-emerald-400",
                icon: CheckCircle2,
            };
    }
});
</script>

<template>
    <div
        aria-live="polite"
        class="pointer-events-none sticky top-18 z-40 flex w-full justify-center px-4 transition-all duration-300 sm:top-20 sm:px-6"
        :class="show && message ? 'py-2' : 'py-0'"
    >
        <Transition
            appear
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="-translate-y-3 scale-95 opacity-0"
            enter-to-class="translate-y-0 scale-100 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-y-0 scale-100 opacity-100"
            leave-to-class="-translate-y-3 scale-95 opacity-0"
        >
            <aside
                v-if="show && message"
                role="status"
                class="pill-container pointer-events-auto relative flex max-w-xl items-center gap-2.5 rounded-2xl p-1.5 pr-2 pl-2 transition-all sm:gap-3 sm:rounded-full sm:pr-2 sm:pl-2"
                :class="bannerConfig.wrapperClass"
            >
                <div
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full sm:h-8 sm:w-8"
                    :class="bannerConfig.iconWrapperClass"
                >
                    <component
                        :is="bannerConfig.icon"
                        :size="15"
                        class="shrink-0"
                        :class="bannerConfig.iconClass"
                    />
                </div>

                <span class="px-1 text-xs leading-snug font-medium sm:text-sm">
                    {{ message }}
                </span>

                <button
                    type="button"
                    class="pill-item ml-auto flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center p-0 text-neutral-400 transition-colors hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/20 active:scale-95 sm:h-8 sm:w-8"
                    aria-label="Dispensar"
                    @click.prevent="show = false"
                >
                    <X :size="14" />
                </button>
            </aside>
        </Transition>
    </div>
</template>
