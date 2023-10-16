import type { VNode } from "vue";

export type View = {
    label: string;
    component: VNode;
};

export type TabId = string;

export type Tabs = Record<TabId, View>;
