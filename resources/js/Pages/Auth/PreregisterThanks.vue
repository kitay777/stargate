<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
</script>

<template>
  <Head title="Thanks" />

  <!-- ===== 背景動画 ===== -->
  <div class="video-bg">
    <video autoplay muted loop playsinline>
      <source src="/assets/imgs/topimage.mp4" type="video/mp4" />
    </video>
    <div class="video-overlay"></div>
  </div>

  <!-- ===== メイン ===== -->
  <div class="page-wrap">
    <AuthenticationCard>
      <!-- ロゴ -->
      <template #logo>
        <div class="logo-wrap">
          <img
            src="/assets/imgs/kiprylogo.png"
            alt="KIPRY"
            class="kipry-logo"
          />
        </div>
      </template>

      <div class="text-white text-center">
        <h1 class="text-2xl font-bold mb-4">
          先行登録ありがとうございます！
        </h1>

        <p class="text-gray-300 mb-8 leading-relaxed">
          先行登録いただいた方へ、<br />
          正式リリース時にご案内をお送りします。
        </p>

        <p class="text-sm text-gray-400">
          引き続き <span class="font-semibold text-white">KIPRY</span> をよろしくお願いします。
        </p>
      </div>
    </AuthenticationCard>
  </div>
</template>

<style scoped>
/* ===== 背景動画 ===== */
.video-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  overflow: hidden;
}

.video-bg video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.video-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
}

/* ===== 全体レイアウト ===== */
.page-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 1;
}

/* AuthenticationCard 外側の背景を透明に */
:deep(.min-h-screen),
:deep(.bg-gray-100) {
  background: transparent !important;
}

/* ===== ロゴ ===== */
.logo-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.kipry-logo {
  max-width: 220px;
  width: 100%;
}
</style>
