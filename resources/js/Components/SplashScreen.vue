<script setup>
import { ref, nextTick, onMounted } from "vue";

const visible = ref(false);

onMounted(async () => {
  // "/" のときだけ出す
  if (window.location.pathname !== "/") return;

  // 即表示（まず濃紺＋星だけでも必ず出る）
  visible.value = true;

  // DOM反映
  await nextTick();

  // 「表示された後」から3秒保証（フレーム基準）
  const start = performance.now();
  const tick = () => {
    if (performance.now() - start >= 3000) {
      visible.value = false;
      return;
    }
    requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
});
</script>

<template>
  <transition name="fade">
    <div v-if="visible" class="splash">
      <!-- 背景（濃紺＋星） -->
      <div class="sky"></div>
      <div class="stars"></div>

      <!-- 絵（必ず描画される方式） -->
      <img
        class="art"
        src="/assets/imgs/splash_base.png"
        alt="KIPRY Splash"
        loading="eager"
        decoding="async"
        fetchpriority="high"
      />
    </div>
  </transition>
</template>

<style scoped>
/* 最前面固定 */
.splash {
  position: fixed;
  inset: 0;
  z-index: 999999;
  overflow: hidden;
}

/* 上下濃紺（まずこれが即出る） */
.sky {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, #071836 0%, #0a1f4d 50%, #071836 100%);
  z-index: 1;
}

/* 星空（上に薄く） */
.stars {
  position: absolute;
  inset: 0;
  z-index: 3;
  pointer-events: none;
  opacity: 0.35;
  background-image:
    radial-gradient(2px 2px at 12% 18%, rgba(255,255,255,.9), transparent),
    radial-gradient(2px 2px at 72% 28%, rgba(255,255,255,.9), transparent),
    radial-gradient(1px 1px at 48% 62%, rgba(255,255,255,.9), transparent),
    radial-gradient(1px 1px at 86% 78%, rgba(255,255,255,.9), transparent),
    radial-gradient(1px 1px at 26% 84%, rgba(255,255,255,.9), transparent);
  animation: twinkle 2.8s ease-in-out infinite alternate;
}

@keyframes twinkle {
  from { opacity: 0.18; }
  to { opacity: 0.45; }
}

/* 絵：横幅優先で画面いっぱい（縦ははみ出してOK＝上下カットOK） */
.art {
  position: absolute;
  inset: 0;
  width: 100vw;
  height: 100vh;
  object-fit: cover;      /* ← 横幅優先・画面を必ず埋める */
  object-position: center;
  z-index: 2;
}

/* フェード */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.6s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>