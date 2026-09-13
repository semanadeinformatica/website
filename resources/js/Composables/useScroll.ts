import { ref, computed, onMounted, onBeforeUnmount } from "vue";

export type QuickScrollMode = "sections" | "top";

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

        const viewportCenter = window.innerHeight / 2;

        const nextSection = sections.find((section) => {
            const rect = section.getBoundingClientRect();
            const sectionCenter = rect.top + rect.height / 2;
            return sectionCenter > viewportCenter + 60;
        });

        if (nextSection) {
            nextSection.scrollIntoView({ behavior: "smooth", block: "center" });
        } else {
            const first = sections[0];
            if (first) {
                first.scrollIntoView({ behavior: "smooth", block: "center" });
            } else {
                scrollToTop();
            }
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
