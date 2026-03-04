<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import RoomChat from "@/Components/RoomChat.vue";
import { onMounted, ref, onBeforeUnmount } from "vue";
import { initPresenter } from "@/webrtc/presenter.js";
import axios from "axios";
import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader";
import { VRMLoaderPlugin, VRMUtils } from "@pixiv/three-vrm";
import AvatarSelector from "@/Components/AvatarSelector.vue";

const showIntro = ref(true);
const isStreaming = ref(false);
const isRecording = ref(false);
const isAuctionRunning = ref(false);
const avatarLoading = ref(false);

const renderMode = ref("camera"); // 'camera' | 'vrm'

const props = defineProps({
    room: Object,
    user: Object,
});
const room = ref(props.room || {});

const containerHeight = ref("100vh");
const bgMode = ref("none");
const bgReady = ref(false);
const bgImg = new Image();
const bgSrc = ref("");

const vrmCanvas = document.createElement("canvas");
vrmCanvas.width = 640;
vrmCanvas.height = 480;

const vrmRenderer = new THREE.WebGLRenderer({
    canvas: vrmCanvas,
    alpha: true,
    antialias: true,
});

vrmRenderer.setSize(640, 480);

let blinkTimer = 0;

let canvas, ctx;
let renderer, scene, camera3d, vrm, clock;
let presenterStarted = false;
let inputVideo = null;
let mouthTimer = null;
let mixer = null;
let waveAction = null;
let waving = false;
let wavePhase = 0;
let micStream = null;
let audioCtx = null;
let analyser = null;
let audioSource = null;
let mouthAnimationId = null;
let lipSyncEnabled = true;
let vrmLoader = null;
const showAvatarSelector = ref(false);

const avatarUrl = ref(null);

let neckPhase = 0;
let shaking = false;

function startHeadShake() {
    shaking = true;
}

function stopHeadShake() {
    shaking = false;
    neckPhase = 0;

    const neck = vrm.humanoid.getNormalizedBoneNode("neck");
    if (neck) neck.rotation.set(0, 0, 0);
}

function openAvatarSelector() {
    showAvatarSelector.value = true;
}

function closeAvatarSelector() {
    showAvatarSelector.value = false;
}

function onAvatarSelected(newUrl) {
    reloadAvatar(newUrl);
    showAvatarSelector.value = false; // ← これ重要
}

function startWave() {
    waving = true;
}

function stopWave() {
    waving = false;
    wavePhase = 0;

    if (!vrm) return;
    ["rightUpperArm", "rightLowerArm", "rightHand"].forEach((name) => {
        const b = vrm.humanoid.getNormalizedBoneNode(name);
        if (b) b.rotation.set(0, 0, 0);
    });
}
async function startCamera() {
    const stream = await navigator.mediaDevices.getUserMedia({
        video: true,
        audio: false,
    });

    inputVideo.srcObject = stream;
    await inputVideo.play();
}

function startPresenterOnce() {
    if (presenterStarted) return;
    if (!room.value?.id) return;
    if (!canvas) return;
    if (!micStream) {
        console.warn("🎤 micStream not ready");
        return;
    }

    const canvasStream = canvas.captureStream(30);
    const videoTrack = canvasStream.getVideoTracks()[0];
    const audioTrack = micStream.getAudioTracks()[0];

    initPresenter(room.value.id, {
        videoTrack,
        audioTrack,
    });

    presenterStarted = true;
    isStreaming.value = true;
}

