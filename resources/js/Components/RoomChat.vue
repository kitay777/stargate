<script setup>
import axios from "axios";
import { nextTick, onMounted, ref, watch } from "vue";
import { onBeforeUnmount } from "vue";

let channel;
const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
    currentUser: {
        type: Object,
        required: true,
    },
    friend: {
        type: Object,
        required: true,
    },
});

const messages = ref([]);
const newMessage = ref("");
const messagesContainer = ref(null);
const isFriendTyping = ref(false);
const isFriendTypingTimer = ref(null);
const currentUser = props.currentUser;
const room = props.room;
const onlineUsers = ref([]);
const gifts = ref([]);
const floatingGifts = ref([]);

const sendGift = async (gift) => {
    try {
        await axios.post(`/rooms/${room.id}/gift`, {
            gift_id: gift.id,
        });
    } catch (error) {
        alert(error.response?.data?.error ?? "送信失敗");
    }
};

onMounted(async () => {
    if (!props.room.id) return;

    const res = await axios.get("/api/tip-images");
    gifts.value = res.data;
    const messageRes = await axios.get(`/roommessages/${props.room.id}`);
    messages.value = messageRes.data;
    channel = Echo.join(`roomchat.${props.room.id}`)
        .here((users) => {
            onlineUsers.value = users;
        })
        .joining((user) => {
            onlineUsers.value.push(user);
        })
        .leaving((user) => {
            onlineUsers.value = onlineUsers.value.filter(
                (u) => u.id !== user.id
            );
        })
        .listen("RoomGiftSent", (response) => {
            const duration = Math.random() * 2 + 3;

            const giftObj = {
                id: Date.now(),
                image: "/storage/" + response.gift.image_path,
                left: Math.random() * 70 + 10,
                duration,
            };

            floatingGifts.value.push(giftObj);

            // 💬 チャットに表示
            messages.value.push({
                id: `gift-${Date.now()}`,
                user_id: null,
                message: `🎁 ${response.user.name} さんが ${response.gift.name} (${response.gift.price}pt) を送りました！`,
            });

            setTimeout(() => {
                floatingGifts.value = floatingGifts.value.filter(
                    (g) => g.id !== giftObj.id
                );
            }, duration * 1000);
        })

        .listen("RoomMessageSent", (response) => {
            messages.value.push(response.message);
        });
});

onBeforeUnmount(() => {
    if (channel) {
        if (props.room?.id) {
            Echo.leave(`roomchat.${props.room.id}`);
        }
    }
});

watch(
    () => messages.value.length, // メッセージの数だけを監視！
    () => {
        nextTick(() => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTo({
                    top: messagesContainer.value.scrollHeight,
                    behavior: "smooth",
                });
            }
        });
    }
);

const sendMessage = () => {
    if (newMessage.value.trim() !== "") {
        axios
            .post(`/roommessages/${props.room.id}`, {
                message: newMessage.value,
            })
            .then((response) => {
                //messages.value.push(response.data);
                newMessage.value = "";
            });
    }
};
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- 💬 メッセージ一覧（スクロール可能） -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto px-2">
            <!-- 💬 メッセージ一覧 -->
            <div
                v-for="message in messages"
                :key="message.id"
                class="mb-2 flex items-start"
                :class="{
                    'flex-row-reverse': message.user_id === currentUser.id,
                }"
            >
                <!-- 🖼️ プロフィール画像 -->
                <img
                    :src="getProfileUrl(message.user?.profile_photo_url)"
                    :alt="message.user?.name || 'User'"
                    class="w-8 h-8 rounded-full object-cover mx-2"
                />

                <!-- 💬 吹き出し -->
                <div
                    class="p-2 rounded-lg max-w-[75%] backdrop-blur-sm"
                    :class="
                        message.user_id === currentUser.id
                            ? 'bg-blue-500/30 text-white ml-2'
                            : 'bg-white/30 text-black mr-2'
                    "
                    style="white-space: pre-wrap"
                >
                    {{ message.message }}
                </div>
                <!-- 💬 吹き出しの下にTipだけスタイル変える例 -->
                <div
                    v-if="message.user_id === null"
                    class="text-center text-green-600 font-semibold text-sm my-2"
                >
                    {{ message.message }}
                </div>
            </div>

            <!-- 📝 入力エリア -->
        </div>
        <!-- 💬 メッセージ入力欄 -->
        <!-- 💬 メッセージ入力欄 & 送信 -->
        <div class="flex items-center gap-0 mb-0">
            <textarea
                v-model="newMessage"
                @keyup.enter.shift="sendMessage"
                placeholder="Type a message..."
                class="flex-1 h-10 px-3 py-2 border rounded text-black resize-none leading-none align-middle"
                rows="1"
            ></textarea>
            <button
                @click="sendMessage"
                class="h-10 px-4 bg-blue-500 text-white rounded align-middle"
            >
                送　信
            </button>
        </div>
        <div class="absolute inset-0 pointer-events-none z-50">
            <div
                v-for="gift in floatingGifts"
                :key="gift.id"
                class="absolute"
                :style="{
                    left: gift.left + '%',
                    top: '50%',
                    transform: 'translate(-50%, -50%)',
                    animation: `floatUp ${gift.duration}s ease-out forwards`,
                }"
            >
                <img :src="gift.image" class="w-80" style="mix-blend-mode: screen;"/>
            </div>
        </div>

        <div class="flex gap-3 overflow-x-auto p-2">
            <div
                v-for="gift in gifts"
                :key="gift.id"
                @click="sendGift(gift)"
                class="cursor-pointer text-center"
            >
                <img
                    :src="'/storage/' + gift.image_path"
                    class="w-14 h-14 object-contain"
                />
                <div class="text-xs">{{ gift.price }}pt</div>
            </div>
        </div>

        <!-- ✍️ タイピング表示 -->
        <small v-if="isFriendTyping" class="text-gray-400 mt-1">
            {{ room.name }} is typing...
        </small>
    </div>
</template>

<style scoped>
@keyframes floatUp {
    0% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 0;
    }
    20% {
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -250%) scale(1.2);
        opacity: 0;
    }
}
</style>

<script>
function getProfileUrl(path) {
    if (!path) return "/storage/default-avatar.png";

    if (path.startsWith("http://") || path.startsWith("https://")) {
        return path;
    }

    return `${path}`;
}
</script>
