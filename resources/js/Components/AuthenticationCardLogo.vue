<script setup>
import { Link } from '@inertiajs/vue3';
</script>
<style scoped>
.icon-row {
  display: flex;
  justify-content: left;
  align-items: center;
  gap: 24px;
}
.icon-row img {
  height: 32px;
  width: auto;
}
</style>
<template>
    <Link :href="'/'">
        <div class="grid gap-6 lg:grid-cols-2 lg:gap-8 text-left">
                        <div class="icon-row">
                            <img src="/assets/imgs/neoriafun.png" alt="Star Icon">
                        </div>
                    </div>
    </Link>
</template>
