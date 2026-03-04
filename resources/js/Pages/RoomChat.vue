
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import RoomChat from '@/Components/RoomChat.vue';

console.log('Chat.vue: props:');
const props = defineProps({
  room: Object,
  user: Object
})

// 安全チェック（ログも出せる）
if (!props.room || !props.room.id) {
  console.warn('⚠️ props.room が null または不正');
}
</script>


<template>
    <AppLayout title="Chat Page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat Page With {{ room.name }}
            </h2>
        </template>

        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="py-12">
                        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div class="p-6 bg-white border-b border-gray-200">

                                    <div v-if="room && room.id">
                                        <!-- チャット本体の表示 -->
                                        <RoomChat
                                                :room="room"
                                                :currentUser="user"
                                            />
        
                                    </div>
                                    <div v-else>
                                        ⚠️ 有効なルーム情報がありません
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
