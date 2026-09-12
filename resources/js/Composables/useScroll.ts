import { ref, computed, onMounted, onBeforeUnmount } from "vue";

export type QuickScrollMode = "sections" | "top";

const NAV_OFFSET = 70;

export function useScroll(mode: QuickScrollMode = "top") {
    const isAtBottom = ref(false);
    const isScrolled = ref(false);

    const shouldShow = computed(() =>
        mode === "top" ? isScrolled.value : true,
    );

    function updateScrollState() {
        const scrollY = window.scrollY || window.pageYOffset;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;

        isAtBottom.value = scrollY + windowHeight >= documentHeight - 60;
        isScrolled.value = scrollY > 400;
    }

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    function scrollToNextSection(): boolean {
        const sections = Array.from(
            document.querySelectorAll<HTMLElement>("section"),
        );
        if (!sections.length) return false;

        const currentY = window.scrollY || window.pageYOffset;

        const nextSection = sections.find((section) => {
            const top = section.getBoundingClientRect().top + currentY;
            return top > currentY + NAV_OFFSET + 20;
        });

        if (nextSection) {
            const targetY =
                nextSection.getBoundingClientRect().top + currentY - NAV_OFFSET;
            window.scrollTo({ top: targetY, behavior: "smooth" });
        } else {
            window.scrollTo({
                top: document.documentElement.scrollHeight,
                behavior: "smooth",
            });
        }
        return true;
    }

    function handleClick() {
        if (mode === "top") {
            scrollToTop();
            return;
        }

        if (isAtBottom.value) {
            scrollToTop();
            return;
        }

        scrollToNextSection();
    }

    onMounted(() => {
        updateScrollState();
        window.addEventListener("scroll", updateScrollState, { passive: true });
        window.addEventListener("resize", updateScrollState, { passive: true });
    });

    onBeforeUnmount(() => {
        window.removeEventListener("scroll", updateScrollState);
        window.removeEventListener("resize", updateScrollState);
    });

    return { isAtBottom, isScrolled, shouldShow, handleClick };
}

export default useScroll;