function initVRM() {
    if (!avatarUrl.value) {
        console.warn("⚠️ avatarUrl not ready");
        return;
    }

    scene = new THREE.Scene();

    camera3d = new THREE.PerspectiveCamera(30, 640 / 480, 0.1, 100);
    camera3d.position.set(0, 1.4, 2.5);

    clock = new THREE.Clock();

    const ambient = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambient);

    const dir = new THREE.DirectionalLight(0xffffff, 1.0);
    dir.position.set(0, 2, 2);
    scene.add(dir);

    vrmLoader = new GLTFLoader();
    vrmLoader.register((parser) => new VRMLoaderPlugin(parser));

    vrmLoader.load(avatarUrl.value, (gltf) => {
        vrm = gltf.userData.vrm;

        VRMUtils.removeUnnecessaryVertices(vrm.scene);
        VRMUtils.removeUnnecessaryJoints(vrm.scene);

        vrm.scene.rotation.y = Math.PI;
        vrm.scene.scale.setScalar(1.0);
        vrm.scene.position.set(0, 0.5, 0);

        scene.add(vrm.scene);
    });
}

function toggleStreaming() {
    if (!isStreaming.value) {
        startPresenterOnce();
    } else {
        window.location.href = "/dashboard";
    }
}

function toggleRecording() {
    if (!isRecording.value) {
        document.getElementById("startRecord")?.click();
        isRecording.value = true;
    } else {
        document.getElementById("stopRecord")?.click();
        isRecording.value = false;
    }
}

function toggleAuction() {
    if (!isAuctionRunning.value) {
        startAuction();
        isAuctionRunning.value = true;
    } else {
        endAuction();
        isAuctionRunning.value = false;
    }
}

function updateHeight() {
    const h = window.visualViewport
        ? window.visualViewport.height
        : window.innerHeight;
    containerHeight.value = `${h}px`;
}

function onBgChange(event) {
    const selected = event.target.value;
    if (selected === "none") {
        bgMode.value = "none";
        bgReady.value = true;
    } else {
        bgMode.value = "image";
        bgReady.value = false;
        bgImg.src = selected;
    }
}

async function onUploadBg(event) {
    console.log("📥 ファイル選択イベント発生", event);
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append("image", file);

    try {
        const res = await axios.post("/background/upload", formData, {
            withCredentials: true,
        });
        bgMode.value = "image";
        bgReady.value = false;
        bgImg.src = res.data.url;
    } catch (err) {
        console.error("背景アップロード失敗:", err);
    }
}

function drawContainBackground(img, ctx, canvasWidth, canvasHeight) {
    const imgAspect = img.width / img.height;
    const canvasAspect = canvasWidth / canvasHeight;
    let drawWidth, drawHeight;
    let offsetX = 0,
        offsetY = 0;

    if (imgAspect > canvasAspect) {
        drawWidth = canvasWidth;
        drawHeight = canvasWidth / imgAspect;
        offsetY = (canvasHeight - drawHeight) / 2;
    } else {
        drawHeight = canvasHeight;
        drawWidth = canvasHeight * imgAspect;
        offsetX = (canvasWidth - drawWidth) / 2;
    }

    ctx.drawImage(img, offsetX, offsetY, drawWidth, drawHeight);
}

const showRoomForm = ref(true);
const form = ref({
    name: "",
    description: "",
    category_id: null,
});
const categories = ref([]);

// 🔻 ページ表示時にカテゴリー一覧取得（必要なら）
onMounted(async () => {
    initThree();
    const res = await axios.get("/api/categories"); // Laravelで要定義
    categories.value = res.data;
    try {
        const res = await axios.get("/api/me");
        avatarUrl.value = res.data.avatar?.vrm_url;

        if (!avatarUrl.value) {
            console.warn("⚠️ avatar not set, skip VRM load");
            return;
        }

        initVRM(); // ★ ここで初回ロード
    } catch (e) {
        console.error("❌ failed to load avatar", e);
    }
});

function loadAvatar() {
    if (!avatarUrl) return;

    loader.load(avatarUrl, (gltf) => {
        vrm = gltf.userData.vrm;
        scene.add(vrm.scene);
    });
}

function reloadAvatar(newUrl) {
    loadVrm(newUrl);
}

