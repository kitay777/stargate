<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import NavLink from "@/Components/NavLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import SplashScreen from "@/Components/SplashScreen.vue";

const page = usePage();

const isGuest = computed(() => !page.props.auth?.user);
const bannerHeight = 190; // ← バナーの高さに合わせて調整（px）

console.log("✅ 現在のページ:", usePage().component);
console.log("✅ モード:", isGuest.value ? "ゲスト" : "ログイン済み");

const paddingBottom = computed(() => {
    return isGuest.value ? `${bannerHeight}px` : "70px";
});

defineProps({
    title: String,
});
const isDrawerOpen = ref(false);
const toggleDrawer = () => {
    isDrawerOpen.value = !isDrawerOpen.value;
};

const isMenuOpen = ref(false);
const toggleMenu = () => {
    console.log("toggleMenu");
    isMenuOpen.value = !isMenuOpen.value;
};
const logout = () => {
    axios.get("/sanctum/csrf-cookie").then(() => {
        router.post(route("logout"));
    });
};

const viewportHeight = ref("100vh");
function updateViewportHeight() {
    const h = window.visualViewport?.height || window.innerHeight;
    viewportHeight.value = `${h}px`;
}

onMounted(() => {
    updateViewportHeight();
    window.visualViewport?.addEventListener("resize", updateViewportHeight);
    window.addEventListener("resize", updateViewportHeight);
});

