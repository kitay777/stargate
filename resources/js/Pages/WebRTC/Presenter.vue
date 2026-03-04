<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import RoomChat from "@/Components/RoomChat.vue";
import { onMounted, ref, onBeforeUnmount } from "vue";
import { initPresenter } from "@/webrtc/presenter.js";
import { stopPresenter } from "@/webrtc/presenter.js";
import axios from "axios";
import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader";
import { VRMLoaderPlugin, VRMUtils } from "@pixiv/three-vrm";
import AvatarSelector from "@/Components/AvatarSelector.vue";
import { useForm } from "@inertiajs/vue3";
import * as PIXI from "pixi.js";
import { Live2DModel } from "pixi-live2d-display/cubism4";
import { watch } from "vue";

const showIntro = ref(true);
const isStreaming = ref(false);
const isRecording = ref(false);
const isAuctionRunning = ref(false);
const avatarLoading = ref(false);

//const renderMode = ref("camera"); // 'camera' | 'vrm'
const renderMode = ref("camera");
// 'camera' | 'vrm' | 'apng'

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
const vrmLoading = ref(true);

const vrmCanvas = document.createElement("canvas");
vrmCanvas.width = 640;
vrmCanvas.height = 480;

const vrmRenderer = new THREE.WebGLRenderer({
    canvas: vrmCanvas,
    alpha: true,
    antialias: true,
});
const currentAvatarId = ref(null);
// APNG用
const apngParts = ref(null);
const isBlinking = ref(false);
const isMouthOpen = ref(false);
const apngImages = {
    base: null,
    eyes_open: null,
    eyes_close: null,
    mouth_open: null,
    mouth_close: null,
};

vrmRenderer.setSize(640, 480);

let clothesVrm = null;
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
let live2dApp = null;
let live2dModel = null;
let animationId = null;
const live2dUrl = ref(null);
const showAvatarSelector = ref(false);

const avatarUrl = ref(null);

let neckPhase = 0;
let shaking = false;
const editingRoom = ref(false);

const roomForm = useForm({
    name: props.room?.name ?? "",
    description: props.room?.description ?? "",
    category_id: props.room?.category_id ?? null,
});

watch(renderMode, async (newMode) => {
if (newMode === "live2d") {
    if (live2dUrl.value) {
        await initLive2D();
    }
}

    if (newMode === "vrm") {
        if (!vrm && avatarUrl.value) {
            initVRM();
        }
    }

    if (newMode === "apng") {
        if (apngParts.value) {
            preloadApngImages();
        }
    }
});
function saveRoomInfo() {
    roomForm.put(route("rooms.update.personal", room.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingRoom.value = false;
            startPresenterOnce();
        },
    });
}
async function loadClothes(url) {
    return new Promise((resolve, reject) => {
        vrmLoader.load(
            url,
            (gltf) => {
                const cVrm = gltf.userData.vrm;
                resolve(cVrm);
            },
            undefined,
            reject
        );
    });
}

function startHeadShake() {
    shaking = true;
}