// 🔻 入力完了→ルーム作成→initPresenter呼び出し
async function createRoomAndStart() {
    try {
        await axios.get("/sanctum/csrf-cookie", { withCredentials: true });

        const res = await axios.post(
            "/api/rooms",
            {
                name: form.value.name,
                description: form.value.description,
                category_id: form.value.category_id,
            },
            { withCredentials: true }
        );

        room.value = res.data;
        showRoomForm.value = false;

        startPresenterOnce();
    } catch (err) {
        console.error("❌ ルーム作成失敗:", err);
    }
}

onMounted(() => {
    console.log("📹 Presenter.vue マウント完了");
    if (props.room?.id != null) {
        room.value = props.room;
        showRoomForm.value = false;
    }
    updateHeight();
    window.visualViewport?.addEventListener("resize", updateHeight);
    window.addEventListener("resize", updateHeight);

    canvas = document.getElementById("canvas");
    canvas.width = 640;
    canvas.height = 480;
    ctx = canvas.getContext("2d", { alpha: true });

    const userId = props.user.id;
    const userBgPath = `/storage/backgrounds/${userId}.jpg`;
    bgImg.onload = () => {
        bgReady.value = true;
        console.log("🖼️ ユーザー背景画像ロード完了");
    };
    bgImg.onerror = () => {
        console.log("❌ 背景画像なし（初期 none モード）");
        bgMode.value = "none";
        bgReady.value = true;
    };
    bgImg.src = userBgPath;
    bgSrc.value = userBgPath;

    const selfieSegmentation = new window.SelfieSegmentation({
        locateFile: (file) =>
            `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/${file}`,
    });
    selfieSegmentation.setOptions({ modelSelection: 1 });

    inputVideo = document.createElement("video");
    inputVideo.setAttribute("playsinline", "");
    inputVideo.setAttribute("muted", "");
    inputVideo.style.display = "none";
    document.body.appendChild(inputVideo);

    const previousMaskCanvas = document.createElement("canvas");
    previousMaskCanvas.width = canvas.width;
    previousMaskCanvas.height = canvas.height;
    const previousMaskCtx = previousMaskCanvas.getContext("2d");

    const blendedMaskCanvas = document.createElement("canvas");
    blendedMaskCanvas.width = canvas.width;
    blendedMaskCanvas.height = canvas.height;
    const blendedMaskCtx = blendedMaskCanvas.getContext("2d");

    /*
      selfieSegmentation.onResults((results) => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // 🎥 入力映像
        ctx.globalAlpha = 1.0;  
        ctx.drawImage(inputVideo, 0, 0, canvas.width, canvas.height);

        // ✨ セグメンテーションで人物だけ残す
        ctx.save();
        ctx.filter = 'blur(1.5px)'; // ← 軽くする or 外す
        ctx.globalCompositeOperation = 'destination-in';
        ctx.drawImage(results.segmentationMask, 0, 0, canvas.width, canvas.height);
        ctx.restore();

        if (bgMode.value === 'image') {
          ctx.globalCompositeOperation = 'destination-over';
          drawContainBackground(bgImg, ctx, canvas.width, canvas.height);
        } else {
            ctx.globalAlpha = 1.0;
            ctx.drawImage(inputVideo, 0, 0, canvas.width, canvas.height);
        }


        ctx.globalCompositeOperation = 'source-over';
      });
    */

    selfieSegmentation.onResults((results) => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const videoAspect = inputVideo.videoWidth / inputVideo.videoHeight;
        const canvasAspect = canvas.width / canvas.height;
        let drawW,
            drawH,
            offsetX = 0,
            offsetY = 0;

        if (videoAspect > canvasAspect) {
            drawW = canvas.width;
            drawH = canvas.width / videoAspect;
            offsetY = (canvas.height - drawH) / 2;
        } else {
            drawH = canvas.height;
            drawW = canvas.height * videoAspect;
            offsetX = (canvas.width - drawW) / 2;
        }

        if (bgMode.value === "image") {
            // 🎥 入力映像
            ctx.drawImage(inputVideo, offsetX, offsetY, drawW, drawH);

            // ✨ segmentation mask による人物切り抜き（blurあり）
            ctx.save();
            ctx.filter = "blur(2px)";
            ctx.globalCompositeOperation = "destination-in";
            ctx.drawImage(
                results.segmentationMask,
                offsetX,
                offsetY,
                drawW,
                drawH
            );
            ctx.restore();

            // 🌄 背景合成（固定位置でクロップ）
            ctx.globalCompositeOperation = "destination-over";
            drawContainBackground(bgImg, ctx, canvas.width, canvas.height);
        } else {
            // 🎥 背景なし：純粋な映像だけを表示
            ctx.drawImage(inputVideo, offsetX, offsetY, drawW, drawH);
        }

        ctx.globalCompositeOperation = "source-over";
    });

    navigator.mediaDevices
        .getUserMedia({
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
            },
        })
        .then((stream) => {
            micStream = stream;
            console.log("🎤 micStream ready");
            // ===== 口パク用 AudioContext =====
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioCtx.createAnalyser();
            analyser.fftSize = 2048;

            audioSource = audioCtx.createMediaStreamSource(stream);
            audioSource.connect(analyser);

            startLipSync(); // ★ ここで開始
        })
        .catch((err) => {
            console.error("🎤 micStream error", err);
        });

    navigator.mediaDevices
        .getUserMedia({ video: true, audio: true })
        .then((stream) => {
            inputVideo.srcObject = stream;
            inputVideo.onloadedmetadata = () => {
                inputVideo.play();

                const camera = new window.Camera(inputVideo, {
                    onFrame: async () => {
                        if (renderMode.value === "camera") {
                            await selfieSegmentation.send({
                                image: inputVideo,
                            });
                        }
                    },
                    width: 640,
                    height: 480,
                });

                camera.start();

                // ✅ streamの準備が整ったタイミングでWebRTC配信開始
                /*setTimeout(() => {
                    //initPresenter(room.value.id, canvas);
                    isStreaming.value = true;
                }, 500);
                */
                setTimeout(() => {
                    startPresenterOnce();
                }, 500);
            };
        });

    drawFrame();
});

