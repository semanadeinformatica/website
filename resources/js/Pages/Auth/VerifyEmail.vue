<script setup lang="ts">
import { computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import route from "ziggy-js";
import CardLayout from "@/Layouts/CardLayout.vue";

interface Props {
    status: string;
}

const props = defineProps<Props>();

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent",
);
</script>

<template>
    <CardLayout title="Verificar email">
        <div class="mb-4 text-sm text-gray-600">
            Before continuing, could you verify your email address by clicking
            on the link we just emailed to you? If you didn't receive the email,
            we will gladly send you another.
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 text-sm font-medium text-green-600"
        >
            A new verification link has been sent to the email address you
            provided in your profile settings.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </PrimaryButton>

                <div>
                    <Link
                        :href="route('profile.show')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Edit Profile</Link
                    >

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="ml-2 rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </form>
    </CardLayout>
</template>
