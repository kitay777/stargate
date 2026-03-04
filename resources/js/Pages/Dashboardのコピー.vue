<template>
    <AppLayout title="Dashboard">
        <div class="max-w-6xl mx-auto p-4 pt-2">
<h2 class="text-sm font-bold text-white/80 mb-3 flex items-center gap-2">
  <span class="animate-pulse text-red-500">●</span>
  現在ライブ配信中
</h2>

            <!-- ✅ 配信がある場合 -->
 <div
  v-if="streamingRoomDetails.length > 0"
  class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-8"
>
                <div
                    v-for="room in streamingRoomDetails"
                    :key="room.id"
                    class="flex items-center gap-2 p-2 bg-white rounded shadow-sm hover:shadow-md transition"
                >
                    {{ getStreamingRoomByProduct(room) ? "LIVE" : "OFFLINE" }}
                    <img
                        :src="room.image_path || '/images/no-image.png'"
                        alt="room thumb"
                        class="w-12 h-12 object-cover rounded border"
                    />

                    <div class="flex-1 min-w-0">
                        <div
                            class="text-sm font-semibold text-black truncate leading-snug"
                        >
                            {{ room.name }}
                        </div>
                        <div
                            class="text-xs text-gray-500 truncate leading-none"
                        >
                            by {{ room.user?.name || "不明" }}
                        </div>
                    </div>

                    <Link
                        :href="`/viewer/${room.id}`"
                        class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 whitespace-nowrap"
                    >
                        ▶︎
                    </Link>
                </div>
            </div>

            <!-- ✅ 配信がない場合 -->
            <div v-else class="text-sm text-gray-500">
                現在ライブ配信はありません。
            </div>
        </div>

        <div
            v-for="user in uniqueUsers"
            :key="user.id"
            class="flex flex-col items-center text-xs text-white-700 min-w-fit"
        ></div>
        <!-- 📂 カテゴリー一覧（横スクロール＆選択可能） -->
        <div class="hidden border-green-500 border-red-500"></div>
        <!-- 📂 カテゴリー一覧（横スクロール・テキスト） -->
        <div style="margin-bottom: 20px">
            <div class="overflow-x-auto">
                <div
                    class="flex flex-nowrap gap-4 text-sm text-black whitespace-nowrap"
                >
                    <span
                        @click="selectCategory(null)"
                        :class="
                            filters.category_id
                                ? 'cursor-pointer hover:underline'
                                : 'font-bold underline'
                        "
                    >
                        全て
                    </span>
                    <span
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="selectCategory(cat.id)"
                        :class="[
                            'cursor-pointer',
                            filters.category_id == cat.id
                                ? 'font-bold underline'
                                : 'hover:underline',
                        ]"
                    >
                        {{ cat.name }}
                    </span>
                </div>
            </div>
        </div>

        <div>
            <div class="overflow-x-auto">
                <div class="flex flex-nowrap gap-2">
                    <div
                        v-for="user in uniqueUsers"
                        :key="user.id"
                        class="flex flex-col items-center text-xs text-white-700 min-w-fit"
                    >
                        <a :href="route('users.show', user.id)">
                            <img
                                v-if="user.profile_photo_path"
                                :src="
                                    user.profile_photo_path
                                        ? `/storage/${user.profile_photo_path}`
                                        : '/images/default-profile.png'
                                "
                                alt="顔写真"
                                class="w-10 h-10 rounded-full object-cover border"
                                :class="
                                    user.streaming
                                        ? 'border-green-500'
                                        : 'border-red-500'
                                "
                            />
                            <img
                                v-else
                                src="/images/default-profile.png"
                                alt="デフォルト"
                                class="w-10 h-10 rounded-full object-cover border"
                            />
                        </a>
                        <span
                            class="mt-0.5 truncate max-w-[60px] text-center"
                            >{{ user.name }}</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔍 検索フォーム -->
        <form
            @submit.prevent="searchRooms"
            class="mb-6 flex gap-2 items-center"
        >
            <input
                v-model="filters.search"
                type="text"
                placeholder="ルーム名で検索"
                class="border px-3 py-1 rounded w-64 text-white"
            />
            <button
                type="submit"
                class="ml-2 px-3 py-1 bg-blue-500 text-white rounded"
            >
                検索
            </button>
        </form>

        <!-- 📂 カテゴリ別ルーム一覧 -->
        <div v-for="category in categories" :key="category.id" class="mb-10">
            <h2
                class="text-2xl font-bold text-black mb-3 flex items-center gap-2"
            >
                <span>📂</span>
                {{ category.name }}
            </h2>

            <ul
                class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-[1px]"
            >
                <li
                    v-for="room in categoryRooms[category.id].data"
                    :key="room.id"
                    class="bg-black text-white border border-black rounded-xl flex flex-col overflow-hidden"
                >
                    <!-- 📷 画像 -->
                    <a :href="`/viewer/${room.id}`">
                        <img
                            v-if="room.image_path"
                            :src="`/storage/${room.image_path}`"
                            alt="ルーム画像"
                            class="w-full aspect-square object-cover rounded-xl"
                        />
                        <div
                            v-else
                            class="w-full aspect-square bg-gray-700 rounded-xl flex items-center justify-center text-sm text-gray-300"
                        >
                            No Image
                        </div>
                    </a>

                    <!-- 👤 ユーザー情報 -->
                    <div class="flex items-center gap-2 px-2 py-1">
                        <a :href="route('users.show', room.user.id)">
                            <img
                                :src="
                                    room.user?.profile_photo_url ??
                                    '/images/default-profile.png'
                                "
                                alt="ユーザー画像"
                                class="w-8 h-8 rounded-full object-cover border shadow"
                                :class="
                                    room.streaming
                                        ? 'border-green-500'
                                        : 'border-red-500'
                                "
                            />
                        </a>
                        <span class="text-sm font-semibold truncate">{{
                            room.user?.name ?? "不明ユーザー"
                        }}</span>
                    </div>

                    <!-- ℹ️ ルーム情報 -->
                    <div class="text-left px-2 pb-2">
                        <p class="text-base font-bold truncate">
                            {{ room.name }}
                        </p>
                        <p class="text-white/70 text-xs truncate">
                            📅 {{ formatDate(room.start) }}
                        </p>
                        <p class="text-white/70 text-xs truncate">
                            📂 {{ room.category?.name ?? "未分類" }}
                        </p>
                    </div>

                    <!-- ⭐️ いいね -->
                    <form @submit.prevent="likeRoom(room)" class="px-2 pb-2">
                        <button
                            type="submit"
                            class="flex items-center gap-1 text-sm"
                        >
                            <span
                                :class="
                                    Array.isArray(room.liked_by) &&
                                    room.liked_by.length > 0
                                        ? 'text-red-400'
                                        : 'text-white'
                                "
                            >
                                {{
                                    Array.isArray(room.liked_by) &&
                                    room.liked_by.length > 0
                                        ? "⭐️"
                                        : "⭐︎"
                                }}
                            </span>
                            <span>{{ room.liked_by_count }}</span>
                        </button>
                    </form>
                </li>
            </ul>

            <!-- 🔁 ページネーション -->
            <div class="flex gap-2 mt-2 justify-center">
                <button
                    v-if="categoryRooms[category.id].prev_page_url"
                    @click="
                        goToCategoryPage(
                            category.id,
                            categoryRooms[category.id].current_page - 1
                        )
                    "
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-white"
                >
                    ← 前へ
                </button>
                <button
                    v-if="categoryRooms[category.id].next_page_url"
                    @click="
                        goToCategoryPage(
                            category.id,
                            categoryRooms[category.id].current_page + 1
                        )
                    "
                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-white"
                >
                    次へ →
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, ref, onMounted } from "vue";
import { router, Head, Link } from "@inertiajs/vue3";
const rooms = ref([]); // WebRTCサーバーから取得した配信中room一覧
const displayMode = ref("grid"); // 初期状態はグリッド（2列）