onBeforeUnmount(() => {
    window.visualViewport?.removeEventListener("resize", updateHeight);
    window.removeEventListener("resize", updateHeight);
    if (mouthAnimationId) {
        cancelAnimationFrame(mouthAnimationId);
        mouthAnimationId = null;
    }

    if (audioCtx) {
        audioCtx.close();
        audioCtx = null;
    }
});

defineExpose({
    onUploadBg,
    onBgChange,
});

const currentBid = ref(0);
const bids = ref([]);
const remainingTime = ref(180);
let auctionTimer = null;

function startAuction() {
    // タイマー開始
    remainingTime.value = 180;
    currentBid.value = 1500;
    bids.value = [
        { id: 1, user: { name: "佐藤" }, amount: 1200 },
        { id: 2, user: { name: "山田" }, amount: 1300 },
        { id: 3, user: { name: "田中" }, amount: 1500 },
    ];

    auctionTimer = setInterval(() => {
        if (remainingTime.value > 0) {
            remainingTime.value--;
        } else {
            endAuction();
        }
    }, 1000);

    // WebSocket購読
    Echo.channel(`auction.${props.room.id}`).listen("BidPlaced", (e) => {
        currentBid.value = e.amount;
        bids.value.unshift(e);
    });
}

function startLipSync() {
    if (!analyser) return;

    const bufferLength = analyser.fftSize;
    const dataArray = new Uint8Array(bufferLength);

    const loop = () => {
        analyser.getByteTimeDomainData(dataArray);

        // 音量（RMS）計算
        let sum = 0;
        for (let i = 0; i < bufferLength; i++) {
            const v = (dataArray[i] - 128) / 128;
            sum += v * v;
        }
        const rms = Math.sqrt(sum / bufferLength);

        // 👄 口の開き量（調整ポイント）
        const mouthValue = Math.min(Math.max(rms * 6.0, 0), 1);

        // ★★★ ここに置く ★★★
        if (lipSyncEnabled && vrm && vrm.expressionManager) {
            vrm.expressionManager.setValue("aa", mouthValue);
        }

        mouthAnimationId = requestAnimationFrame(loop);
    };

    loop();
}

