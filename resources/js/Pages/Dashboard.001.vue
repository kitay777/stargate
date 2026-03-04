<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Sidebar from '@/Layouts/Sidebar.vue';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

defineProps({
    users: Array,
    categories: Array,
});

// 🔹 いいね数 & お気に入り数の管理
const iineCounts = ref({});
const okiniiriCounts = ref({});
const likedUsers = ref(new Set());
const favoritedUsers = ref(new Set());
const userRefs = ref([]);

// 🔹 いいね数 & お気に入り数を取得
const fetchUserCounts = async (userId) => {
    if (iineCounts.value[userId] === undefined || okiniiriCounts.value[userId] === undefined) {
        try {
            const response = await axios.get(`/user-counts/${userId}`);
            iineCounts.value[userId] = response.data.iine_count ?? 0;
            okiniiriCounts.value[userId] = response.data.okiniiri_count ?? 0;

            if (response.data.liked) likedUsers.value.add(userId);
            if (response.data.favorited) favoritedUsers.value.add(userId);
        } catch (error) {
            console.error(`データ取得失敗 (ID: ${userId}):`, error);
            iineCounts.value[userId] = 0;
            okiniiriCounts.value[userId] = 0;
        }
    }
};

// 🔹 いいねを押したとき
const toggleIine = async (userId) => {
    try {
        const response = await axios.post('/iine', { targetid: userId });

        if (response.data.liked) {
            likedUsers.value.add(userId);
            iineCounts.value[userId] = (iineCounts.value[userId] ?? 0) + 1;
        } else {
            likedUsers.value.delete(userId);
            iineCounts.value[userId] = Math.max((iineCounts.value[userId] ?? 1) - 1, 0);
        }
        await fetchUserCounts(userId);
        
    } catch (error) {
        console.error("いいねの送信に失敗:", error.response?.data || error.message);
    }
};

// 🔹 お気に入りを押したとき
const toggleOkiniiri = async (userId) => {
    try {
        const response = await axios.post('/okiniiri', { targetid: userId });

        if (response.data.favorited) {
            favoritedUsers.value.add(userId);
            okiniiriCounts.value[userId] = (okiniiriCounts.value[userId] ?? 0) + 1;
        } else {
            favoritedUsers.value.delete(userId);
            okiniiriCounts.value[userId] = Math.max((okiniiriCounts.value[userId] ?? 1) - 1, 0);
        }
        await fetchUserCounts(userId);
    } catch (error) {
        console.error("お気に入りの送信に失敗:", error.response?.data || error.message);
    }
};

// 🔹 画面に表示されたら取得
onMounted(async () => {
    await nextTick();

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const userId = entry.target.dataset.id;
                fetchUserCounts(userId);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    userRefs.value.forEach(el => observer.observe(el));
});
</script>






<template>
    <AppLayout title="Dashboard">
        <template #sidebar>
            <Sidebar :categories="categories" />
        </template>

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
                ユーザーリスト
            </h2>
        </template>

        <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
            <Carousel 
                :breakpoints="{
                    640: { itemsToShow: 1 },
                    640: { itemsToShow: 1 }
                }"
                :wrap-around="false" 
                :contain="true"
                class="w-full overflow-hidden"
            >
                <Slide v-for="(user, index) in users" :key="user.id">
                    <div class="w-full flex justify-center">
                        <div 
                            class="bg-white shadow-lg rounded-lg w-full max-w-lg p-6"
                            ref="userRefs" 
                            :data-id="user.id"
                        >
                            <img v-if="user.profile_photo_url"
                                :src="user.profile_photo_url"
                                alt="プロフィール画像"
                                class="w-full h-96 object-cover rounded-lg">

                            <div class="text-center mt-4">
                                <a :href="`/user/profile/${user.id}`">
                                    <p class="font-semibold text-2xl">{{ user.name }}</p>
                                </a>
                                <a :href="`/chat/${user.id}`" class="block">
                                    <p class="text-gray-500 text-lg">{{ user.email }}</p>
                                </a>
                            </div>

                            <!-- 🔹 いいね数＆ボタン -->
                            <div class="grid grid-cols-4 gap-2 mt-2">
    <!-- いいねボタン -->
                                <div class="flex items-center justify-center space-x-1">
                                    <button @click="toggleIine(user.id)">
                                        <img 
                                            src="/assets/imgs/iine.png" 
                                            class="h-6 w-6" 
                                            :class="{ 'opacity-100': likedUsers.has(user.id), 'opacity-50': !likedUsers.has(user.id) }"
                                        >
                                    </button>
                                    <p class="text-sm text-gray-700">{{ iineCounts[user.id] ?? '0' }}</p>
                                </div>

                                <!-- お気に入りボタン -->
                                <div class="flex items-center justify-center space-x-1">
                                    <button @click="toggleOkiniiri(user.id)">
                                        <img 
                                            src="/assets/imgs/okiniiri.png" 
                                            class="h-6 w-6"
                                            :class="{ 'opacity-100': favoritedUsers.has(user.id), 'opacity-50': !favoritedUsers.has(user.id) }"
                                        >
                                    </button>
                                    <p class="text-sm text-gray-700">{{ okiniiriCounts[user.id] ?? '0' }}</p>
                                </div>

                                <!-- 余白 -->
                                <div class="p-1"></div>
                                <div class="p-1"></div>
                            </div>


                        </div>
                    </div>
                </Slide>

                <template #addons>
                    <Navigation />
                    <Pagination />
                </template>
            </Carousel>
        </div>
    </AppLayout>
</template>