const props = defineProps({
    products: Object,
    streamingRooms: Array,
    categories: Array,
    categoryRooms: Object,
    filters: Object,
});

const products = ref(props.products);

async function fetchRoomList() {
    try {
        const res = await fetch("https://moon.timesfun.net:8443/status");
        const json = await res.json();
        console.log("🌐 streamingRooms:", json); // ← ここで構造確認
        rooms.value = json;
    } catch (e) {
        console.error("❌ Room一覧取得失敗:", e);
    }
}

onMounted(() => {
    fetchRoomList();
    fetchProducts();
    fetchStreamingRoomsInfo();
    setInterval(() => {
        fetchRoomList();
        fetchProducts(); // ✅ 5秒ごとに再取得
        fetchStreamingRoomsInfo();
    }, 5000);
});

const streamingRoomDetails = ref([]);

async function fetchStreamingRoomsInfo() {
    try {
        const res = await fetch("https://moon.timesfun.net:8443/status");
        const rawRooms = await res.json();
        rooms.value = rawRooms;

        // Laravel側に一括問い合わせ
        const roomIds = rawRooms.map((r) => r.room); // ["30", "31", ...]
        const roomRes = await axios.post("/api/rooms/streaming-details", {
            ids: roomIds,
        });
        streamingRoomDetails.value = roomRes.data;
    } catch (e) {
        console.error("❌ 配信ルーム情報取得失敗:", e);
    }
}
// ✅ 各商品の配信情報を判定
const getStreamingRoomByProduct = (product) => {
    if (!product.room) return null; // 予約されてない商品
    return rooms.value.find(
        (room) =>
            String(room.room) === String(product.room.id) && room.streaming
    );
};