function endAuction() {
    clearInterval(auctionTimer);
    alert(
        `落札者：${bids.value[0]?.user.name}さん（${bids.value[0]?.amount}円）`
    );
}
/*
function drawVRMFrame() {
    if (!vrm || !scene || !camera3d || !clock) return;

    const delta = clock.getDelta();
    vrm.update(delta);

    // VRM描画
    vrmRenderer.render(scene, camera3d);
    ctx.drawImage(vrmCanvas, 0, 0, canvas.width, canvas.height);

    // 背景
    if (bgMode.value === "image" && bgImg.complete) {
        ctx.globalCompositeOperation = "destination-over";
        drawContainBackground(bgImg, ctx, canvas.width, canvas.height);
        ctx.globalCompositeOperation = "source-over";
    }
}
*/
function drawFrame() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (renderMode.value === "camera") {
        drawCameraFrame();
    } else {
        drawVRMFrame();
    }

    requestAnimationFrame(drawFrame);
}

function drawCameraFrame() {
    if (!inputVideo || !inputVideo.videoWidth) return;

    // ★ まず黒で塗る（これが決定打）
    ctx.fillStyle = "#000";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    const videoAspect = inputVideo.videoWidth / inputVideo.videoHeight;
    const canvasAspect = canvas.width / canvas.height;
    let drawW,
        drawH,
        offsetX = 0,
        offsetY = 0;

    if (videoAspect > canvasAspect) {
        drawW = canvas.width;
        drawH = canvas.width / videoAspect;
        offsetY = (canvas.height - drawH) / 2;
    } else {
        drawH = canvas.height;
        drawW = canvas.height * videoAspect;
        offsetX = (canvas.width - drawW) / 2;
    }

    // 背景（任意）
    if (bgMode.value === "image" && bgImg.complete) {
        drawContainBackground(bgImg, ctx, canvas.width, canvas.height);
    }

    // カメラ
    ctx.drawImage(inputVideo, offsetX, offsetY, drawW, drawH);
}

function drawVRMFrame() {
    if (!vrm || !scene || !camera3d || !clock) return;

    const delta = clock.getDelta();

    if (waving && vrm) {
        wavePhase += delta * 5; // ← 速度

        const upper = vrm.humanoid.getNormalizedBoneNode("rightUpperArm");
        const lower = vrm.humanoid.getNormalizedBoneNode("rightLowerArm");
        const hand = vrm.humanoid.getNormalizedBoneNode("rightHand");

        if (upper) upper.rotation.x = 1.5; // 前に出す
        if (lower) lower.rotation.x = 1; // 少し曲げる   // 少し曲げる
        //if (hand)  hand.rotation.z  = Math.sin(wavePhase) * 0.7; // 手首：補助
        if (lower) lower.rotation.z = Math.sin(wavePhase) * 0.5 - 0.8;
    }

    if (shaking && vrm) {
      neckPhase += delta * 4; // 速度

      const neck = vrm.humanoid.getNormalizedBoneNode("neck");
      if (neck) {
          neck.rotation.y = Math.sin(neckPhase) * 0.5; // 左右
      }
  }
    vrm.update(delta);
    // ===== VRM更新 =====
    vrm.update(delta);
    if (mixer) {
        mixer.update(delta);
    }
    // ===== 描画 =====
    vrmRenderer.render(scene, camera3d);
    ctx.drawImage(vrmCanvas, 0, 0, canvas.width, canvas.height);

    // 背景
    if (bgMode.value === "image" && bgImg.complete) {
        ctx.globalCompositeOperation = "destination-over";
        drawContainBackground(bgImg, ctx, canvas.width, canvas.height);
        ctx.globalCompositeOperation = "source-over";
    }
}
function blinkOnce() {
    if (!vrm) return;

    // 閉じる
    vrm.expressionManager.setValue("blink", 1.0);

    // 120ms 後に開く
    setTimeout(() => {
        vrm.expressionManager.setValue("blink", 0.0);
    }, 120);
}
function mouthOpen() {
    if (!vrm) return;
    vrm.expressionManager.setValue("aa", 1.0);
}

