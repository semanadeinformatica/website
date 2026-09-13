import { ref, onMounted, onBeforeUnmount } from "vue";

interface ScrollRevealOptions {
    threshold?: number;
    rootMargin?: string;
    once?: boolean;
}

/**
 * Composable that triggers visibility when an element scrolls into view.
 * Supports repeatable triggering on enter/exit with once: false.
 */
export function useScrollReveal(options: ScrollRevealOptions = {}) {
    const {
        threshold = 0.15,
        rootMargin = "0px",
        once = false,
    } = options;
    const targetRef = ref<HTMLElement | null>(null);
    const isVisible = ref(false);
    let observer: IntersectionObserver | null = null;

    function checkVisibility() {
        if (!targetRef.value) return;
        const rect = targetRef.value.getBoundingClientRect();
        const vh = window.innerHeight;
        const inView = rect.top < vh * 0.8 && rect.bottom > vh * 0.2;
        if (inView) {
            isVisible.value = true;
            if (once) {
                cleanup();
            }
        } else if (!once) {
            isVisible.value = false;
        }
    }

    function cleanup() {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
        window.removeEventListener("scroll", checkVisibility);
        document.removeEventListener("scroll", checkVisibility);
        window.removeEventListener("resize", checkVisibility);
    }

    onMounted(() => {
        if (!targetRef.value) return;

        // Immediate check on mount
        setTimeout(checkVisibility, 50);

        if (typeof IntersectionObserver !== "undefined") {
            observer = new IntersectionObserver(
                ([entry]) => {
                    if (
                        entry &&
                        entry.isIntersecting &&
                        entry.intersectionRatio >= threshold
                    ) {
                        isVisible.value = true;
                        if (once) {
                            cleanup();
                        }
                    } else if (!once && entry && !entry.isIntersecting) {
                        isVisible.value = false;
                    }
                },
                { threshold, rootMargin },
            );

            observer.observe(targetRef.value);
        }

        window.addEventListener("scroll", checkVisibility, { passive: true });
        document.addEventListener("scroll", checkVisibility, { passive: true });
        window.addEventListener("resize", checkVisibility, { passive: true });
    });

    onBeforeUnmount(() => {
        cleanup();
    });

    return { targetRef, isVisible };
}