function stopHeadShake() {
    shaking = false;
    neckPhase = 0;

    const neck = vrm.humanoid.getNormalizedBoneNode("neck");
    if (neck) neck.rotation.set(0, 0, 0);
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

async function loadSelectedClothes(avatarId) {
    console.log("👕 選択中の服をロード:", avatarId);
    const res = await axios.get(`/api/avatar/${avatarId}/clothes/current`);
    console.log("現在選択中の服:", res.data);
    if (res.data?.file_path) {
        await attachClothes(`/storage/${res.data.file_path}`);
    }
}

function initVRM() {
    console.log("🔄 initVRM called -- initVRM");
    if (!avatarUrl.value) {
        console.warn("⚠️ avatarUrl not ready");
        return;
    }
    console.log("🔄 VRM初期化開始====start");
    vrmLoading.value = true;

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

    console.log("🔄 VRM読み込み開始:", avatarUrl.value);
    vrmLoader.load(avatarUrl.value, async (gltf) => {
        console.log("🔄 VRM読み込み完了:", avatarUrl.value);
        const loadedVrm = gltf.userData.vrm;

        loadedVrm.scene.rotation.y = Math.PI;
        loadedVrm.scene.scale.setScalar(0.5);
        loadedVrm.scene.position.set(0, 1, 0);

        vrm = loadedVrm; // ← 先に代入
        if (currentAvatarId.value) {
            await loadSelectedClothes(currentAvatarId.value);
        }

        scene.add(vrm.scene); // ← 最後に表示

        vrmLoading.value = false;
    });

    vrmLoading.value = false;
}
async function initLive2D() {
    if (live2dApp) return;

    if (!window.Live2DCubismCore) {
        console.error("Cubism Core not loaded");
        return;
    }

    live2dApp = new PIXI.Application({
        width: 640,
        height: 480,
        backgroundAlpha: 0,
    });
    console.log("Live2DApp created:>>>>>>>", live2dApp);
    live2dModel = await Live2DModel.from("/models/robot0910/robot.model3.json");
    live2dModel.interactive = false;
    // ⭐ モデルの実サイズを取得
    const bounds = live2dModel.getBounds();
    const modelHeight = bounds.height;

    // ⭐ 表示したい高さ（px）
    const targetHeight = 380; // ← ここで微調整

    const scale = targetHeight / modelHeight;

    live2dModel.scale.set(scale);

    // ⭐ 足元基準で中央配置
    live2dModel.anchor.set(0.5, 1);
    live2dModel.x = 640 / 2;
    live2dModel.y = 420;

    live2dApp.stage.addChild(live2dModel);

    live2dModel.motion("Idle", {
        loop: true,
        speed: 10.0,
    });
    live2dApp.ticker.add((delta) => {
        live2dModel.update(delta * 10);
    });
    vrmLoading.value = false;
}
// DB
/*
async function initLive2D() {
    if (!live2dUrl.value) return;

    // 🔥 既存破棄（超重要）
    if (live2dApp) {
        live2dApp.destroy(true);
        live2dApp = null;
        live2dModel = null;
    }

    if (!window.Live2DCubismCore) {
        console.error("Cubism Core not loaded");
        return;
    }

    live2dApp = new PIXI.Application({
        width: 640,
        height: 480,
        backgroundAlpha: 0,
    });

    live2dModel = await Live2DModel.from(live2dUrl.value);

    live2dModel.interactive = false;

    const bounds = live2dModel.getBounds();
    const modelHeight = bounds.height;

    const targetHeight = 380;
    const scale = targetHeight / modelHeight;
    live2dModel.scale.set(scale);

    live2dModel.anchor.set(0.5, 1);
    live2dModel.x = 320;
    live2dModel.y = 420;

    live2dApp.stage.addChild(live2dModel);

    // 🔥 motionは存在確認してから
    if (live2dModel.internalModel.motionManager.motionGroups["Idle"]) {
        live2dModel.motion("Idle", { loop: true });
    }

    vrmLoading.value = false;
}
*/
async function attachClothes(url) {
    if (!vrm) return;

    if (clothesVrm) {
        vrm.scene.remove(clothesVrm);
        clothesVrm = null;
    }

    console.log("服を読み込み中:", url);

    const normalLoader = new GLTFLoader();

    const gltf = await new Promise((resolve, reject) => {
        normalLoader.load(url, resolve, undefined, reject);
    });

    if (!gltf?.scene) {
        console.error("GLB load failed");
        return;
    }

    // 🔥 Body を特定
    let bodyMesh = null;

    vrm.scene.traverse((obj) => {
        if (obj.isSkinnedMesh && obj.name.toLowerCase().includes("body")) {
            bodyMesh = obj;
        }
    });

    console.log("Body mesh:", bodyMesh?.name);

    if (!bodyMesh) {
        console.error("Body mesh not found");
        return;
    }

    gltf.scene.traverse((obj) => {
        if (obj.isSkinnedMesh) {
            obj.bind(bodyMesh.skeleton, bodyMesh.bindMatrix);
            obj.skeleton = bodyMesh.skeleton;
            obj.frustumCulled = false;
        }
    });

    clothesVrm = gltf.scene;
    vrm.scene.add(clothesVrm);

    console.log("服バインド完了");
}

function toggleStreaming() {
    if (!isStreaming.value) {
        startPresenterOnce();
    } else {
        stopPresenter();
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

//const showRoomForm = ref(true);
const form = ref({
    name: "",
    description: "",
    category_id: null,
});
const categories = ref([]);

// 🔻 ページ表示時にカテゴリー一覧取得（必要なら）
function preloadApngImages() {
    console.log("🔄 APNG画像のプリロード開始");

    Object.keys(apngParts.value).forEach((key) => {
        const img = new Image();

        img.onload = () => {
            console.log("✅ APNG loaded:", key);
        };

        img.src = apngParts.value[key];
        apngImages[key] = img;
    });
}
function drawAPNGFrame() {
    console.log(
        "🎬 drawAPNGFrame - isBlinking:",
        isBlinking.value,
        "isMouthOpen:",
        isMouthOpen.value
    );
    if (!canvas || !ctx) return;
    if (!apngImages.base || !apngImages.base.complete) return;
    console.log("🎬 drawAPNGFrame - base image loaded, drawing frame");
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // 🌄 背景（あれば）
    if (bgMode.value === "image" && bgImg.complete) {
        drawContainBackground(bgImg, ctx, canvas.width, canvas.height);
    } else {
        // 背景なし時は黒
        ctx.fillStyle = "#000";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // ---- 基本比率 ----
    const imgAspect = 1 / 2;
    const canvasAspect = canvas.width / canvas.height;

    let drawW, drawH, offsetX, offsetY;

    if (canvasAspect > imgAspect) {
        drawH = canvas.height;
        drawW = drawH * imgAspect;
    } else {
        drawW = canvas.width;
        drawH = drawW / imgAspect;
    }

    const scale = 0.88;
    drawW *= scale;
    drawH *= scale;

    offsetX = (canvas.width - drawW) / 2;
    offsetY = (canvas.height - drawH) / 2 + canvas.height * 0.04;
    console.log("🎬 drawAPNGFrame - calculated dimensions:", {
        drawW,
        drawH,
        offsetX,
        offsetY,
    });
    // base
    ctx.drawImage(apngImages.base, offsetX, offsetY, drawW, drawH);

    // eyes
    ctx.drawImage(
        isBlinking.value ? apngImages.eyes_close : apngImages.eyes_open,
        offsetX,
        offsetY,
        drawW,
        drawH
    );

    // mouth
    ctx.drawImage(
        isMouthOpen.value ? apngImages.mouth_open : apngImages.mouth_close,
        offsetX,
        offsetY,
        drawW,
        drawH
    );
}

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

onMounted(async () => {
    canvas = document.getElementById("canvas");
    canvas.width = 640;
    canvas.height = 480;
    ctx = canvas.getContext("2d", { alpha: true });

    initThree();
    console.log("📹 Presenter.vue マウント完了");

    const res = await axios.get("/api/categories"); // Laravelで要定義
    categories.value = res.data;
    editingRoom.value = true;
    try {
        const res = await axios.get("/api/me");
        const avatar = res.data.avatar;
        currentAvatarId.value = avatar.id;

        console.log("👤 自分のアバター情報:", avatar);
        if (!avatar) return;

        renderMode.value = avatar ? avatar.type : "camera";

        console.log("👤 自分のアバタータイプ:", renderMode.value);
        console.log("👤 自分のアバターURL:", avatar.vrm_path);
        // VRM
        if (avatar.type === "vrm" && avatar.vrm_path) {
            renderMode.value = "vrm";
            avatarUrl.value = `/storage/${avatar.vrm_path}`;
            console.log("🔄 VRM読み込み(1):", avatarUrl.value);
            initVRM();
        }

        // APNG
        if (avatar.type === "apng") {
            renderMode.value = "apng";
            apngParts.value = avatar.apng_parts_urls;
            preloadApngImages();
            vrmLoading.value = false;
        }

        if (avatar.type === "live2d" && avatar.live2d_path) {
            renderMode.value = "live2d";

            live2dUrl.value = `/storage/${avatar.live2d_path}`; // ★DBから
            await initLive2D();
            vrmLoading.value = false;
        }
    } catch (e) {
        console.error("❌ failed to load avatar", e);
    }
    startBlinkLoop();

    if (props.room?.id != null) {
        room.value = props.room;
    }
    updateHeight();
    window.visualViewport?.addEventListener("resize", updateHeight);
    window.addEventListener("resize", updateHeight);

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
                /*
                setTimeout(() => {
                    startPresenterOnce();
                }, 500);
                */
            };
        });
    console.log(">>>>>>>>>>>>>>>>> renderMode:", renderMode.value);

    drawFrame();
});

onBeforeUnmount(() => {
    window.visualViewport?.removeEventListener("resize", updateHeight);
    window.removeEventListener("resize", updateHeight);
    stopPresenter();
    if (mouthAnimationId) {
        cancelAnimationFrame(mouthAnimationId);
        mouthAnimationId = null;
    }
    if (animationId) {
        cancelAnimationFrame(animationId);
        animationId = null;
    }
    stopPresenter();
    if (audioCtx) {
        audioCtx.close();
        audioCtx = null;
    }
});

window.addEventListener("beforeunload", () => {
    stopPresenter();
});

defineExpose({
    onUploadBg,
    onBgChange,
});

const currentBid = ref(0);
const bids = ref([]);
const remainingTime = ref(180);
let auctionTimer = null;

function startBlinkLoop() {
    setInterval(() => {
        isBlinking.value = true;
        setTimeout(() => {
            isBlinking.value = false;
        }, 120);
    }, 4000 + Math.random() * 2000);
}
function playExpression(name) {
    if (!live2dModel) return;
    live2dModel.expression(name);
    setTimeout(() => {
        live2dModel.expression();
    }, 500);
}

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
        if (renderMode.value === "apng") {
            isMouthOpen.value = mouthValue > 0.15;
        }
        if (renderMode.value === "live2d" && live2dModel) {
            live2dModel.internalModel.coreModel.setParameterValueById(
                "ParamMouthOpenY",
                mouthValue
            );
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
    }
    if (renderMode.value === "vrm") {
        drawVRMFrame();
    }
    if (renderMode.value === "apng") {
        drawAPNGFrame();
    }
    if (renderMode.value === "live2d") {
        drawLive2DFrame();
    }

    animationId = requestAnimationFrame(drawFrame);
}

