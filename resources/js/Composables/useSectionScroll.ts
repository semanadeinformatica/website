import { ref, onMounted, onBeforeUnmount, type Ref } from "vue";

export interface SectionScrollOptions {
    sectionSelector?: string;
    threshold?: number;
    cooldownMs?: number;
}

/**
 * Composable for controlled section-by-section snap scrolling.
 * Anchors the center of each section at the center of the viewport and ensures
 * content from different sections does not appear simultaneously.
 */
export function useSectionScroll(
    containerRef?: Ref<HTMLElement | null>,
    options: SectionScrollOptions = {},
) {
    const {
        sectionSelector = ".landing-section, footer",
        threshold = 20,
        cooldownMs = 700,
    } = options;

    const currentSectionIndex = ref(0);
    let isTransitioning = false;
    let touchStartY = 0;
    let lastWheelTime = 0;

    function getSections(): HTMLElement[] {
        return Array.from(
            document.querySelectorAll<HTMLElement>(sectionSelector),
        );
    }

    function scrollToSection(index: number, immediate = false) {
        const sections = getSections();
        if (!sections.length) return;

        const clampedIndex = Math.max(0, Math.min(sections.length - 1, index));
        currentSectionIndex.value = clampedIndex;

        const target = sections[clampedIndex];
        if (!target) return;

        isTransitioning = true;
        const isFooter = target.tagName.toLowerCase() === "footer";
        target.scrollIntoView({
            behavior: immediate ? "instant" : "smooth",
            block: isFooter ? "end" : "center",
            inline: "nearest",
        });

        setTimeout(() => {
            isTransitioning = false;
        }, cooldownMs);
    }

    function nextSection() {
        const sections = getSections();
        if (currentSectionIndex.value < sections.length - 1) {
            scrollToSection(currentSectionIndex.value + 1);
        }
    }

    function prevSection() {
        if (currentSectionIndex.value > 0) {
            scrollToSection(currentSectionIndex.value - 1);
        }
    }

    function isModalOrInteractiveOpen(e: Event): boolean {
        const target = e.target as HTMLElement | null;
        if (!target) return false;
        return !!target.closest(
            '[role="dialog"], [aria-modal="true"], .modal-container, select, input, textarea, button',
        );
    }

    function onWheel(e: WheelEvent) {
        // Allow modal or dropdown contents to scroll normally
        const target = e.target as HTMLElement | null;
        if (
            target?.closest(
                '[role="dialog"], [aria-modal="true"], .modal-content, textarea',
            )
        ) {
            return;
        }

        // Only handle vertical scrolling
        if (Math.abs(e.deltaY) < threshold) return;

        // Prevent free-scrolling that would display parts of two sections at once
        e.preventDefault();

        const now = Date.now();
        if (isTransitioning || now - lastWheelTime < cooldownMs) return;

        lastWheelTime = now;
        if (e.deltaY > 0) {
            nextSection();
        } else {
            prevSection();
        }
    }

    function onKeyDown(e: KeyboardEvent) {
        if (isModalOrInteractiveOpen(e)) return;

        if (
            ["ArrowDown", "PageDown"].includes(e.key) ||
            (e.key === " " && !e.shiftKey)
        ) {
            e.preventDefault();
            nextSection();
        } else if (
            ["ArrowUp", "PageUp"].includes(e.key) ||
            (e.key === " " && e.shiftKey)
        ) {
            e.preventDefault();
            prevSection();
        } else if (e.key === "Home") {
            e.preventDefault();
            scrollToSection(0);
        } else if (e.key === "End") {
            const sections = getSections();
            e.preventDefault();
            scrollToSection(sections.length - 1);
        }
    }

    function onTouchStart(e: TouchEvent) {
        if (isModalOrInteractiveOpen(e)) return;
        touchStartY = e.touches[0].clientY;
    }

    function onTouchEnd(e: TouchEvent) {
        if (isModalOrInteractiveOpen(e)) return;
        const touchEndY = e.changedTouches[0].clientY;
        const diff = touchStartY - touchEndY;

        if (Math.abs(diff) > 40) {
            if (diff > 0) {
                nextSection();
            } else {
                prevSection();
            }
        }
    }

    function syncActiveSectionFromScroll() {
        if (isTransitioning) return;
        const sections = getSections();
        if (!sections.length) return;

        const viewportCenter = window.innerHeight / 2;
        let closestIndex = 0;
        let minDistance = Infinity;

        sections.forEach((section, idx) => {
            const rect = section.getBoundingClientRect();
            const sectionCenter = rect.top + rect.height / 2;
            const distance = Math.abs(sectionCenter - viewportCenter);
            if (distance < minDistance) {
                minDistance = distance;
                closestIndex = idx;
            }
        });

        currentSectionIndex.value = closestIndex;
    }

    function syncFromHash() {
        const hash = window.location.hash;
        if (!hash) return;
        const target = document.querySelector<HTMLElement>(hash);
        if (target) {
            const sections = getSections();
            const idx = sections.indexOf(target);
            if (idx !== -1) {
                scrollToSection(idx);
            }
        }
    }

    onMounted(() => {
        document.documentElement.classList.add("landing-snap");
        window.addEventListener("wheel", onWheel, { passive: false });
        window.addEventListener("keydown", onKeyDown);
        window.addEventListener("touchstart", onTouchStart, { passive: true });
        window.addEventListener("touchend", onTouchEnd, { passive: true });
        window.addEventListener("scroll", syncActiveSectionFromScroll, {
            passive: true,
        });
        window.addEventListener("hashchange", syncFromHash);

        if (window.location.hash) {
            setTimeout(syncFromHash, 150);
        }
    });

    onBeforeUnmount(() => {
        document.documentElement.classList.remove("landing-snap");
        window.removeEventListener("wheel", onWheel);
        window.removeEventListener("keydown", onKeyDown);
        window.removeEventListener("touchstart", onTouchStart);
        window.removeEventListener("touchend", onTouchEnd);
        window.removeEventListener("scroll", syncActiveSectionFromScroll);
        window.removeEventListener("hashchange", syncFromHash);
    });

    return {
        currentSectionIndex,
        scrollToSection,
        nextSection,
        prevSection,
    };
}
