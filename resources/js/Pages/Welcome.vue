<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>
<!-- このブロックはテンプレート外に配置する -->
<style scoped>
.icon-row {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 24px;
}
.icon-row img {
  height: 42px;
  width: auto;
}
</style>
<template>
    <Head title="Welcome" />
    <div class="bg-tiffany text-black/50 dark:bg-tiffany dark:text-black/50">
        
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-black">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="flex items-center justify-between gap-4 py-10">
                    <!-- アイコンたち（中央寄せ） -->

                    <!-- ナビゲーション（右寄せ） -->
                    <nav v-if="canLogin" class="ml-auto flex gap-3">
                        <a
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-black/80 dark:focus-visible:ring-white"
                        >
                        Dashboard
                        </a>

                        <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-black/80 dark:focus-visible:ring-white"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-black dark:hover:text-black/80 dark:focus-visible:ring-white"
                        >
                            Register
                        </Link>
                        </template>
                    </nav>
                    </header>

                <main class="mt-6">
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8 text-center">
                        <div class="icon-row">
                            <img src="/assets/imgs/neoriafun.png" alt="Star Icon">
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-black/70">
                    NEORIA.FUN
                    from 2025
                </footer>
            </div>
        </div>
    </div>
</template>