function mouthClose() {
    if (!vrm) return;
    vrm.expressionManager.setValue("aa", 0.0);
}
function resetFace() {
    if (!vrm) return;

    const names = ["happy", "angry", "sad", "relaxed", "blink", "aa"];

    names.forEach((n) => {
        vrm.expressionManager.setValue(n, 0.0);
    });
}
function faceHappy() {
    resetFace();
    vrm.expressionManager.setValue("happy", 1.0);
    setTimeout(() => {
        resetFace();
    }, 1000);
}

function faceAngry() {
    resetFace();
    vrm.expressionManager.setValue("angry", 1.0);
    setTimeout(() => {
        resetFace();
    }, 1000);
}
function startMouth() {
    if (!vrm) return;

    lipSyncEnabled = false; // 自動口パク停止
    vrm.expressionManager.setValue("aa", 1.0); // 開けっ放し
}
function stopMouth() {
    if (!vrm) return;

    vrm.expressionManager.setValue("aa", 0.0); // 一旦閉じる
    lipSyncEnabled = true; // 自動口パク再開
}

function faceSad() {
    resetFace();
    vrm.expressionManager.setValue("sad", 1.0);
    setTimeout(() => {
        resetFace();
    }, 1000);
}

function faceRelaxed() {
    resetFace();
    vrm.expressionManager.setValue("relaxed", 1.0);
    setTimeout(() => {
        resetFace();
    }, 1000);
}

function setSmile() {
    faceHappy();
}
function setAngry() {
    faceAngry();
}
function waveHand() {
    if (!waveAction) return;

    // 連打対策
    if (waveAction.isRunning()) return;

    waveAction.reset();
    waveAction.play();
}
/*
function loadVrm(url, onLoaded) {
    vrmLoader.load(
        url,
        (gltf) => {
            const newVrm = gltf.userData.vrm;

            VRMUtils.removeUnnecessaryVertices(newVrm.scene);
            VRMUtils.removeUnnecessaryJoints(newVrm.scene);

            newVrm.scene.rotation.y = Math.PI;
            newVrm.scene.scale.setScalar(1.0);
            newVrm.scene.position.set(0, 0.5, 0);

            scene.add(newVrm.scene);
            onLoaded(newVrm);
        },
        (progress) => {
            console.log(
                "loading",
                Math.round((progress.loaded / progress.total) * 100),
                "%"
            );
        }
    );
}
    */
function loadVrm(url) {
    vrmLoader.load(url, (gltf) => {
        const newVrm = gltf.userData.vrm;

        // ← 最初はこれ外してOK
        // VRMUtils.removeUnnecessaryVertices(newVrm.scene);
        // VRMUtils.removeUnnecessaryJoints(newVrm.scene);

        newVrm.scene.rotation.y = Math.PI;
        newVrm.scene.position.set(0, 0.5, 0);

        replaceVrm(newVrm);
    });
}

function initThree() {
    scene = new THREE.Scene();

    camera3d = new THREE.PerspectiveCamera(30, 640 / 480, 0.1, 100);
    camera3d.position.set(0, 1.4, 2.5);

    const ambient = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambient);

    const dir = new THREE.DirectionalLight(0xffffff, 1.0);
    dir.position.set(0, 2, 2);
    scene.add(dir);

    vrmLoader = new GLTFLoader();
    vrmLoader.register((parser) => new VRMLoaderPlugin(parser));
}
</script>