function formatDate(date) {
    if (!date) return "";
    const d = new Date(date);
    return `${d.getFullYear()}年${
        d.getMonth() + 1
    }月${d.getDate()}日 ${d.getHours()}時${String(d.getMinutes()).padStart(
        2,
        "0"
    )}分`;
}
async function fetchProducts() {
    try {
        const res = await fetch("/products-all-api"); // 🔥 ここはAPIエンドポイント
        const data = await res.json();
        products.value = data.products; // ✅ 最新データに置き換える！
    } catch (e) {
        console.error("❌ 商品一覧取得失敗:", e);
    }
}

const filters = ref({ ...props.filters });

function mergeStreamingInfo() {
    const liveRoomIds = rooms.value.map((r) => String(r.room));

    for (const categoryId in props.categoryRooms) {
        const roomList = props.categoryRooms[categoryId].data;
        roomList.forEach((room) => {
            // ✅ true でも false でも毎回書き換える
            Object.assign(room, {
                streaming: liveRoomIds.includes(String(room.id)),
            });
        });
    }
}

function goToCategoryPage(categoryId, pageNumber) {
    router.get(
        route("dashboard"),
        {
            ...filters.value,
            [`page_category_${categoryId}`]: pageNumber,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
}

function searchRooms() {
    router.get(
        route("dashboard"),
        { ...filters.value },
        { preserveState: true }
    );
}

function likeRoom(room) {
    if (!room.liked_by) {
        alert("ログインしてください");
        return;
    }

    router.post(
        route("rooms.like", room.id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                if (room.liked_by.length > 0) {
                    room.liked_by = [];
                    room.liked_by_count--;
                } else {
                    room.liked_by = [{}];
                    room.liked_by_count++;
                }
            },
        }
    );
}

function selectCategory(id) {
    filters.value.category_id = id;
    searchRooms();
}

const uniqueUsers = computed(() => {
    const usersMap = new Map();

    for (const categoryId in props.categoryRooms) {
        const rooms = props.categoryRooms[categoryId].data;
        rooms.forEach((room) => {
            const user = room.user;
            if (user?.id) {
                // ✅ 既に登録済みなら、どちらかが配信中なら true
                if (usersMap.has(user.id)) {
                    const existing = usersMap.get(user.id);
                    existing.streaming ||= room.streaming === true;
                } else {
                    // ✅ user に streaming フラグを付けて登録
                    usersMap.set(user.id, {
                        ...user,
                        streaming: room.streaming === true,
                    });
                }
            }
        });
    }

    return Array.from(usersMap.values());
});
</script>
