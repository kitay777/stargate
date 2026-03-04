<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Sidebar from '@/Layouts/Sidebar.vue';
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
});

const displayedUsers = ref([...props.users]);
const iineCounts = ref({});
const okiniiriCounts = ref({});
const likedUsers = ref(new Set());
const favoritedUsers = ref(new Set());

const dragging = ref(false);
const startX = ref(0);
const startY = ref(0);
const currentX = ref(0);
const activeUserId = ref(null);
const swipeDirection = ref(null);
const superLikedUserId = ref(null);
const swipingUserId = ref(null);
const swipeDirectionMap = ref({}); // { [userId]: 'left' | 'right' | 'up' }
const showSwipeLabel = ref(false);



// Super Like 処理
const superLike = (userId) => {
  console.log('Super Like:', userId);
  // axios.post('/superlike', { targetid: userId });
  superLikedUserId.value = userId;
};

const startDrag = (e, userId) => {
  dragging.value = true;
  startX.value = e.clientX || e.touches?.[0]?.clientX;
  startY.value = e.clientY || e.touches?.[0]?.clientY;
  activeUserId.value = userId;
  swipingUserId.value = userId;
  showSwipeLabel.value = true;

};

const currentY = ref(0); // ← 追加！

const onDrag = (e) => {
  if (!dragging.value || activeUserId.value === null) return;
  e.preventDefault?.();

  currentX.value = (e.clientX || e.touches?.[0]?.clientX) - startX.value;
  currentY.value = (e.clientY || e.touches?.[0]?.clientY) - startY.value; // ← これ追加！
};

const endDrag = (e, userId) => {
  if (!dragging.value || activeUserId.value !== userId) return;

  const endX = e.clientX || e.changedTouches?.[0]?.clientX;
  const endY = e.clientY || e.changedTouches?.[0]?.clientY;
  const dx = endX - startX.value;
  const dy = startY.value - endY;

  const threshold = 80;

  /*
  if (dy > threshold && Math.abs(dx) < 50) {
    swipeDirectionMap.value[userId] = 'up';
    superLike(userId);
  } else if (dx > threshold) {
    swipeDirectionMap.value[userId] = 'right';
  } else if (dx < -threshold) {
    swipeDirectionMap.value[userId] = 'left';
  } else {
    swipeDirectionMap.value[userId] = null;
  }
*/
if (dy > threshold && Math.abs(dx) < 50) {
  swipeDirectionMap.value[userId] = 'up';
  superLike(userId);
} else if (dx > threshold) {
  swipeDirectionMap.value[userId] = 'right';
} else if (dx < -threshold) {
  swipeDirectionMap.value[userId] = 'left';
}
  

  dragging.value = false;https://dev.tinder.navi.jpn.com/chat/3
  currentX.value = dx;https://dev.tinder.navi.jpn.com/storage/profile-photos/kmUV1jfecGOo2JIbwNhttps://dev.tinder.navi.jpn.com/user/profile/3faSeoUTV5iZyf4jJ7WnVGK.jpg
  currentY.value = dy; https://dev.tinder.navi.jpn.com/user/profile/2
  activeUserId.value = null;
  const dir = swipeDirectionMap.value[userId];
  
  if (dir) {
    setTimeout(() => {
      const index = displayedUsers.value.findIndex(u => u.id === userId);
      if (index !== -1) displayedUsers.value.splice(index, 1);

      // ✨ 該当ユーザーのスワイプ状態だけリセット
      setTimeout(() => {
        delete swipeDirectionMap.value[userId];
        currentX.value = 0;
        currentY.value = 0;
        if (dir === 'up') superLikedUserId.value = null;
        showSwipeLabel.value = false;
      }, 1000);
    }, 300);
  } else {
    currentX.value = 0;
    currentY.value = 0;
  }
  activeUserId.value = null;
  swipingUserId.value = null;

};

const cardStyle = (userId) => {
  const isActive = activeUserId.value === userId;
  const dx = isActive ? currentX.value : 0;
  const dy = isActive ? currentY.value : 0;
  const rotate = dx / 10;

  let translateX = dx;
  let translateY = dy;

  /*
  if (!isActive && swipeDirection.value && userId === swipingUserId.value) {
  if (swipeDirection.value === 'left') {
    translateX = -window.innerWidth;
    translateY = 0;
  } else if (swipeDirection.value === 'right') {
    translateX = window.innerWidth;
    translateY = 0;
  } else if (swipeDirection.value === 'up') {
    translateX = 0;
    translateY = -window.innerHeight;
  }
    }
    */
    const dir = swipeDirectionMap.value[userId];

    if (!isActive && dir) {
      if (dir === 'left') {
        translateX = -window.innerWidth;
        translateY = 0;
      } else if (dir === 'right') {
        translateX = window.innerWidth;
        translateY = 0;
      } else if (dir === 'up') {
        translateX = 0;
        translateY = -window.innerHeight;
      }
    }
  return {
    transform: `translate(${translateX}px, ${translateY}px) rotate(${rotate}deg)`,
    transition: dragging.value ? 'none' : 'transform 0.4s ease',
    zIndex: 1000 - userId,
    position: 'absolute',
    width: '100%',
  };
};