function drawLive2DFrame() {
    if (!live2dApp || !live2dModel) return;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // 🌄 背景（あれば）
    if (bgMode.value === "image" && bgImg.complete) {
        drawContainBackground(bgImg, ctx, canvas.width, canvas.height);
    } else {
        // 背景なし時は黒
        ctx.fillStyle = "#000";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // Live2D描画
    live2dApp.renderer.render(live2dApp.stage);

    // 上に重ねる
    ctx.drawImage(live2dApp.view, 0, 0, canvas.width, canvas.height);
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

let basePose = {};

function saveBasePose() {
    ["leftUpperArm", "leftLowerArm", "rightUpperArm", "rightLowerArm"].forEach(
        (name) => {
            const bone = vrm.humanoid.getNormalizedBoneNode(name);
            if (bone) basePose[name] = bone.quaternion.clone();
        }
    );
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
    // ===== VRM更新 =====
    vrm.update(delta);
    if (mixer) {
        mixer.update(delta);
    }

    if (vrm && !waving && !shaking) {
        const leftUpper = vrm.humanoid.getNormalizedBoneNode("leftUpperArm");
        const rightUpper = vrm.humanoid.getNormalizedBoneNode("rightUpperArm");
        const leftLower = vrm.humanoid.getNormalizedBoneNode("leftLowerArm");
        const rightLower = vrm.humanoid.getNormalizedBoneNode("rightLowerArm");

        if (leftUpper) {
            leftUpper.rotation.x = -0.4;
            leftUpper.rotation.z = 1.1;
        }

        if (rightUpper) {
            rightUpper.rotation.x = -0.4;
            rightUpper.rotation.z = -1.1;
        }

        if (leftLower) {
            leftLower.rotation.x = 0.2;
            leftLower.rotation.y = -1.5;
            leftLower.rotation.z = 0.57;
        }

        if (rightLower) {
            rightLower.rotation.x = 0.2;
            rightLower.rotation.y = 1.5;
            rightLower.rotation.z = -0.57;
        }
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
        saveBasePose();
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
async function cycleRenderMode() {
    if (renderMode.value === "camera") {
        renderMode.value = "vrm";
        if (!vrm && avatarUrl.value) {
            initVRM(); // ← これ必須
        }
    } else if (renderMode.value === "vrm") {
        renderMode.value = "apng";
    } else if (renderMode.value === "apng") {
        renderMode.value = "live2d";
        initLive2D();
    } else if (renderMode.value === "live2d") {
        renderMode.value = "camera";
    } else {
        renderMode.value = "camera";
    }
}
function openStreamingDialog() {
    editingRoom.value = true;
}
</script>

<template>
    <AppLayout title="ライブ配信中">
        <!-- 🎉 ポップアップルーム設定フォーム -->
        <div
            v-if="vrmLoading"
            class="absolute inset-0 flex items-center justify-center bg-black z-50"
        >
            <div
                class="animate-spin w-8 h-8 border-4 border-white border-t-transparent rounded-full"
            ></div>
        </div>

        <canvas
            id="canvas"
            class="fixed left-0 w-full h-[calc(100%-64px)] top-16 object-cover z-0 pointer-events-none"
        />

        <div
            class="fixed top-16 inset-0 z-10 flex flex-col text-white bg-transparent"
            :style="{ height: containerHeight }"
        >
            <div
                class="absolute top- left-0 z-20 p-4 flex gap-2 bg-transparent"
            >
                <button
                    @click="openStreamingDialog"
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
                <button @click="cycleRenderMode()">
                    切替：{{ renderMode }}
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

            <!-- APNG 操作 -->
            <div
                v-if="renderMode === 'apng'"
                class="absolute right-4 top-[9rem] grid grid-cols-2 gap-2 z-50"
            >
                <button
                    @mousedown="isBlinking = true"
                    @mouseup="isBlinking = false"
                    @mouseleave="isBlinking = false"
                    @touchstart.prevent="isBlinking = true"
                    @touchend="isBlinking = false"
                    @touchcancel="isBlinking = false"
                    class="btn"
                >
                    <img src="/assets/imgs/b01.png" alt="瞬き" />
                </button>

                <button @click="$inertia.visit('/avatar/select')" class="btn">
                    <img src="/assets/imgs/b09.png" alt="アバター変更" />
                </button>
            </div>

            <div
                v-if="renderMode === 'vrm'"
                class="absolute right-4 top-[9rem] grid grid-cols-2 gap-2 z-50"
            >
                <button @click="blinkOnce" class="btn">
                    <img src="/assets/imgs/b01.png" alt="瞬き" />
                </button>

                <!-- 手を振る（完全安定版） -->
                <button
                    @pointerdown="startWave"
                    @pointerup="stopWave"
                    @pointerleave="stopWave"
                    @pointercancel="stopWave"
                    class="btn"
                >
                    <img src="/assets/imgs/b02.png" alt="手を振る" />
                </button>

                <button @click="faceHappy" class="btn">
                    <img src="/assets/imgs/b03.png" alt="喜" />
                </button>

                <button @click="faceAngry" class="btn">
                    <img src="/assets/imgs/b04.png" alt="怒" />
                </button>

                <button @click="faceSad" class="btn">
                    <img src="/assets/imgs/b05.png" alt="哀" />
                </button>

                <button @click="faceRelaxed" class="btn">
                    <img src="/assets/imgs/b06.png" alt="楽" />
                </button>

                <!-- 口 -->
                <button
                    @touchcancel="stopMouth"
                    @pointerdown="startMouth"
                    @pointerup="stopMouth"
                    @pointerleave="stopMouth"
                    @pointercancel="stopMouth"
                    class="btn"
                >
                    <img src="/assets/imgs/b07.png" alt="口開け" />
                </button>

                <!-- 首 -->
                <button
                    @pointerdown="startHeadShake"
                    @pointerup="stopHeadShake"
                    @pointerleave="stopHeadShake"
                    @pointercancel="stopHeadShake"
                    class="btn"
                >
                    <img src="/assets/imgs/b08.png" alt="首を振る" />
                </button>

                <button @click="$inertia.visit('/avatar/select')" class="btn">
                    <img src="/assets/imgs/b09.png" alt="アバター変更" />
                </button>
            </div>
            <div
                v-if="renderMode === 'live2d'"
                class="absolute right-4 top-[9rem] grid grid-cols-3 gap-3 z-50"
            >
                <button
                    class="circle-btn"
                    @click="playExpression('expression1')"
                >
                    1
                </button>
                <button
                    class="circle-btn"
                    @click="playExpression('expression2')"
                >
                    2
                </button>
                <button
                    class="circle-btn"
                    @click="playExpression('expression3')"
                >
                    3
                </button>
                <button
                    class="circle-btn"
                    @click="playExpression('expression4')"
                >
                    4
                </button>
                <button
                    class="circle-btn"
                    @click="playExpression('expression5')"
                >
                    5
                </button>
                <button
                    class="circle-btn"
                    @click="playExpression('expression6')"
                >
                    6
                </button>
                <button @click="$inertia.visit('/avatar/select')" class="btn">
                    <img src="/assets/imgs/b09.png" alt="アバター変更" />
                </button>
            </div>

            <div v-if="room && room.id">
                <div
                    class="absolute bottom-[120px] left-0 w-full h-[45%] p-4 overflow-y-auto z-10 bg-transparent"
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
            <div
                class="bg-white rounded-lg p-6 w-[320px] max-h-[80vh] overflow-y-auto relative"
            >
                <button
                    @click="closeAvatarSelector"
                    class="absolute top-2 right-2 text-gray-500 hover:text-black"
                >
                    ✕
                </button>

                <h3 class="text-sm font-bold mb-3">アバターを選択</h3>

                <AvatarSelector @selected="onAvatarSelected" />
            </div>
        </div>
        <div
            v-if="editingRoom"
            class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center"
        >
            <div class="bg-white rounded-lg p-6 w-[400px] space-y-4 text-black">
                <h3 class="text-lg font-bold">ルーム情報編集</h3>

                <input
                    v-model="roomForm.name"
                    class="w-full border px-3 py-2 rounded"
                    placeholder="タイトル"
                />

                <textarea
                    v-model="roomForm.description"
                    class="w-full border px-3 py-2 rounded"
                    placeholder="説明"
                />

                <select
                    v-model="roomForm.category_id"
                    class="w-full border px-3 py-2 rounded"
                >
                    <option
                        v-for="cat in categories"
                        :key="cat.id"
                        :value="cat.id"
                    >
                        {{ cat.name }}
                    </option>
                </select>

                <div class="flex justify-end gap-2">
                    <button
                        @click="editingRoom = false"
                        class="px-3 py-1 bg-gray-300 rounded"
                    >
                        キャンセル
                    </button>

                    <button
                        @click="saveRoomInfo"
                        class="px-3 py-1 bg-green-600 text-white rounded"
                    >
                        保存
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
html,
body {
    height: 100%;
    touch-action: none;
    background-color: transparent !important;
}
</style>
<style scoped>
.btn {
    width: 40px;
    height: 40px;
    padding: 0;
    background: transparent;
    border: none;
    outline: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;

    touch-action: none;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
}

.btn img {
    height: 40px;
    width: 40px;

    pointer-events: none;
    -webkit-user-drag: none;
    -webkit-touch-callout: none;
    user-select: none;
}

/* 押した感触だけ軽く */
.btn:active {
    transform: scale(0.95);
}

/* hover時にほんのり分かるように */
.btn:hover {
    filter: brightness(1.1);
}
.circle-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.2s;
}

.circle-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.circle-btn:active {
    transform: scale(0.9);
}
</style>
