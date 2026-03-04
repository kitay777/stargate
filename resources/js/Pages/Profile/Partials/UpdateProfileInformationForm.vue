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
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            プロファイル インフォメーション
        </template>

        <template #description>
            アカウントのプロフィール情報を更新してください。
        </template>

        <template #form>
                        <!-- Profile Photo -->
                <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div v-show="! photoPreview" class="mt-2">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full size-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">
                    Select A New Photo
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.profile_photo_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deletePhoto"
                >
                    Remove Photo
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>
            <!-- 名前 -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="名前" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full text-black" required />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- メールアドレス -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="メールアドレス" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full text-black" required />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <!-- 氏（firstname） -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="firstname" value="氏" />
                <TextInput id="firstname" v-model="form.firstname" type="text" class="mt-1 block w-full text-black" required />
                <InputError :message="form.errors.firstname" class="mt-2" />
            </div>

            <!-- 名（lastname） -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="lastname" value="名" />
                <TextInput id="lastname" v-model="form.lastname" type="text" class="mt-1 block w-full text-black" required />
                <InputError :message="form.errors.lastname" class="mt-2" />
            </div>

            <!-- 性別（選択式） -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="sex" value="性別" />
                <select id="sex" v-model="form.sex" class="mt-1 block w-full border-gray-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-black">
                    <option v-for="gender in genders" :key="gender" :value="gender">
                        {{ gender }}
                    </option>
                </select>
                <InputError :message="form.errors.sex" class="mt-2" />
            </div>

            <!-- 生年月日 -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="birthday" value="生年月日" />
                <TextInput id="birthday" v-model="form.birthday" type="date" class="mt-1 block w-full text-black" />
                <InputError :message="form.errors.birthday" class="mt-2" />
            </div>

            <!-- 電話番号 -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="phone" value="電話番号" />
                <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full text-black" />
                <InputError :message="form.errors.phone" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="state" value="都道府県" />
                <select id="state" v-model="form.state" class="mt-1 block w-full border-gray-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-black">
                    <option v-for="state in states" :key="state" :value="state">
                        {{ state }}
                    </option>
                </select>
                <InputError :message="form.errors.state" class="mt-2" />
            </div>
            <!-- 住所 -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="address" value="住所" />
                <TextInput id="address" v-model="form.address" type="text" class="mt-1 block w-full text-black" required />
                <InputError :message="form.errors.address" class="mt-2" />
            </div>

            <!-- 市区町村 -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="city" value="市区町村" />
                <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full text-black" />
                <InputError :message="form.errors.city" class="mt-2" />
            </div>

            <!-- 郵便番号 -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="zip" value="郵便番号" />
                <TextInput id="zip" v-model="form.zip" type="text" class="mt-1 block w-full text-black" />
                <InputError :message="form.errors.zip" class="mt-2" />
            </div>

            <!-- 国 -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="country" value="国" />
                <TextInput id="country" v-model="form.country" type="text" class="mt-1 block w-full text-black" />
                <InputError :message="form.errors.country" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="title" value="タイトル" />
                <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full text-black" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="message" value="メッセージ" />
                <textarea id="message" v-model="form.message" class="mt-1 block w-full text-black" rows="5" maxlength="2048"></textarea>
                <InputError :message="form.errors.message" class="mt-2" />
            </div>

        </template>
        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                保存されました。
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                保存
            </PrimaryButton>
        </template>
    </FormSection>
</template>
