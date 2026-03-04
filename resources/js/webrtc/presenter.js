let webRtcPeer;
let ws;

let retryCount = 0;
const MAX_RETRIES = 5;
const RETRY_DELAY_MS = 3000;
let monitorInterval = null;

let ROOM = null;
let TRACKS = null;
let audioCtx = null;
let analyser = null;
let audioSource = null;
let mouthAnimationId = null;

// ===============================
// 初期化（Vue から呼ばれる）
// ===============================
export function initPresenter(roomId, tracks) {
  console.log("🎥 initPresenter:", roomId);

  ROOM = roomId;
  TRACKS = tracks;

  if (!TRACKS?.videoTrack) {
    console.error("❌ videoTrack missing");
    return;
  }

  start();
}
export function stopPresenter() {
  console.log("🛑 stopPresenter called");
  stop();
}


// ===============================
// 配信開始
// ===============================
function start() {
  const videoTrack = TRACKS.videoTrack;
  const audioTrack = TRACKS.audioTrack;

  ws = new WebSocket(`wss://moon.timesfun.net:8443/call/${ROOM}`);

  ws.onopen = () => {
    console.log("✅ WebSocket connected");

    const options = {
      videoStream: new MediaStream([videoTrack]),
      audioStream: audioTrack ? new MediaStream([audioTrack]) : null,

      onicecandidate: (candidate) => {
        if (ws && ws.readyState === WebSocket.OPEN) {
          ws.send(JSON.stringify({
            id: "onIceCandidate",
            room: ROOM,
            candidate
          }));
        }
      },
    };

    webRtcPeer = new kurentoUtils.WebRtcPeer.WebRtcPeerSendonly(
      options,
      function (error) {
        if (error) {
          console.error(error);
          return;
        }

        this.generateOffer((error, offerSdp) => {
          if (error) {
            console.error("Offer error:", error);
            return;
          }

          ws.send(JSON.stringify({
            id: "presenter",
            room: ROOM,
            sdpOffer: offerSdp
          }));
        });

        // ICE 切断監視（重要）
        webRtcPeer.peerConnection.oniceconnectionstatechange = () => {
          const state = webRtcPeer.peerConnection.iceConnectionState;
          console.log("🌐 ICE State:", state);

          if (state === "disconnected") {
            console.warn("⚠️ ICE failed → reconnect");
            attemptReconnect();
          }
        };
      }
    );
  };

  ws.onmessage = (message) => {
    const parsed = JSON.parse(message.data);

    switch (parsed.id) {
      case "presenterResponse":
        if (parsed.response === "accepted") {
          retryCount = 0;
          webRtcPeer.processAnswer(parsed.sdpAnswer);
        } else {
          stop();
          attemptReconnect();
        }
        break;

      case "iceCandidate":
        webRtcPeer.addIceCandidate(parsed.candidate);
        break;
    }
  };

ws.onerror = () => {
  console.warn("⚠️ WebSocket error");
  attemptReconnect();
};

ws.onclose = () => {
  console.warn("🔌 WebSocket closed");
  // attemptReconnect();
};

  // ===============================
  // videoTrack 監視（canvas停止対策）
  // ===============================
  if (monitorInterval) clearInterval(monitorInterval);
  /*
  monitorInterval = setInterval(() => {
    if (videoTrack.readyState === "ended") {
      console.warn("⚠️ videoTrack ended → reconnect");
      stop();
      attemptReconnect();
    }
  }, 5000);
  */
}

// ===============================
// 停止
// ===============================
function stop() {
  if (webRtcPeer) {
    webRtcPeer.dispose();
    webRtcPeer = null;
  }

  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify({ id: "stop", room: ROOM }));
    ws.close();
  } else if (ws) {
    ws.close();
  }
 

  ws = null;

  if (monitorInterval) {
    clearInterval(monitorInterval);
    monitorInterval = null;
  }

  console.log("🛑 配信停止");
}

// ===============================
// 再接続（← 消えてない）
// ===============================
let reconnecting = false;

function attemptReconnect() {
  if (reconnecting) return;
  reconnecting = true;

  if (retryCount >= MAX_RETRIES) return;

  retryCount++;
  console.log(`🔁 reconnect (${retryCount}/${MAX_RETRIES})`);

  setTimeout(() => {
    reconnecting = false;
    start();
  }, RETRY_DELAY_MS);
}

