<script setup>
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-6 text-xl font-bold border-b border-gray-700">
                KIPRY Admin
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <Link
                    href="/admin/dashboard"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    ダッシュボード
                </Link>

                <Link
                    href="/admin/users"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    ユーザー管理
                </Link>

                <Link
                    href="/admin/rooms"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    配信管理
                </Link>

                <Link
                    href="/admin/products"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    商品管理
                </Link>

                <Link
                    href="/admin/reports"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    通報管理
                </Link>
                <Link
                    href="/admin/clothes"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    衣類管理
                </Link>
                <Link
                    href="/admin/avatars/create"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    公式アバター登録
                </Link>
                <Link
                    href="/admin/avatars"
                    class="block p-2 rounded hover:bg-gray-700"
                >
                    公式アバター
                </Link>
            </nav>

            <div class="p-4 border-t border-gray-700 text-sm text-gray-400">
                {{ page.props.auth.user.name }}
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 p-8">
            <slot />
        </main>
    </div>
</template>
