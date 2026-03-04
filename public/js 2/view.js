
let webRtcPeer;
let ws;
ws = new WebSocket(`wss://moon.timesfun.net:8443/call/${ROOM}`);

document.getElementById('viewer').addEventListener('click', startViewer);
document.getElementById('stop').addEventListener('click', stop);

function startViewer() {
  const roomName = document.getElementById('room').value.trim();
  console.log(">>>>>>>>>roomName: " + roomName);
  if (!roomName) {
    alert("Please enter a room name");
    return;
  }

  ws = new WebSocket('wss://moon.timesfun.net:8443/call/' + roomName);

console.log(">>>>>>>>>>ws>>>>"+roomName);

  ws.onmessage = function (message) {
    const parsedMessage = JSON.parse(message.data);
    console.log('📩 Received message: ' + message.data);

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

  ws.onopen = () => {
    console.log("✅ WebSocket connected");

    const video = document.getElementById('video');
    const options = {
      remoteVideo: video,
      onicecandidate: onIceCandidate
    };
    if (!video) {
      console.error("❌ video element not found!");
    } else {
      console.log("✅ video element found");
    }

    webRtcPeer = new kurentoUtils.WebRtcPeer.WebRtcPeerRecvonly(options, function (error) {
      if (error) return console.error("❌ Error creating WebRtcPeer:", error);
      this.generateOffer(onOfferViewer);
    });
  };

  ws.onerror = (err) => {
    console.error("❌ WebSocket error:", err);
  };

  ws.onclose = () => {
    console.warn("🔌 WebSocket closed");
  };

  ws.onMounted = () => {
    checkStreamStatus()
    setInterval(checkStreamStatus, 5000) // 5秒ごとにチェック
  }
}

function onOfferViewer(error, offerSdp) {
  if (error) return console.error('❌ Error generating the offer');

  const message = {
    id: 'viewer',
    room: document.getElementById('room').value.trim(),
    sdpOffer: offerSdp
  };
  sendMessage(message);
}

function viewerResponse(message) {
  console.log("viewstart");
  if (message.response !== 'accepted') {
    console.error('❌ Call not accepted: ', message.message);
    dispose();
  } else {
	  webRtcPeer.processAnswer(message.sdpAnswer, function (err) {
      if (err) return console.error(err);
      console.log("✅ SDP answer processed");
      

      const stream = new MediaStream(
        webRtcPeer.peerConnection.getReceivers().map(r => r.track).filter(Boolean)
      );
      console.log("📺 stream tracks:", stream.getTracks());

      const video = document.getElementById('video');
      video.srcObject = stream;
      video.play().then(() => {
        console.log("▶️ 再生開始成功");
      }).catch(e => {
        console.warn("🔇 autoplay blocked", e);
      });
    });
    console.log("✅ SDP answer processed");
  }
}

function onIceCandidate(candidate) {
  const message = {
    id: 'onIceCandidate',
    room: document.getElementById('room').value.trim(), // 追加！
    candidate: candidate
  };
  sendMessage(message);
}

function stop() {
  if (webRtcPeer) {
    const message = { id: 'stop' };
    sendMessage(message);
    dispose();
  }
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
  const jsonMessage = JSON.stringify(message);
  console.log('📤 Sending message: ' + jsonMessage);

  if (ws && ws.readyState === WebSocket.OPEN) {
    ws.send(jsonMessage);
  } else {
    console.warn('⚠️ WebSocket not open. Message not sent:', jsonMessage);
  }
}

