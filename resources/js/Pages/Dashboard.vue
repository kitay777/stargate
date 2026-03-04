<template>
  <AppLayout title="Dashboard">
    <template #default>
      <div class="max-w-6xl mx-auto px-4 py-12">

        <!-- 🟦 ヘッダー（カードに乗せる） -->
        <div class="flex justify-center -mb-12 relative z-10">
                <div class="flex justify-center mb-8">
                    <img
                        src="/assets/imgs/dashboard_icon.png"
                        alt="ルーム作成"
                        class="w-48 h-auto drop-shadow-lg"
                    />
                </div>
        </div>

        <!-- 🟨 メインカード -->
        <div
          class="bg-white/10 backdrop-blur
                 rounded-2xl shadow-xl
                 p-6 pt-16 space-y-10"
        >

          <!-- ===================== -->
          <!-- 📡 現在ライブ配信中 -->
          <!-- ===================== -->
          <section>
            <h2 class="text-sm font-bold text-white/80 mb-3 flex items-center gap-2">
              <span class="animate-pulse text-red-500">●</span>
              現在ライブ配信中
            </h2>

            <div
              v-if="streamingRoomDetails.length > 0"
              class="grid grid-cols-1 sm:grid-cols-2 gap-3"
            >
              <div
                v-for="room in streamingRoomDetails"
                :key="room.id"
                class="flex items-center gap-3 p-3
                       bg-white/10 backdrop-blur
                       rounded-xl
                       hover:shadow-lg hover:scale-[1.01]
                       transition"
              >
                <!-- LIVE バッジ -->
                <span
                  class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                  :class="getStreamingRoomByProduct(room)
                    ? 'bg-red-500 text-white'
                    : 'bg-gray-500 text-white/70'"
                >
                  {{ getStreamingRoomByProduct(room) ? 'LIVE' : 'OFFLINE' }}
                </span>

                <img
                  :src="room.image_path || '/images/no-image.png'"
                  class="w-12 h-12 rounded object-cover"
                />

                <div class="flex-1 min-w-0">
                  <div class="text-sm font-semibold text-white truncate">
                    {{ room.name }}
                  </div>
                  <div class="text-xs text-white/60 truncate">
                    by {{ room.user?.name || '不明' }}
                  </div>
                </div>

                <Link
                  :href="`/viewer/${room.id}`"
                  class="text-xs px-3 py-1 rounded
                         bg-blue-500 text-white
                         hover:bg-blue-600"
                >
                  ▶︎
                </Link>
              </div>
            </div>

            <div v-else class="text-sm text-white/50">
              現在ライブ配信はありません。
            </div>
          </section>

          <!-- ===================== -->
          <!-- 👤 配信者一覧 -->
          <!-- ===================== -->
          <!--
          <section>
            <div class="overflow-x-auto">
              <div class="flex gap-3">
                <div
                  v-for="user in uniqueUsers"
                  :key="user.id"
                  class="flex flex-col items-center text-xs min-w-fit"
                >
                  <a :href="route('users.show', user.id)">
                    <img
                      :src="user.profile_photo_path
                        ? `/storage/${user.profile_photo_path}`
                        : '/images/default-profile.png'"
                      class="w-10 h-10 rounded-full object-cover border-2"
                      :class="user.streaming
                        ? 'border-green-400 shadow-[0_0_8px_rgba(34,197,94,0.8)]'
                        : 'border-white/30'"
                    />
                  </a>
                  <span class="mt-1 max-w-[60px] truncate text-white/80">
                    {{ user.name }}
                  </span>
                </div>
              </div>
            </div>
          </section>
-->
          <!-- ===================== -->
          <!-- 🔍 検索 -->
          <!-- ===================== -->
          <section>
            <form
              @submit.prevent="searchRooms"
              class="flex gap-2 items-center
                     bg-white/10 backdrop-blur
                     rounded-xl p-3"
            >
              <input
                v-model="filters.search"
                placeholder="ルーム名で検索"
                class="flex-1 bg-transparent border border-white/30
                       rounded px-3 py-1 text-white
                       focus:outline-none focus:border-blue-400"
              />
              <button
                type="submit"
                class="px-4 py-1 rounded bg-blue-500 text-white"
              >
                検索
              </button>
            </form>
          </section>

          <!-- ===================== -->
          <!-- 📂 カテゴリ別ルーム -->
          <!-- ===================== -->
          <section
            v-for="category in categories"
            :key="category.id"
            class="space-y-4"
          >
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              📂 {{ category.name }}
            </h2>

            <ul
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3"
            >
              <li
                v-for="room in categoryRooms[category.id].data"
                :key="room.id"
                class="bg-white/10 backdrop-blur
                       rounded-2xl overflow-hidden
                       hover:scale-[1.02] hover:shadow-xl
                       transition"
              >
                <a :href="`/viewer/${room.id}`">
                  <img
                    v-if="room.image_path"
                    :src="`/storage/${room.image_path}`"
                    class="w-full aspect-square object-cover"
                  />
                  <div
                    v-else
                    class="w-full aspect-square bg-gray-700
                           flex items-center justify-center text-white/50"
                  >
                    No Image
                  </div>
                </a>

                <div class="p-2 space-y-1">
                  <p class="text-sm font-bold text-white truncate">
                    {{ room.name }}
                  </p>
                  <p class="text-xs text-white/60 truncate">
                    📅 {{ formatDate(room.start) }}
                  </p>
                  <p class="text-xs text-white/60 truncate">
                    📂 {{ room.category?.name ?? '未分類' }}
                  </p>
                </div>
              </li>
            </ul>
          </section>

        </div>
      </div>
    </template>
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
    //fetchProducts();
    fetchStreamingRoomsInfo();
    setInterval(() => {
        fetchRoomList();
        //fetchProducts(); // ✅ 5秒ごとに再取得
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
