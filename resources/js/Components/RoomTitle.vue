<template>
  <span>{{ title ?? '読み込み中...' }}</span>
</template>

<script setup>
import { onMounted, ref } from 'vue'

const props = defineProps({
  roomId: {
    type: [Number, String],
    required: true,
  }
})

const title = ref(null)

onMounted(async () => {
  try {
    const res = await fetch(`/room-name/${props.roomId}`)
    const data = await res.json()
    title.value = data.name
  } catch (e) {
    console.error(`room ${props.roomId} の名前取得失敗`, e)
    title.value = '取得失敗'
  }
})
</script>