<template>
    <AppLayout title="ライブ配信中">
        <!-- 🎉 ポップアップルーム設定フォーム -->
        <div
            v-if="showRoomForm"
            class="absolute inset-0 z-50 bg-black/60 text-white flex flex-col items-center justify-center text-center px-6"
        >
            <div
                class="bg-white rounded-lg p-6 max-w-md w-full shadow-lg space-y-4 bg-opacity-5"
            >
                <h1 class="text-2xl font-bold text-center text-white">
                    ルーム情報を入力
                </h1>

                <input
                    v-model="form.name"
                    class="w-full border text-black px-3 py-2 rounded"
                    placeholder="タイトル"
                />
                <textarea
                    v-model="form.description"
                    class="w-full border text-black px-3 py-2 rounded"
                    placeholder="メッセージ"
                />
                <select
                    v-model="form.category_id"
                    class="w-full border text-black px-3 py-2 rounded"
                >
                    <option disabled value="">カテゴリーを選択</option>
                    <option
                        v-for="cat in categories"
                        :key="cat.id"
                        :value="cat.id"
                    >
                        {{ cat.name }}
                    </option>
                </select>

                <button
                    @click="createRoomAndStart"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full"
                >
                    📡 配信開始123
                </button>
            </div>
        </div>

        <canvas
            id="canvas"
            class="fixed top-0 left-0 w-full h-full object-cover z-0 bg-transparent"
        />

        <div
            class="relative z-10 flex flex-col text-white bg-transparent"
            :style="{ height: containerHeight }"
        >
            <div
                class="absolute top-0 left-0 z-20 p-4 flex gap-2 bg-transparent"
            >
                <button
                    @click="toggleStreaming"
                    class="w-[64px] h-[64px] bg-transparent p-0 border-none outline-none"
                >
                    <img
                        :src="
                            isStreaming
                                ? '/assets/imgs/b2.png'
                                : '/assets/imgs/b1.png'
                        "
                        alt="配信ボタン"
                    />
                </button>
                <!--
        <a href="/dashboard" class="bg-red-500 text-white px-4 py-2 rounded">⛔ 配信停止</a>

        <button id="startRecord" class="bg-red-500 text-white px-4 py-2 rounded">🎥 録画開始</button>
        <button id="stopRecord" class="bg-gray-500 text-white px-4 py-2 rounded">⏹ 録画停止</button>
                -->
                <button
                    @click="
                        renderMode = renderMode === 'camera' ? 'vrm' : 'camera'
                    "
                    class="px-5 py-2 rounded-lg bg-gray-900/70 text-white text-sm font-bold tracking-wide border border-gray-500/40 hover:bg-gray-900 active:scale-95 transition-all"
                >
                    {{
                        renderMode === "camera"
                            ? "切替：カメラ → VRM"
                            : "切替：VRM → カメラ"
                    }}
                </button>
                <!-- 🔴 録画ボタン -->
                <button
                    @click="toggleRecording"
                    class="w-[64px] h-[64px] bg-transparent p-0 border-none outline-none"
                >
                    <img
                        :src="
                            isRecording
                                ? '/assets/imgs/b4.png'
                                : '/assets/imgs/b3.png'
                        "
                        alt="録画ボタン"
                    />
                </button>

                <select
                    @change="onBgChange($event)"
                    class="ml-4 bg-white text-black px-2 py-1 rounded"
                >
                    <option value="none">🚫 背景なし</option>
                    <option value="/bg.jpg">🏞️ 自然</option>
                    <option value="/bg2.jpg">🌇 バルーン</option>
                    <option value="/bg3.png">部屋</option>
                    <option value="/bg4.jpg">部屋2</option>
                </select>

                <label
                    class="w-[64px] h-[64px] p-0 border-none outline-none cursor-pointer bg-transparent"
                >
                    <img src="/assets/imgs/b6.png" alt="画像アップロード" />
                    <input
                        type="file"
                        accept="image/*"
                        @change="onUploadBg"
                        class="hidden"
                    />
                </label>
                <!-- オークション開始 / 停止トグルボタン（b7 / b8） 
                <button
                    @click="toggleAuction"
                    class="w-[64px] h-[64px] bg-transparent p-0 border-none outline-none"
                >
                    <img
                        :src="
                            isAuctionRunning
                                ? '/assets/imgs/b8.png'
                                : '/assets/imgs/b7.png'
                        "
                        alt="オークション"
                    />
                </button>
                -->
            </div>

            <!--
            <div
                class="absolute right-4 bg-black/70 text-white p-4 rounded shadow-lg z-20 w-50"
                :style="{ bottom: 'calc(60% + 8px)' }"
            >
                <div class="text-lg font-bold">
                    ⏱️ 残り時間：{{ remainingTime }} 秒
                </div>
                <div class="mt-2">
                    現在の入札価格：<span
                        class="text-yellow-400 font-bold text-xl"
                        >{{ currentBid }}円</span
                    >
                </div>
                <ul class="mt-2 max-h-32 overflow-y-auto text-sm">
                    <li
                        v-for="bid in bids"
                        :key="bid.id"
                        class="border-b border-white/20 py-1"
                    >
                        💸 {{ bid.user.name }} さん → {{ bid.amount }}円
                    </li>
                </ul>
                <button
                    @click="endAuction"
                    class="mt-3 w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded"
                >
                    ⏹ 終了
                </button>
            </div>
            -->

            <div
                v-if="renderMode === 'vrm'"
                class="absolute right-4 top-1/2 -translate-y-1/2 flex flex-col gap-2 z-30"
            >
                <button @click="blinkOnce" class="btn">👀 瞬き</button>
                <button
                    @mousedown="startWave"
                    @mouseup="stopWave"
                    @mouseleave="stopWave"
                    class="btn"
                >
                    👋 手を振る
                </button>
                <button @click="setSmile" class="btn">🙂 笑顔</button>
                <button @click="setAngry" class="btn">😠 怒る</button>

                <div class="h-px bg-white/30 my-1"></div>

                <button @click="faceHappy" class="btn">🙂 喜</button>
                <button @click="faceAngry" class="btn">😠 怒</button>
                <button @click="faceSad" class="btn">😢 哀</button>
                <button @click="faceRelaxed" class="btn">😌 楽</button>
                <button
                    @mousedown="startMouth"
                    @mouseup="stopMouth"
                    @mouseleave="stopMouth"
                    class="btn"
                >
                    👄 口パク
                </button>
                <button
                  @mousedown="startHeadShake"
                  @mouseup="stopHeadShake"
                  @mouseleave="stopHeadShake"
                  class="btn"
                >
                  🙅 首を振る
                </button>
                <button
                    @click="openAvatarSelector"
                    class="px-3 py-2 rounded bg-gray-900/70 text-white text-sm border border-gray-500/40 hover:bg-gray-800"
                >
                    🧍 アバター変更
                </button>
            </div>
            <div v-if="room && room.id">
                <div
                    class="absolute bottom-[120px] left-0 w-full h-1/2 p-4 overflow-y-auto z-10 bg-transparent"
                >
                    <RoomChat :room="room" :currentUser="user" />
                </div>
            </div>
            <div v-else>🔒 チャットルーム情報が見つかりません。</div>
        </div>
<div
    v-if="showAvatarSelector"
    class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center"
>
    <div class="bg-white rounded-lg p-6 w-[320px] max-h-[80vh] overflow-y-auto relative">
        <button
            @click="closeAvatarSelector"
            class="absolute top-2 right-2 text-gray-500 hover:text-black"
        >
            ✕
        </button>

        <h3 class="text-sm font-bold mb-3">アバターを選択</h3>

        <AvatarSelector
            @selected="onAvatarSelected"
        />
    </div>
</div>

    </AppLayout>
</template>

<style>
html,
body {
    background-color: transparent !important;
}
</style>
<style scoped>
.btn {
    width: 80px;
    padding: 6px;
    border-radius: 6px;
    background: #333;
    color: white;
    font-size: 14px;
}
.btn:hover {
    background: #555;
}
</style>
