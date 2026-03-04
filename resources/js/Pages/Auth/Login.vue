<template>
  <div class="login-wrapper">

    <!-- 星空キャンバス -->
    <canvas ref="canvas" class="star-canvas"></canvas>

    <!-- 中央コンテンツ -->
    <div class="center-content">
      
      <div class="logo-circle">
        <img src="/assets/imgs/starmark.png" />
      </div>
      <div class="logo">
        <img src="/assets/imgs/kiplylogo.png" />
      </div>

      <a href="/auth/line" class="line-btn">
        LINEでログイン♪
      </a>

    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";

const canvas = ref(null);

onMounted(() => {
  const ctx = canvas.value.getContext("2d");
  const dpr = window.devicePixelRatio || 1;

  const resize = () => {
    canvas.value.width = window.innerWidth * dpr;
    canvas.value.height = window.innerHeight * dpr;
    canvas.value.style.width = window.innerWidth + "px";
    canvas.value.style.height = window.innerHeight + "px";
    ctx.scale(dpr, dpr);
  };

  resize();
  window.addEventListener("resize", resize);

  const stars = [];

  const STAR_COUNT = 600; // ←ここで増やせる

  for (let i = 0; i < STAR_COUNT; i++) {
    stars.push({
      x: Math.random() * window.innerWidth,
      y: Math.random() * window.innerHeight,
      size: Math.random() * 2.5,
      speed: Math.random() * 0.3 + 0.1,
      alpha: Math.random(),
    });
  }

  function animate() {
    ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);

    for (let star of stars) {
      star.y -= star.speed;

      if (star.y < 0) {
        star.y = window.innerHeight;
        star.x = Math.random() * window.innerWidth;
      }

      ctx.beginPath();
      ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(255,255,255,${star.alpha})`;
      ctx.fill();
    }

    requestAnimationFrame(animate);
  }

  animate();
});
</script>


<style scoped>
.login-wrapper {
  position: relative;
  min-height: 100vh;
  background: linear-gradient(to bottom, #071836, #0a1f4d, #071836);
  overflow: hidden;

  display: flex;
  justify-content: center;
  align-items: center;
}

/* 星空は一番下 */
.star-canvas {
  position: absolute;
  inset: 0;
  z-index: 1;
}

/* 中央ボックス */
.center-content {
  position: relative;
  z-index: 10;   /* ← これ重要 */
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 40px;
}

/* 丸ロゴ */
.logo-circle {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 0 40px rgba(255,255,255,0.2);
}

.logo-circle img {
  width: 70%;
}

.logo{
  width: 300px;
  display: flex;
  justify-content: center;
}
.logo img {
  width: 70%;
}

/* LINEボタン */
.line-btn {
  background: #06c755;
  color: white;
  padding: 18px 50px;
  font-size: 18px;
  border-radius: 14px;
  font-weight: bold;
  text-decoration: none;
  box-shadow: 0 8px 20px rgba(0,0,0,0.4);
  transition: 0.2s;
}

.line-btn:hover {
  transform: translateY(-3px);
}
</style>