<script setup lang="ts">
import { computed, type Component } from "vue";
import { Link } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { ChevronDown } from "@lucide/vue";

export interface PillDropdownItem {
    id?: string | number;
    label: string;
    sublabel?: string;
    href?: string;
    icon?: Component;
    trailingIcon?: Component;
    active?: boolean;
    disabled?: boolean;
    danger?: boolean;
    divider?: boolean;
    method?: "get" | "post";
    as?: "button" | "a";
    onClick?: () => void;
}

export interface PillDropdownConfig {
    align?: "left" | "right" | "center";
    width?: string | number;
    contentClasses?: string;
    items?: PillDropdownItem[];
}

export interface PillAvatarConfig {
    src?: string;
    alt?: string;
}

export interface PillImageConfig {
    src: string;
    alt?: string;
    class?: string;
}

export interface PillOption {
    id: string | number;
    label?: string;
    sublabel?: string;
    icon?: Component;
    trailingIcon?: Component;
    avatar?: string | PillAvatarConfig;
    image?: string | PillImageConfig;
    count?: number | string;
    href?: string;
    disabled?: boolean;
    active?: boolean;
    ariaLabel?: string;
    dropdown?: PillDropdownConfig;
}

interface Props {
    items?: PillOption[];
    modelValue?: string | number | null;
    size?: "sm" | "md" | "lg";
    as?: "nav" | "div";
    wrap?: boolean;
    containerClass?: string;
    itemClass?: string;
}

interface Emits {
    (event: "update:modelValue", value: string | number | null): void;
    (event: "select", item: PillOption): void;
}

const props = withDefaults(defineProps<Props>(), {
    items: () => [],
    modelValue: undefined,
    size: "md",
    as: "div",
    wrap: true,
    containerClass: "",
    itemClass: "",
});

const emit = defineEmits<Emits>();

const isItemActive = (item: PillOption) => {
    if (item.active !== undefined) return item.active;
    if (props.modelValue !== undefined) return props.modelValue === item.id;
    return false;
};

const handleSelect = (item: PillOption) => {
    if (item.disabled) return;
    emit("update:modelValue", item.id);
    emit("select", item);
};

const isExternalOrAnchor = (href?: string) => {
    if (!href) return false;
    return (
        href.startsWith("#") || href.startsWith("/#") || href.startsWith("http")
    );
};

// Helper methods for avatar and image
const getAvatarSrc = (item: PillOption): string | undefined => {
    if (!item.avatar) return undefined;
    return typeof item.avatar === "string" ? item.avatar : item.avatar.src;
};

const getAvatarAlt = (item: PillOption): string => {
    if (!item.avatar) return "";
    return typeof item.avatar === "string"
        ? (item.label ?? "")
        : (item.avatar.alt ?? item.label ?? "");
};

const getImageSrc = (item: PillOption): string | undefined => {
    if (!item.image) return undefined;
    return typeof item.image === "string" ? item.image : item.image.src;
};

const getImageAlt = (item: PillOption): string => {
    if (!item.image) return "";
    return typeof item.image === "string"
        ? (item.label ?? "")
        : (item.image.alt ?? item.label ?? "");
};

const getImageClass = (item: PillOption): string => {
    if (typeof item.image === "object" && item.image.class) {
        return item.image.class;
    }
    return "h-4 w-auto object-contain";
};

const isAvatarOnly = (item: PillOption): boolean => {
    return Boolean(getAvatarSrc(item) && !item.label && !item.sublabel);
};

const isImageOnly = (item: PillOption): boolean => {
    return Boolean(getImageSrc(item) && !item.label && !item.sublabel);
};

const isSingleAvatarOnly = computed(() => {
    return props.items.length === 1 && isAvatarOnly(props.items[0]);
});

const sizeClasses = {
    sm: "px-3 py-1.5 text-xs gap-1.5",
    md: "px-4 py-2 text-xs sm:text-sm gap-2",
    lg: "px-5 py-2.5 text-sm sm:text-base gap-2.5",
};

