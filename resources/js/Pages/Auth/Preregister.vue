<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const form = useForm({
  nickname: '',
  email: '',
})

const submit = () => {
  // webミドルウェアなら通常はこれ不要（InertiaがXSRF処理する）だけど
  // 既存の流儀に合わせたいなら残してもOK
  form.post(route('preregister.store'), {
    onFinish: () => form.reset('email'),
  })
}
</script>

<template>
  <Head title="Pre Register" />

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
      <!-- ロゴを中央に -->
      <template #logo>
        <div class="logo-wrap">
          <img
            src="/assets/imgs/kiprylogo.png"
            alt="KIPRY"
            class="kipry-logo"
          />
        </div>
      </template>

      <div>
        <p class="text-gray-300 text-sm mb-6 text-center">
          先行登録いただいた方へ、正式リリース時にご案内します。
        </p>

        <form @submit.prevent="submit">
          <div>
            <InputLabel for="nickname" value="ニックネーム" style="color:#fff" />
            <TextInput
              id="nickname"
              v-model="form.nickname"
              type="text"
              class="mt-1 block w-full"
              style="color:#000"
            />
            <InputError :message="form.errors.nickname" />
          </div>

          <div class="mt-4">
            <InputLabel for="email" value="メールアドレス" style="color:#fff" />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full"
              required
              style="color:#000"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div class="flex justify-center mt-8">
            <PrimaryButton
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              先行登録する
            </PrimaryButton>
          </div>
        </form>
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

/* ★ここが本命：AuthenticationCard（Jetstreamの外側）を透明にする */
:deep(.min-h-screen) {
  background: transparent !important;
}

/* Jetstream/Breezeが bg-gray-100 を使ってる場合の保険 */
:deep(.bg-gray-100) {
  background: transparent !important;
}

/* ===== Card ===== */
.auth-card {
  background: rgba(0, 0, 0, 0.85) !important;
  backdrop-filter: blur(6px);
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