// いいね・お気に入り関連
const fetchUserCounts = async (userId) => {
  try {
    const res = await axios.get(`/user-counts/${userId}`);
    iineCounts.value[userId] = res.data.iine_count ?? 0;
    okiniiriCounts.value[userId] = res.data.okiniiri_count ?? 0;
    if (res.data.liked) likedUsers.value.add(userId);
    if (res.data.favorited) favoritedUsers.value.add(userId);
  } catch (err) {
    iineCounts.value[userId] = 0;
    okiniiriCounts.value[userId] = 0;
    console.error(`Fetch error:`, err);
  }
};

const toggleIine = async (userId) => {
  try {
    const res = await axios.post('/iine', { targetid: userId });
    if (res.data.liked) {
      likedUsers.value.add(userId);
      iineCounts.value[userId] = (iineCounts.value[userId] ?? 0) + 1;
    } else {
      likedUsers.value.delete(userId);
      iineCounts.value[userId] = Math.max((iineCounts.value[userId] ?? 1) - 1, 0);
    }
    await fetchUserCounts(userId);
  } catch (err) {
    console.error('いいね失敗:', err);
  }
};

const toggleOkiniiri = async (userId) => {
  try {
    const res = await axios.post('/okiniiri', { targetid: userId });
    if (res.data.favorited) {
      favoritedUsers.value.add(userId);
      okiniiriCounts.value[userId] = (okiniiriCounts.value[userId] ?? 0) + 1;
    } else {
      favoritedUsers.value.delete(userId);
      okiniiriCounts.value[userId] = Math.max((okiniiriCounts.value[userId] ?? 1) - 1, 0);
    }
    await fetchUserCounts(userId);
  } catch (err) {
    console.error('お気に入り失敗:', err);
  }
};

onMounted(async () => {
  await nextTick();
  displayedUsers.value.forEach(user => fetchUserCounts(user.id));
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

    <div class="max-w-md mx-auto py-10 relative h-[600px]">
      <div
        v-for="(user, index) in displayedUsers"
        :key="user.id"
        :style="cardStyle(user.id)"
        class="bg-white shadow-lg rounded-lg p-6 cursor-grab relative"
        @mousedown="(e) => startDrag(e, user.id)"
        @mousemove="onDrag"
        @mouseup="(e) => endDrag(e, user.id)"
        @touchstart="(e) => startDrag(e, user.id)"
        @touchmove="onDrag"
        @touchend="(e) => endDrag(e, user.id)"
      >
        <!-- プロフィール画像 -->
        <img
          v-if="user.profile_photo_url"
          :src="user.profile_photo_url"
          alt="プロフィール画像"
          class="w-full h-96 object-cover rounded-lg"
        />





        <!-- 名前・メール -->
        <div class="text-center mt-4">
          <a :href="`/user/profile/${user.id}`">
            <p class="font-semibold text-2xl">{{ user.name }}</p>
          </a>
          <a :href="`/chat/${user.id}`" class="block">
            <p class="text-gray-500 text-lg">{{ user.email }}</p>
          </a>
        </div>

        <!-- いいね・お気に入り -->
        <div class="grid grid-cols-4 gap-2 mt-2">
          <div class="flex items-center justify-center space-x-1">
            <button @click="toggleIine(user.id)">
              <img
                src="/assets/imgs/iine.png"
                class="h-6 w-6"
                :class="{ 'opacity-100': likedUsers.has(user.id), 'opacity-50': !likedUsers.has(user.id) }"
              />
            </button>
            <p class="text-sm text-gray-700">{{ iineCounts[user.id] ?? '0' }}</p>
          </div>

          <div class="flex items-center justify-center space-x-1">
            <button @click="toggleOkiniiri(user.id)">
              <img
                src="/assets/imgs/okiniiri.png"
                class="h-6 w-6"
                :class="{ 'opacity-100': favoritedUsers.has(user.id), 'opacity-50': !favoritedUsers.has(user.id) }"
              />
            </button>
            <p class="text-sm text-gray-700">{{ okiniiriCounts[user.id] ?? '0' }}</p>
          </div>

          <div class="p-1"></div>
          <div class="p-1"></div>
        </div>
      </div>
    </div>

<!-- ❤️ LIKE / ❌ NOPE ラベル - 本物のフェードあり -->
<div
  v-show="dragging"
  class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 px-10 py-4 rounded-full text-white text-4xl font-bold z-50 pointer-events-none transition-all duration-300 ease-out transition-all duration-300 ease-out scale-100"
  :class="{
    'opacity-100 bg-green-500': currentX > 30,
    'opacity-100 bg-gray-600': currentX < -30,
    'opacity-0': Math.abs(currentX) <= 30,
  }"
  :style="{
    zIndex: 1000
  }"
>
  {{ currentX > 30 ? '❤️ LIKE' : currentX < -30 ? '❌ NOPE' : '' }}
</div>



    <!-- 💙 Super Like モーダル -->
    <div v-if="superLikedUserId" class="fixed inset-0 flex items-center justify-center z-50" style="z-index:1000">
      <div class="bg-white p-4 rounded-lg shadow-lg">
        <p class="text-2xl font-bold text-center">Super Like!!</p>
      </div>
    </div>
  </AppLayout>
</template>

