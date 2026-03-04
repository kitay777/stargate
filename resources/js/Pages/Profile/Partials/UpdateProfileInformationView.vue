<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// 🔹 都道府県リスト
const states = [
    "北海道", "青森県", "岩手県", "宮城県", "秋田県", "山形県", "福島県",
    "茨城県", "栃木県", "群馬県", "埼玉県", "千葉県", "東京都", "神奈川県",
    "新潟県", "富山県", "石川県", "福井県", "山梨県", "長野県", "岐阜県",
    "静岡県", "愛知県", "三重県", "滋賀県", "京都府", "大阪府", "兵庫県",
    "奈良県", "和歌山県", "鳥取県", "島根県", "岡山県", "広島県", "山口県",
    "徳島県", "香川県", "愛媛県", "高知県", "福岡県", "佐賀県", "長崎県",
    "熊本県", "大分県", "宮崎県", "鹿児島県", "沖縄県"
];

// 🔹 性別の選択肢
const genders = ["男性", "女性", "その他"];

const props = defineProps({
    user: Object,
    profile: Object,
});

// 🔹 `profiles` のデータをフォームにセット
const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null,
    firstname: props.profile?.firstname || '', // 🔹 氏
    lastname: props.profile?.lastname || '', // 🔹 名
    sex: props.profile?.sex || '男性', // 🔹 デフォルト値: 男性
    birthday: props.profile?.birthday || '',
    phone: props.profile?.phone || '', // 🔹 電話番号
    address: props.profile?.address || '',
    city: props.profile?.city || '', // 🔹 市区町村
    state: props.profile?.state || '東京都', // 🔹 都道府県（デフォルト値: 東京都）
    zip: props.profile?.zip || '', // 🔹 郵便番号
    country: props.profile?.country || '日本', // 🔹 国（デフォルト値: 日本）
    title: props.profile?.title || '', // 🔹 国（デフォルト値: 日本）
    message: props.profile?.message || '', // 🔹 国（デフォルト値: 日本）
});
const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);


const updateProfileInformation = () => {
    form.put(route('user-profile-information.update'), {
        preserveScroll: true,
        onSuccess: () => alert("プロフィールが更新されました！"),
    });
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    form.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};    


const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow relative">
      <!-- 大きな画像 -->
      <div class="relative">
        <div class="relative w-full h-80">
  <!-- 画像 -->
        <img :src="user.profile_photo_url" alt="Profile" class="w-full h-full object-cover object-center rounded-lg">
        
        <!-- ぼかしのオーバーレイ -->
        <div class="absolute inset-0 bg-white/30 backdrop-blur-sm rounded-lg"></div>
        </div>

        
        <!-- 中央のアイコン (オーバーラップ) -->
        <div class="absolute left-1/2 transform -translate-x-1/2 -bottom-8 bg-white rounded-full p-1 shadow-lg">
          <img :src="user.profile_photo_url" alt="Profile" class="w-20 h-20 rounded-full object-cover">
        </div>
      </div>
  
      <!-- ユーザー情報 -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-16">
        <div>
          <InputLabel value="名前" />
          <p class="text-gray-900">{{ form.name }}</p>
        </div>
  
        <div>
          <InputLabel value="メールアドレス" />
          <p class="text-gray-900">{{ form.email }}</p>
        </div>
  
        <div>
          <InputLabel value="氏" />
          <p class="text-gray-900">{{ form.firstname }}</p>
        </div>
  
        <div>
          <InputLabel value="名" />
          <p class="text-gray-900">{{ form.lastname }}</p>
        </div>
  
        <div>
          <InputLabel value="性別" />
          <p class="text-gray-900">{{ form.sex }}</p>
        </div>
  
        <div>
          <InputLabel value="生年月日" />
          <p class="text-gray-900">{{ form.birthday }}</p>
        </div>
  
        <div>
          <InputLabel value="電話番号" />
          <p class="text-gray-900">{{ form.phone }}</p>
        </div>
  
        <div>
          <InputLabel value="都道府県" />
          <p class="text-gray-900">{{ form.state }}</p>
        </div>
  
        <div>
          <InputLabel value="住所" />
          <p class="text-gray-900">{{ form.address }}</p>
        </div>
  
        <div>
          <InputLabel value="市区町村" />
          <p class="text-gray-900">{{ form.city }}</p>
        </div>
  
        <div>
          <InputLabel value="郵便番号" />
          <p class="text-gray-900">{{ form.zip }}</p>
        </div>
  
        <div>
          <InputLabel value="国" />
          <p class="text-gray-900">{{ form.country }}</p>
        </div>
      </div>
    </div>
  </template>
  