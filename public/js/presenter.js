// presenter.js (修正版)

let webRtcPeer;
let ws;

let retryCount = 0;
const MAX_RETRIES = 5;
const RETRY_DELAY_MS = 3000;
let monitorInterval = null;

export function initPresenter(roomId, canvas) {
  console.log("🎥 initPresenter:", roomId);
  window.ROOM = roomId;
  window._PRESENTER_CANVAS = canvas;
  start();
}

export function stopPresenter() {
  console.log("🛑 stopPresenter called");
  stop();
}

function start() {
  const canvas = window._PRESENTER_CANVAS;
  if (!canvas) {
    console.warn("❌ canvas が未定義です");
    return;
  }

  ws = new WebSocket(`wss://moon.timesfun.net:8443/call/${ROOM}`);

  ws.onopen = () => {
    console.log("✅ WebSocket connected");

    const options = {
      videoStream: canvas.captureStream(30), // ✅ ここが canvas ベースの映像
      onicecandidate: (candidate) => {
        if (ws && ws.readyState === WebSocket.OPEN) {
          ws.send(JSON.stringify({ id: "onIceCandidate", room: ROOM, candidate }));
        }
      },
    };

    webRtcPeer = new kurentoUtils.WebRtcPeer.WebRtcPeerSendonly(options, function (error) {
      if (error) return console.error(error);
      this.generateOffer((error, offerSdp) => {
        if (error) return console.error("Offer error:", error);
        ws.send(JSON.stringify({ id: "presenter", room: ROOM, sdpOffer: offerSdp }));
      });

      webRtcPeer.peerConnection.oniceconnectionstatechange = () => {
        const state = webRtcPeer.peerConnection.iceConnectionState;
        console.log("🌐 [PRESENTER] ICE State:", state);
        if (state === 'disconnected' || state === 'failed') {
          console.warn("⚠️ ICE failed → reconnecting");
          setTimeout(() => {
            stop();
            start();
          }, RETRY_DELAY_MS);
        }
      };
    });
  };

  ws.onmessage = (message) => {
    const parsed = JSON.parse(message.data);
    switch (parsed.id) {
      case "presenterResponse":
        if (parsed.response === "accepted") {
          retryCount = 0;
          webRtcPeer.processAnswer(parsed.sdpAnswer);
        } else {
          console.error("❌ Presenter rejected:", parsed.message);
          stop();
          attemptReconnect();
        }
        break;
      case "iceCandidate":
        webRtcPeer.addIceCandidate(parsed.candidate);
        break;
    }
  };

  ws.onerror = (err) => {
    console.error("❌ WebSocket error:", err);
    attemptReconnect();
  };

  ws.onclose = () => {
    console.warn("🔌 WebSocket closed");
    attemptReconnect();
  };

  if (monitorInterval) clearInterval(monitorInterval);
  monitorInterval = setInterval(() => {
    const track = canvas.captureStream()?.getVideoTracks?.()[0];
    if (track && track.readyState === 'ended') {
      console.warn("⚠️ 映像トラックが終了 → 自動再接続");
      stop();
      attemptReconnect();
    }
  }, 5000);
}

function stop() {
  if (webRtcPeer) {
    webRtcPeer.dispose();
    webRtcPeer = null;
  }

  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify({ id: "stop" }));
  }

  if (ws) {
    ws.close();
    ws = null;
  }

  if (monitorInterval) {
    clearInterval(monitorInterval);
    monitorInterval = null;
  }

  console.log("🛑 配信停止");
}

function attemptReconnect() {
  if (retryCount >= MAX_RETRIES) {
    console.error("🚫 最大再接続回数に達しました");
    return;
  }

  retryCount++;
  console.log(`🔁 ${RETRY_DELAY_MS / 1000}s後に再接続 (${retryCount}/${MAX_RETRIES})`);
  setTimeout(() => {
    start();
  }, RETRY_DELAY_MS);
}

export function attachRecordEvents() {
  const startBtn = document.getElementById("startRecord");
  const stopBtn = document.getElementById("stopRecord");

  if (!startBtn || !stopBtn) {
    console.warn("🚫 録画ボタンがまだDOMに存在しません");
    return;
  }

  startBtn.onclick = () => {
    if (ws && ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify({ id: "startRecord", room: ROOM }));
      console.log("📡 録画開始リクエストを送信");
    } else {
      console.warn("WebSocket未接続です");
    }
  };

  stopBtn.onclick = () => {
    if (ws && ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify({ id: "stopRecord", room: ROOM }));
      console.log("📡 録画停止リクエストを送信");
    } else {
      console.warn("WebSocket未接続です");
    }
  };

  console.log("✅ 録画ボタンにイベント登録しました");
}