const imageOnlyPadding = {
    sm: "px-2.5 py-1",
    md: "px-3 py-1.5",
    lg: "px-4 py-2",
};

const avatarSizes = {
    sm: "h-7 w-7",
    md: "h-8 w-8",
    lg: "h-10 w-10",
};

const avatarInlineSizes = {
    sm: "h-5 w-5",
    md: "h-6 w-6",
    lg: "h-7 w-7",
};

const iconSizes = {
    sm: 14,
    md: 16,
    lg: 18,
};

const getItemClass = (item: PillOption, activeState?: boolean) => {
    const isActive =
        activeState !== undefined ? activeState : isItemActive(item);

    if (isAvatarOnly(item)) {
        return [
            "group inline-flex shrink-0 items-center justify-center rounded-full p-0 bg-transparent border-0 shadow-none transition-all duration-200 focus:outline-none cursor-pointer",
            isActive ? "scale-105" : "hover:scale-105",
            item.disabled
                ? "cursor-not-allowed opacity-40 pointer-events-none"
                : "",
            props.itemClass,
        ];
    }

    let paddingAndTextClass = sizeClasses[props.size];
    if (isImageOnly(item)) {
        paddingAndTextClass = imageOnlyPadding[props.size];
    }

    return [
        "pill-item font-medium transition-all duration-200 cursor-pointer",
        paddingAndTextClass,
        isActive ? "pill-item-active" : "",
        item.disabled
            ? "cursor-not-allowed opacity-40 hover:bg-transparent pointer-events-none"
            : "",
        props.itemClass,
    ];
};
</script>

