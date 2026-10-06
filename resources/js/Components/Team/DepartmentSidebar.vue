<script setup lang="ts">
import { computed } from "vue";
import type Department from "@/Types/Department";

interface Props {
    departments: Department[];
    selectedId: number | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: "select", id: number | null): void;
}>();

const departmentsWithStaff = computed(() =>
    props.departments.filter((d) => (d.staff?.length ?? 0) > 0),
);

const totalMembers = computed(() =>
    departmentsWithStaff.value.reduce(
        (sum, d) => sum + (d.staff?.length ?? 0),
        0,
    ),
);

interface SidebarItem {
    id: number | null;
    label: string;
    count: number;
    active: boolean;
}

const items = computed<SidebarItem[]>(() => [
    {
        id: null,
        label: "Todos",
        count: totalMembers.value,
        active: props.selectedId === null,
    },
    ...departmentsWithStaff.value.map((dept) => ({
        id: dept.id,
        label: dept.name,
        count: dept.staff?.length ?? 0,
        active: props.selectedId === dept.id,
    })),
]);

const handleSelect = (id: number | null, event?: MouseEvent) => {
    emit("select", id);
    if (event?.currentTarget) {
        const el = event.currentTarget as HTMLElement;
        el.scrollIntoView({
            behavior: "smooth",
            inline: "center",
            block: "nearest",
        });
    }
};
</script>

<template>
    <div v-if="departmentsWithStaff.length > 1" class="w-full">
        <div
            class="sticky top-16 z-30 -mx-4 mb-6 px-4 py-2.5 backdrop-blur-md sm:-mx-6 sm:px-6 lg:hidden"
        >
            <div
                class="pill-container no-scrollbar max-w-full flex-nowrap gap-1 overflow-x-auto p-1.5"
            >
                <button
                    v-for="item in items"
                    :key="item.id ?? 'all'"
                    type="button"
                    class="pill-item shrink-0 gap-2 text-xs whitespace-nowrap sm:text-sm"
                    :class="item.active ? 'pill-item-active' : ''"
                    @click="handleSelect(item.id, $event)"
                >
                    <span>{{ item.label }}</span>
                    <span
                        class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold transition-colors"
                        :class="
                            item.active
                                ? 'bg-white/20 text-white'
                                : 'bg-white/10 text-neutral-400'
                        "
                    >
                        {{ item.count }}
                    </span>
                </button>
            </div>
        </div>

        <nav
            class="no-scrollbar hidden max-h-[calc(100vh-7.5rem)] flex-col gap-1.5 overflow-y-auto rounded-3xl border border-white/8 bg-black/50 p-2 shadow-[var(--shadow-pill-inset)] backdrop-blur-md lg:flex"
            aria-label="Departamentos"
        >
            <button
                v-for="item in items"
                :key="item.id ?? 'all'"
                type="button"
                :class="[
                    'group flex w-full cursor-pointer items-center justify-between gap-3 rounded-full px-4 py-2.5 text-left text-sm font-medium transition-all duration-200 select-none',
                    item.active
                        ? 'pill-item-active'
                        : 'text-neutral-300 hover:bg-white/8 hover:text-white active:scale-[0.98]',
                ]"
                @click="handleSelect(item.id)"
            >
                <span class="truncate">{{ item.label }}</span>
                <span
                    class="ml-auto shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold transition-colors"
                    :class="
                        item.active
                            ? 'bg-white/20 text-white'
                            : 'bg-white/10 text-neutral-400 group-hover:text-neutral-300'
                    "
                >
                    {{ item.count }}
                </span>
            </button>
        </nav>
    </div>
</template>