onBeforeUnmount(() => {
    window.visualViewport?.removeEventListener("resize", updateViewportHeight);
    window.removeEventListener("resize", updateViewportHeight);
});
</script>
<template>
    <SplashScreen />
    <style scoped>
        .slide-enter-active,
        .slide-leave-active {
            transition: transform 0.3s ease;
        }
        .slide-enter-from {
            transform: translateX(-100%);
        }
        .slide-enter-to {
            transform: translateX(0);
        }
        .slide-leave-from {
            transform: translateX(0);
        }
        .slide-leave-to {
            transform: translateX(-100%);
        }
    </style>
    <div
        class="font-rounded font-thin bg-black text-white z-9000 overflow-hidden"
    >
        <Head :title="title" />
        <Banner />

        <!-- 高さを visualViewport.height に合わせる -->
        <div class="flex flex-col min-h-screen bg-black text-white">
            <!-- ナビゲーションメニュー -->
            <nav class="bg-black border-b border-gray-100 w-full z-0">
                <!-- (省略: ナビゲーションコードは元のままでOK) -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex w-full">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>
                            <div
                                class="flex-1 flex justify-left items-center gap-4"
                            >
                                <img
                                    src="/assets/imgs/neoriafun.png"
                                    alt="star"
                                    class="h-12"
                                />
                            </div>
                            <!-- 🔹 デスクトップ用メニュー -->
                            <!--
                            <div
                                class="hidden sm:flex flex-1 space-x-8 sm:-my-px sm:ms-10 items-center"
                            >
                                <a
                                    href="/dashboard"
                                    :active="route().current('dashboard')"
                                    class="text-center"
                                >
                                    ダッシュボード
                                </a>
                                <a
                                    href="/user/profile"
                                    :active="route().current('profile.show')"
                                    class="text-center"
                                >
                                    プロファイル
                                </a>
                                <a
                                    href="/presenter/1"
                                    :active="route().current('profile.show')"
                                    class="text-center"
                                >
                                    Presenter
                                </a>
                            </div>
                            -->
                            <!-- ユーザー未ログイン時のみ表示 -->
                            <div
                                v-if="!page.props.auth?.user"
                                class="hidden sm:flex sm:items-center gap-4"
                            >
                                <Link
                                    href="/login"
                                    class="text-sm text-gray-700 hover:text-blue-500"
                                >
                                    ログイン
                                </Link>
                                <!--
                                <Link
                                    href="/register"
                                    class="text-sm text-gray-700 hover:text-blue-500"
                                >
                                    新規登録
                                </Link>
                                -->
                            </div>
                        </div>

                        <!-- 🔹 デスクトップ用 LOGOUT & プロフィール画像 -->
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- ユーザー画像 -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            v-if="
                                                $page.props.jetstream
                                                    .managesProfilePhotos
                                            "
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition"
                                        >
                                            <img
                                                class="size-8 rounded-full object-cover"
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_photo_url
                                                "
                                                :alt="
                                                    $page.props.auth.user.name
                                                "
                                            />
                                        </button>
                                    </template>

                                    <template #content>
                                        <form
                                            @submit.prevent="logout"
                                            method="post"
                                        >
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- 🔹 モバイル右上：未ログイン時は横並びで表示 -->
                        <!-- モバイル右上：未ログイン時は横並びボタン -->
                        <div
                            class="sm:hidden flex items-center ml-auto pr-2 min-w-[160px] justify-end space-x-3"
                        >
                            <template v-if="!page.props.auth?.user">
                                <Link
                                    href="/login"
                                    class="inline-block whitespace-nowrap px-2 py-1 text-sm text-gray-700 hover:text-white hover:bg-blue-500 rounded"
                                >
                                    ログイン
                                </Link>
                                <!--
                                <Link
                                    href="/register"
                                    class="inline-block whitespace-nowrap px-2 py-1 text-sm text-gray-700 hover:text-white hover:bg-green-500 rounded"
                                >
                                    新規登録
                                </Link>
                                -->
                            </template>

                            <!-- ログイン済み時はハンバーガー -->
                            <template v-else>
                                <button
                                    @click="toggleMenu"
                                    class="p-2 rounded-md text-gray-500 hover:text-gray-700"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            v-if="!isMenuOpen"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            v-else
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- モバイルメニュー -->
            <!-- モバイルメニュー -->
            <div v-if="isMenuOpen" class="sm:hidden bg-black text-white p-4">
                <template v-if="!page.props.auth?.user">
                    <!-- 🔹 未ログイン時 -->
                    <div class="space-y-2">
                        <Link href="/login" class="block hover:text-blue-500"
                            >🔐 ログイン</Link
                        >
                        <!--
                        <Link href="/register" class="block hover:text-blue-500"
                            >🆕 新規登録</Link
                        >
                        -->
                    </div>
                </template>

                <template v-else>
                    <!-- 🔹 ログイン済み時 -->
                    <!--
                    <a
                        href="/user/profile"
                        :active="route().current('profile.show')"
                        class="block p-2"
                    >
                        Profile
                    </a>

                    <div class="border-t border-gray-300 my-2"></div>

                    <div class="flex items-center space-x-3 p-2">
                        <img
                            v-if="$page.props.jetstream.managesProfilePhotos"
                            class="size-10 rounded-full object-cover"
                            :src="$page.props.auth.user.profile_photo_url"
                            :alt="$page.props.auth.user.name"
                        />
                        <span class="text-gray-700">{{
                            $page.props.auth.user.name
                        }}</span>
                    </div>
                    -->
                    <!-- 🔹 ログアウト -->
                    <DropdownLink as="button" @click="logout">
                        ログアウト
                    </DropdownLink>

                    <div class="border-t border-gray-300 my-2"></div>
                </template>
            </div>

            <!-- サイドバー＋メイン -->
            <div class="flex flex-1 overflow-hidden">
                <main
                    class="flex-1 overflow-auto p-4"
                    :style="{ paddingBottom: paddingBottom }"
                >
                    <slot />
                </main>
            </div>
        </div>

        <!-- 固定フッター -->
        <footer
            v-if="page.props.auth?.user"
            class="fixed bottom-0 left-0 right-0 bg-black border-t border-gray-200 z-50 text-white"
        >
            <div
                class="flex justify-around items-center py-2 max-w-4xl mx-auto h-16"
            >
                <!-- ホーム -->
                <button
                    class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                >
                    <Link
                        href="/dashboard"
                        class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"
                            />
                        </svg>
                        <span class="text-xs">ホーム</span>
                    </Link>
                </button>
                <!--
                <button
                    class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                >
                    <Link
                        :href="route('shop')"
                        class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 9l1-5h16l1 5M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9M9 22V12h6v10"
                            />
                        </svg>
                        <span class="text-xs">SHOP</span>
                    </Link>
                </button>
            -->
                <!-- 作成 -->
                <button
                    class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                >
                    <Link
                        :href="route('make-room')"
                        class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        <span class="text-xs">作成</span>
                    </Link>
                </button>

                <!-- マイページ -->
                <button
                    class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                >
                    <Link
                        :href="route('myrooms')"
                        class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <circle cx="12" cy="12" r="3" />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 2v2m6.364 1.636l-1.414 1.414M22 12h-2m-1.636 6.364l-1.414-1.414M12 22v-2m-6.364-1.636l1.414-1.414M2 12h2m1.636-6.364l1.414 1.414"
                            />
                        </svg>
                        <span class="text-xs">配信</span>
                    </Link>
                </button>
                <!-- ⭐ 新しいライブ配信 -->
                <Link
                    href="/presenter"
                    class="flex flex-col items-center text-white hover:text-yellow-400"
                >
                    <div class="bg-yellow-300 p-3 rounded-full shadow-lg -mt-5">
                        <img
                            src="/assets/imgs/starmark2.png"
                            alt="Presenter Star"
                            class="w-12 h-12 object-contain"
                        />
                    </div>
                </Link>

                <!-- ユーザー -->
                <!-- ユーザー -->
                <Link
                    :href="route('profile.basic.edit')"
                    class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 4a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <span class="text-xs">ユーザー</span>
                </Link>

                <!-- メニュー -->
                <button
                    @click="toggleDrawer"
                    class="flex flex-col items-center text-sm text-white hover:text-blue-500"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 7h18M3 12h18M3 17h18"
                        />
                    </svg>
                    <span class="text-xs">メニュー</span>
                </button>
            </div>
        </footer>
        <footer
            v-else
            class="fixed bottom-0 left-0 right-0 z-50 bg-black text-center"
        >
            <div class="w-full">
                <img
                    src="/assets/imgs/neoriabanner01.png"
                    alt="Neoria Banner"
                    class="w-full h-auto max-h-[300px] object-contain"
                />
            </div>
        </footer>
    </div>
    <!-- スライドメニュー前に追加 -->
    <div
        v-if="isDrawerOpen"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40"
        @click="toggleDrawer"
    ></div>
    <!-- ✅ 左からスライドするメニュー -->
    <transition name="slide">
        <div
            v-if="isDrawerOpen"
            class="fixed top-0 left-0 h-full w-3/4 z-50 shadow-xl overflow-auto"
            :style="{
                backgroundColor: 'rgba(32, 15, 0, 0.9)',
                color: 'white',
            }"
        >
            <!-- 上部ヘッダー -->
            <div
                class="flex justify-between items-center p-4 border-b border-white/20"
            >
                <h2 class="text-lg font-semibold">メニュー</h2>
                <button
                    @click="toggleDrawer"
                    class="text-white hover:text-yellow-300 text-xl"
                >
                    ✕
                </button>
            </div>

            <!-- メニュー項目 -->
            <div class="p-4 space-y-4">
                <a href="/settings" class="block hover:text-yellow-300"
                    >⚙️ 設定
                </a>
                <a href="/notifications" class="block hover:text-yellow-300"
                    >🔔 通知
                </a>
                <a href="/help" class="block hover:text-yellow-300"
                    >❓ ヘルプ
                </a>
                <hr />
                <a href="/avatar/upload" class="block hover:text-yellow-300"
                    >アバター登録
                </a>
                <a href="/avatar/select" class="block hover:text-yellow-300"
                    >アバター選択
                </a>
                <hr />
                <a href="/my-tips" class="block hover:text-yellow-300"
                    >💰 ポイント確認
                </a>

                <a
                    href="/my-liked-presenters"
                    class="block hover:text-yellow-300"
                    >⭐️ マイスター一覧
                </a>
                <!--
                <a
                    v-if="
                        $page.props.auth.user?.type === 'seller' ||
                        $page.props.auth.user?.type === 'admin'
                    "
                    href="/products"
                    class="block hover:text-yellow-300"
                >
                    📦 商品管理
                </a>
                <a href="/cart" class="block hover:text-yellow-300">
                    📦 カート
                </a>
                <a href="/purchased" class="block hover:text-yellow-300">
                    📦 購入履歴
                </a>
                <a href="/videos/upload" class="block hover:text-yellow-300">
                    動画アップロード
                </a>
                -->
            </div>
            <!--
            <a
                v-if="$page.props.auth.user?.type === 'admin'"
                href="/products/seller-manage"
                class="block hover:text-yellow-300"
            >
                👤 ライバー追加
            </a>
            -->
        </div>
    </transition>
</template>

<style scoped>
/* 必要に応じて追加スタイルをここに */
html,
body {
    height: 100%;
    overflow: auto;
    touch-action: none;
}
</style>
