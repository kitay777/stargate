let webRtcPeer;
let ws;
let currentRoomId = null;
let isPresenter = false;

export function startViewerDynamic(roomId, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return console.error("❌ container not found:", containerId);

  const video = document.createElement('video');
  video.id = `video-${roomId}`;
  video.autoplay = true;
  video.playsInline = true;
  video.muted = true;
  video.className = "w-full max-w-md border rounded my-2 bg-black";

  container.appendChild(video);

  const ws = new WebSocket(`wss://moon.timesfun.net:8443/call/${roomId}`);
  let webRtcPeer;

  ws.onopen = () => {
    const options = {
      remoteVideo: video,
      onicecandidate: (candidate) => {
        ws.send(JSON.stringify({
          id: 'onIceCandidate',
          room: roomId,
          candidate
        }));
      }
    };

    webRtcPeer = new kurentoUtils.WebRtcPeer.WebRtcPeerRecvonly(options, function (err) {
      if (err) return console.error(err);
      this.generateOffer((error, sdpOffer) => {
        if (error) return console.error(error);
        ws.send(JSON.stringify({ id: 'viewer', room: roomId, sdpOffer }));
      });
    });

    ws.onmessage = (msg) => {
      const parsed = JSON.parse(msg.data);
      if (parsed.id === 'viewerResponse') {
        webRtcPeer.processAnswer(parsed.sdpAnswer);
        console.log(`✅ stream received for room ${roomId}`);
      } else if (parsed.id === 'iceCandidate') {
        webRtcPeer.addIceCandidate(parsed.candidate);
      }
    };
  };
}

export function startViewer(roomId) {
  console.log("📺 startViewer roomId:", roomId);
  currentRoomId = roomId;

  const video = document.getElementById('video');
  if (!video) {
    console.error("❌ video element not found!");
    return;
  }

  video.muted = true;
  video.setAttribute("playsinline", true);

  ws = new WebSocket(`wss://moon.timesfun.net:8443/call/${roomId}`);

  ws.onopen = () => {
    console.log("✅ WebSocket connected");
    retryCount = 0; // ← 成功時にリセット！
    const options = {
      remoteVideo: video,
      onicecandidate: onIceCandidate
    };

    webRtcPeer = new kurentoUtils.WebRtcPeer.WebRtcPeerRecvonly(options, function (error) {
      if (error) return console.error("❌ WebRtcPeer error", error);

      this.generateOffer((err, offerSdp) => {
        if (err) return console.error("❌ SDP offer error", err);

        ws.send(JSON.stringify({
          id: 'viewer',
          room: roomId,
          sdpOffer: offerSdp
        }));
      });
    });

    
    ws.onmessage = (message) => {
      const parsedMessage = JSON.parse(message.data);
      console.log('📩 Received:', parsedMessage);

      switch (parsedMessage.id) {
        case 'viewerResponse':
          viewerResponse(parsedMessage);
          break;
        case 'iceCandidate':
          webRtcPeer.addIceCandidate(parsedMessage.candidate);
          break;
        case 'stopCommunication':
          dispose();
          break;
      }
    };

    ws.onerror = (err) => console.error("❌ WebSocket error:", err);
    ws.onclose = () => {
      console.warn("🔌 WebSocket closed");
      setTimeout(() => {
        retryViewer();
      }, 3000);
    };    
  };
}

let retryCount = 0;
const MAX_RETRIES = 5;

function retryViewer() {
  if (retryCount < MAX_RETRIES) {
    retryCount++;
    console.log(`🔁 Reconnecting... (${retryCount}/${MAX_RETRIES})`);
    stopViewer();
    startViewer(currentRoomId);
  } else {
    console.warn("🚫 Reconnect limit reached.");
  }
}

export function stopViewer() {
  if (webRtcPeer) {
    webRtcPeer.dispose();
    webRtcPeer = null;
  }
  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(JSON.stringify({ id: 'stop' }));
    ws.close();
  }
  ws = null;
}



function viewerResponse(message) {
  if (message.response !== 'accepted') {
    console.error('❌ Call not accepted:', message.message);
    dispose();
    return;
  }

  webRtcPeer.processAnswer(message.sdpAnswer, (err) => {
    if (err) return console.error("❌ SDP answer error", err);
    console.log("✅ SDP answer processed");

    const stream = new MediaStream(
      webRtcPeer.peerConnection.getReceivers().map(r => r.track).filter(Boolean)
    );

    const video = document.getElementById('video');
    video.srcObject = stream;
    video.play()
      .then(() => {
          console.log("▶️ 再生成功");
          setInterval(() => {
            if (video?.srcObject) {
              const track = video.srcObject.getVideoTracks()[0];
              if (track && track.readyState === 'ended') {
                console.warn("⚠️ Video track ended, restarting viewer");
                stopViewer();
                retryViewer();
              }
            }
          }, 5000);
  
        })
      .catch(e => console.warn("🔇 autoplay blocked", e));
      webRtcPeer.peerConnection.oniceconnectionstatechange = () => {
        const state = webRtcPeer.peerConnection.iceConnectionState;
        console.log("🌐 ICE State::", state);
      
        if (state === 'disconnected' || state === 'failed') {
          console.warn("⚠️ ICE failed → 再接続します");
      
          // クールダウン後に再接続
          setTimeout(() => {
            stopViewer();
            retryViewer();
          }, 3000);
        }
        if(state === 'new' ) {
              window.location.reload();
        }
        if(state === 'connected') {
          onIceCandidate(candidate);
        }
      };
  });
}

function onIceCandidate(candidate) {
  sendMessage({
    id: 'onIceCandidate',
    room: currentRoomId,
    candidate
  });
}

function dispose() {
  if (webRtcPeer) {
    webRtcPeer.dispose();
    webRtcPeer = null;
  }
  if (ws) {
    ws.close();
    ws = null;
  }
}

function sendMessage(message) {
  const json = JSON.stringify(message);
  console.log("📤 Sending:", json);
  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(json);
  } else {
    console.warn("⚠️ WebSocket not open. Message not sent:", json);
  }
}

window.startViewer = startViewer;
window.stopViewer = stopViewer;

window.startViewerDynamic = startViewerDynamic;
