<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationView from '@/Pages/Profile/Partials/UpdateProfileInformationView.vue';
import Sidebar from '@/Layouts/Sidebar.vue';


defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
    profile: Object, // 🔹 `profile` を受け取る
    user: Object, // 🔹 `profile` を受け取る
});
</script>

<template>
    <AppLayout title="Profile">
        <template #sidebar>
            <Sidebar />
        </template>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                プロファイル
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationView 
                        :user="user"
                        :profile="profile"  />
                </div>
            </div>
        </div>

    </AppLayout>
</template>
