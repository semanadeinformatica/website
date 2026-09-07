<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";
import PillSelector, {
    type PillOption,
} from "@/Components/UI/PillSelector.vue";
import { User, ShieldCheck, Settings, ArrowLeft } from "@lucide/vue";
import type Session from "@/Types/Session";

interface Props {
    confirmsTwoFactorAuthentication: boolean;
    sessions: Session[];
}

const props = defineProps<Props>();
const page = usePage();

type ProfileTab = "profile" | "security" | "account";

const tabs = computed<PillOption[]>(() => {
    const list: PillOption[] = [];

    if (page.props.jetstream.canUpdateProfileInformation) {
        list.push({
            id: "profile",
            label: "Perfil",
            icon: User,
        });
    }

    if (
        page.props.jetstream.canUpdatePassword ||
        page.props.jetstream.canManageTwoFactorAuthentication
    ) {
        list.push({
            id: "security",
            label: "Segurança",
            icon: ShieldCheck,
        });
    }

    if (
        (props.sessions && props.sessions.length > 0) ||
        page.props.jetstream.hasAccountDeletionFeatures
    ) {
        list.push({
            id: "account",
            label: "Conta",
            icon: Settings,
        });
    }

    return list;
});

const getDefaultTab = (): ProfileTab => {
    const errors = (page.props.errors || {}) as Record<string, unknown>;
    if (errors.current_password || errors.password) {
        return "security";
    }
    return (tabs.value[0]?.id as ProfileTab) ?? "profile";
};

const activeTab = ref<ProfileTab>(getDefaultTab());

onMounted(() => {
    const hash = window.location.hash.replace("#", "");
    if (hash === "profile" || hash === "security" || hash === "account") {
        activeTab.value = hash as ProfileTab;
    }
});

watch(activeTab, (newTab) => {
    if (typeof window !== "undefined" && window.history?.replaceState) {
        window.history.replaceState(null, "", `#${newTab}`);
    }
});
</script>

<template>
    <AppLayout title="Definições de Perfil">
        <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex justify-center sm:mb-10">
                <PillSelector v-model="activeTab" :items="tabs" size="md">
                    <template #leading>
                        <Link
                            :href="route('profile.show')"
                            class="pill-item cursor-pointer gap-2 px-4 py-2 text-xs font-medium text-neutral-400 transition-all duration-200 hover:bg-white/8 hover:text-white active:scale-95 sm:text-sm"
                        >
                            <ArrowLeft :size="16" class="shrink-0" />
                            <span>Voltar ao perfil</span>
                        </Link>
                    </template>
                </PillSelector>
            </div>

            <div v-if="activeTab === 'profile'" class="space-y-6">
                <UpdateProfileInformationForm
                    v-if="$page.props.jetstream.canUpdateProfileInformation"
                    :user="$page.props.auth.user"
                />
            </div>

            <div v-else-if="activeTab === 'security'" class="space-y-6">
                <UpdatePasswordForm
                    v-if="$page.props.jetstream.canUpdatePassword"
                />

                <TwoFactorAuthenticationForm
                    v-if="
                        $page.props.jetstream.canManageTwoFactorAuthentication
                    "
                    :requires-confirmation="confirmsTwoFactorAuthentication"
                />
            </div>

            <div v-else-if="activeTab === 'account'" class="space-y-6">
                <LogoutOtherBrowserSessionsForm
                    v-if="sessions && sessions.length > 0"
                    :sessions="sessions"
                />

                <DeleteUserForm
                    v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                />
            </div>
        </div>
    </AppLayout>
</template>