<template>
    <component
        :is="as"
        class="pill-container justify-center"
        :class="[
            isSingleAvatarOnly ? 'p-1' : 'p-1.5',
            wrap ? 'flex-wrap' : 'flex-nowrap',
            containerClass,
        ]"
        aria-label="Selector"
    >
        <slot name="leading" />

        <template v-for="item in items" :key="item.id">
            <!-- 1. Dropdown Item Mode -->
            <Dropdown
                v-if="item.dropdown"
                :align="item.dropdown.align ?? 'center'"
                :width="item.dropdown.width ?? '48'"
                :content-classes="item.dropdown.contentClasses ?? 'p-1.5'"
            >
                <template #trigger="{ open }">
                    <button
                        type="button"
                        :disabled="item.disabled"
                        :aria-label="item.ariaLabel ?? item.label"
                        :class="getItemClass(item, open || isItemActive(item))"
                    >
                        <slot
                            name="item"
                            :item="item"
                            :active="open || isItemActive(item)"
                            :select="() => handleSelect(item)"
                        >
                            <!-- Avatar Only -->
                            <template v-if="isAvatarOnly(item)">
                                <img
                                    :src="getAvatarSrc(item)"
                                    :alt="getAvatarAlt(item)"
                                    :class="[
                                        avatarSizes[size],
                                        'shrink-0 rounded-full bg-neutral-800 object-cover transition-all duration-200',
                                        open || isItemActive(item)
                                            ? 'shadow-[0_0_12px_rgba(255,255,255,0.35)] ring-2 ring-white brightness-110'
                                            : 'ring-1 ring-white/20 group-hover:ring-2 group-hover:ring-white/60 group-hover:brightness-110',
                                    ]"
                                />
                            </template>

                            <!-- Normal Item with/without Avatar/Icon/Label -->
                            <template v-else>
                                <img
                                    v-if="getAvatarSrc(item)"
                                    :src="getAvatarSrc(item)"
                                    :alt="getAvatarAlt(item)"
                                    :class="[
                                        avatarInlineSizes[size],
                                        'shrink-0 rounded-full object-cover ring-1 ring-white/20',
                                    ]"
                                />
                                <component
                                    :is="item.icon"
                                    v-else-if="item.icon"
                                    :size="iconSizes[size]"
                                    class="shrink-0"
                                />
                                <span v-if="item.label">{{ item.label }}</span>
                                <span
                                    v-if="item.sublabel"
                                    class="text-[11px] font-normal text-neutral-400"
                                >
                                    {{ item.sublabel }}
                                </span>
                                <span
                                    v-if="item.count !== undefined"
                                    class="ml-0.5 rounded-full bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold text-neutral-300 transition-colors"
                                >
                                    {{ item.count }}
                                </span>
                                <component
                                    :is="item.trailingIcon"
                                    v-if="item.trailingIcon"
                                    :size="iconSizes[size]"
                                    class="shrink-0"
                                />
                                <ChevronDown
                                    v-else-if="item.label"
                                    :size="size === 'sm' ? 12 : 14"
                                    class="shrink-0 text-neutral-400 transition-transform duration-200"
                                    :class="{ 'rotate-180 text-white': open }"
                                />
                            </template>
                        </slot>
                    </button>
                </template>

                <template #content="{ close }">
                    <slot
                        :name="`dropdown-${item.id}`"
                        :item="item"
                        :close="close"
                    >
                        <slot name="dropdown" :item="item" :close="close">
                            <!-- Pre-configured Dropdown Items List -->
                            <div v-if="item.dropdown.items" class="space-y-0.5">
                                <template
                                    v-for="(dItem, dIdx) in item.dropdown.items"
                                    :key="dItem.id ?? `${item.id}-item-${dIdx}`"
                                >
                                    <hr
                                        v-if="dItem.divider"
                                        class="my-1 border-white/6"
                                    />
                                    <DropdownLink
                                        v-if="dItem.href"
                                        :href="dItem.href"
                                        :method="dItem.method"
                                        :as="dItem.as"
                                        :danger="dItem.danger"
                                        @click="
                                            () => {
                                                dItem.onClick?.();
                                                close();
                                            }
                                        "
                                    >
                                        <span class="flex items-center gap-2">
                                            <component
                                                :is="dItem.icon"
                                                v-if="dItem.icon"
                                                :size="16"
                                                class="shrink-0 text-neutral-400"
                                            />
                                            <span class="flex-1">{{
                                                dItem.label
                                            }}</span>
                                            <component
                                                :is="dItem.trailingIcon"
                                                v-if="dItem.trailingIcon"
                                                :size="14"
                                                class="shrink-0 text-neutral-500 opacity-0 transition-all duration-150 group-hover:translate-x-0.5 group-hover:text-white group-hover:opacity-100"
                                            />
                                        </span>
                                    </DropdownLink>
                                    <button
                                        v-else
                                        type="button"
                                        class="group flex w-full cursor-pointer items-center justify-between gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-medium transition-colors hover:bg-white/8 hover:text-white focus:outline-none"
                                        :class="
                                            dItem.danger
                                                ? 'text-red-400 hover:bg-red-500/10 hover:text-red-300'
                                                : 'text-neutral-300'
                                        "
                                        @click="
                                            () => {
                                                dItem.onClick?.();
                                                close();
                                            }
                                        "
                                    >
                                        <span class="flex items-center gap-2">
                                            <component
                                                :is="dItem.icon"
                                                v-if="dItem.icon"
                                                :size="16"
                                                class="shrink-0 text-neutral-400"
                                            />
                                            <span class="flex-1">{{
                                                dItem.label
                                            }}</span>
                                        </span>
                                        <component
                                            :is="dItem.trailingIcon"
                                            v-if="dItem.trailingIcon"
                                            :size="14"
                                            class="shrink-0 text-neutral-500 opacity-0 transition-all duration-150 group-hover:translate-x-0.5 group-hover:text-white group-hover:opacity-100"
                                        />
                                    </button>
                                </template>
                            </div>
                        </slot>
                    </slot>
                </template>
            </Dropdown>

            <!-- 2. External / Anchor Link Mode -->
            <a
                v-else-if="item.href && isExternalOrAnchor(item.href)"
                :href="item.href"
                :aria-label="item.ariaLabel ?? item.label"
                :class="getItemClass(item)"
                @click="handleSelect(item)"
            >
                <slot
                    name="item"
                    :item="item"
                    :active="isItemActive(item)"
                    :select="() => handleSelect(item)"
                >
                    <!-- Image -->
                    <img
                        v-if="getImageSrc(item)"
                        :src="getImageSrc(item)"
                        :alt="getImageAlt(item)"
                        :class="getImageClass(item)"
                    />
                    <!-- Avatar Only -->
                    <img
                        v-else-if="isAvatarOnly(item)"
                        :src="getAvatarSrc(item)"
                        :alt="getAvatarAlt(item)"
                        :class="[
                            avatarSizes[size],
                            'shrink-0 rounded-full bg-neutral-800 object-cover transition-all duration-200',
                            isItemActive(item)
                                ? 'shadow-[0_0_12px_rgba(255,255,255,0.35)] ring-2 ring-white brightness-110'
                                : 'ring-1 ring-white/20 group-hover:ring-2 group-hover:ring-white/60 group-hover:brightness-110',
                        ]"
                    />
                    <!-- Avatar with label -->
                    <img
                        v-else-if="getAvatarSrc(item)"
                        :src="getAvatarSrc(item)"
                        :alt="getAvatarAlt(item)"
                        :class="[
                            avatarInlineSizes[size],
                            'shrink-0 rounded-full object-cover ring-1 ring-white/20',
                        ]"
                    />
                    <!-- Icon -->
                    <component
                        :is="item.icon"
                        v-else-if="item.icon"
                        :size="iconSizes[size]"
                        class="shrink-0"
                    />
                    <span v-if="item.label">{{ item.label }}</span>
                    <span
                        v-if="item.sublabel"
                        class="text-[11px] font-normal text-neutral-400"
                    >
                        {{ item.sublabel }}
                    </span>
                    <span
                        v-if="item.count !== undefined"
                        class="ml-0.5 rounded-full bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold text-neutral-300 transition-colors"
                        :class="
                            isItemActive(item)
                                ? 'bg-white/20 text-white'
                                : 'text-neutral-400'
                        "
                    >
                        {{ item.count }}
                    </span>
                    <component
                        :is="item.trailingIcon"
                        v-if="item.trailingIcon"
                        :size="iconSizes[size]"
                        class="shrink-0"
                    />
                </slot>
            </a>

            <!-- 3. SPA Link Mode -->
            <Link
                v-else-if="item.href"
                :href="item.href"
                :aria-label="item.ariaLabel ?? item.label"
                :class="getItemClass(item)"
                @click="handleSelect(item)"
            >
                <slot
                    name="item"
                    :item="item"
                    :active="isItemActive(item)"
                    :select="() => handleSelect(item)"
                >
                    <!-- Image -->
                    <img
                        v-if="getImageSrc(item)"
                        :src="getImageSrc(item)"
                        :alt="getImageAlt(item)"
                        :class="getImageClass(item)"
                    />
                    <!-- Avatar Only -->
                    <img
                        v-else-if="isAvatarOnly(item)"
                        :src="getAvatarSrc(item)"
                        :alt="getAvatarAlt(item)"
                        :class="[
                            avatarSizes[size],
                            'shrink-0 rounded-full bg-neutral-800 object-cover transition-all duration-200',
                            isItemActive(item)
                                ? 'shadow-[0_0_12px_rgba(255,255,255,0.35)] ring-2 ring-white brightness-110'
                                : 'ring-1 ring-white/20 group-hover:ring-2 group-hover:ring-white/60 group-hover:brightness-110',
                        ]"
                    />
                    <!-- Avatar with label -->
                    <img
                        v-else-if="getAvatarSrc(item)"
                        :src="getAvatarSrc(item)"
                        :alt="getAvatarAlt(item)"
                        :class="[
                            avatarInlineSizes[size],
                            'shrink-0 rounded-full object-cover ring-1 ring-white/20',
                        ]"
                    />
                    <!-- Icon -->
                    <component
                        :is="item.icon"
                        v-else-if="item.icon"
                        :size="iconSizes[size]"
                        class="shrink-0"
                    />
                    <span v-if="item.label">{{ item.label }}</span>
                    <span
                        v-if="item.sublabel"
                        class="text-[11px] font-normal text-neutral-400"
                    >
                        {{ item.sublabel }}
                    </span>
                    <span
                        v-if="item.count !== undefined"
                        class="ml-0.5 rounded-full bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold text-neutral-300 transition-colors"
                        :class="
                            isItemActive(item)
                                ? 'bg-white/20 text-white'
                                : 'text-neutral-400'
                        "
                    >
                        {{ item.count }}
                    </span>
                    <component
                        :is="item.trailingIcon"
                        v-if="item.trailingIcon"
                        :size="iconSizes[size]"
                        class="shrink-0"
                    />
                </slot>
            </Link>

            <!-- 4. Button Mode -->
            <button
                v-else
                type="button"
                :disabled="item.disabled"
                :aria-label="item.ariaLabel ?? item.label"
                :class="getItemClass(item)"
                @click="handleSelect(item)"
            >
                <slot
                    name="item"
                    :item="item"
                    :active="isItemActive(item)"
                    :select="() => handleSelect(item)"
                >
                    <!-- Image -->
                    <img
                        v-if="getImageSrc(item)"
                        :src="getImageSrc(item)"
                        :alt="getImageAlt(item)"
                        :class="getImageClass(item)"
                    />
                    <!-- Avatar Only -->
                    <img
                        v-else-if="isAvatarOnly(item)"
                        :src="getAvatarSrc(item)"
                        :alt="getAvatarAlt(item)"
                        :class="[
                            avatarSizes[size],
                            'shrink-0 rounded-full bg-neutral-800 object-cover transition-all duration-200',
                            isItemActive(item)
                                ? 'shadow-[0_0_12px_rgba(255,255,255,0.35)] ring-2 ring-white brightness-110'
                                : 'ring-1 ring-white/20 group-hover:ring-2 group-hover:ring-white/60 group-hover:brightness-110',
                        ]"
                    />
                    <!-- Avatar with label -->
                    <img
                        v-else-if="getAvatarSrc(item)"
                        :src="getAvatarSrc(item)"
                        :alt="getAvatarAlt(item)"
                        :class="[
                            avatarInlineSizes[size],
                            'shrink-0 rounded-full object-cover ring-1 ring-white/20',
                        ]"
                    />
                    <!-- Icon -->
                    <component
                        :is="item.icon"
                        v-else-if="item.icon"
                        :size="iconSizes[size]"
                        class="shrink-0"
                    />
                    <span v-if="item.label">{{ item.label }}</span>
                    <span
                        v-if="item.sublabel"
                        class="text-[11px] font-normal text-neutral-400"
                    >
                        {{ item.sublabel }}
                    </span>
                    <span
                        v-if="item.count !== undefined"
                        class="ml-0.5 rounded-full bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold text-neutral-300 transition-colors"
                        :class="
                            isItemActive(item)
                                ? 'bg-white/20 text-white'
                                : 'text-neutral-400'
                        "
                    >
                        {{ item.count }}
                    </span>
                    <component
                        :is="item.trailingIcon"
                        v-if="item.trailingIcon"
                        :size="iconSizes[size]"
                        class="shrink-0"
                    />
                </slot>
            </button>
        </template>

        <!-- Default slot (when items is empty or alongside items) -->
        <slot />

        <slot name="trailing" />
    </component>
</template>
