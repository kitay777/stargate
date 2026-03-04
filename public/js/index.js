/*
 * (C) Copyright 2014 Kurento (http://kurento.org/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

var ws = new WebSocket('wss://moon.timesfun.net:8443/call');
var video;
var webRtcPeer;

window.onload = function() {
	//console = new Console();
	video = document.getElementById('video');
	disableStopButton();
}

window.onbeforeunload = function() {
	ws.close();
}

ws.onmessage = function(message) {
	console.log(message.sdpAnswer)
	var parsedMessage = JSON.parse(message.data);
	console.info('Received message: ' + message.data);

	switch (parsedMessage.id) {
	case 'presenterResponse':
		presenterResponse(parsedMessage);
		break;
	case 'viewerResponse':
		viewerResponse(parsedMessage);
		break;
	case 'iceCandidate':
		webRtcPeer.addIceCandidate(parsedMessage.candidate, function(error) {
			if (error)
				return console.error('Error adding candidate: ' + error);
		});
		break;
	case 'stopCommunication':
		dispose();
		break;
	default:
		console.error('Unrecognized message', parsedMessage);
	}
}

function isSafari() {
  return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
}

function presenterResponse(message) {
  if (message.response != 'accepted') {
    var errorMsg = message.message ? message.message : 'Unknown error';
    console.info('Call not accepted for the following reason: ' + errorMsg);
    dispose();
  } else {
    webRtcPeer.processAnswer(message.sdpAnswer, function(error) {
      if (error) {
        return console.error(error);
      }
      console.log("✅ Presenter SDP processed");

      const senders = webRtcPeer.peerConnection.getSenders();
      senders.forEach(sender => {
        if (sender.track && sender.track.kind === 'video') {
          console.log("📤 Sending video track:", sender.track);
        }
      });
    });
  }
}


function filterSdpForSafari(sdp) {
  const allowedPayloads = [];
  return sdp
    .split('\r\n')
    .filter(line => {
      if (line.startsWith('a=rtpmap:')) {
        if (line.includes('VP8') || line.includes('VP9') || line.includes('AV1')) {
          return false;
        } else {
          // 例えば H264 の payload type を保持する
          const match = line.match(/a=rtpmap:(\d+)/);
          if (match) allowedPayloads.push(match[1]);
        }
      } else if (line.startsWith('a=fmtp:') || line.startsWith('a=rtcp-fb:')) {
        // fmtp や rtcp-fb の対象が VP8/9/AV1 用なら消す
        const pt = line.match(/a=(?:fmtp|rtcp-fb):(\d+)/);
        if (pt && !allowedPayloads.includes(pt[1])) {
          return false;
        }
      } else if (line.includes('red') || line.includes('ulpfec') || line.includes('flexfec') || line.includes('rtx')) {
        return false;
      }
      return true;
    })
    .join('\r\n');
}
/*
function filterSdpForSafari(sdp) {
  return sdp
    .split('\r\n')
    .filter(line =>
      !line.includes('VP8') &&
      !line.includes('VP9') &&
      !line.includes('AV1') &&
      !line.includes('red') &&
      !line.includes('ulpfec') &&
      !line.includes('flexfec') &&
      !line.includes('rtx') &&
      !line.startsWith('a=rtcp-fb:') &&
      !line.startsWith('a=fmtp:')
    )
    .join('\r\n');
}
*/


function viewerResponse(message) {
  if (message.response != 'accepted') {
    var errorMsg = message.message ? message.message : 'Unknown error';
    console.info('Call not accepted for the following reason: ' + errorMsg);
    dispose();
  } else {
    webRtcPeer.processAnswer(message.sdpAnswer, function(error) {
      if (error) {
        return console.error(error);
      }
      console.log("✅ SDP processed, waiting for stream...");

      const receivers = webRtcPeer.peerConnection.getReceivers();
      const stream = new MediaStream(receivers.map(r => r.track).filter(t => t));

      video.srcObject = stream;

      video.onloadedmetadata = () => {
        console.log("🎬 Metadata loaded, trying to play...");
        video.play().then(() => {
          console.log("▶️ Video is playing.");
        }).catch(e => {
          console.error("🔴 Video play failed:", e);
        });
      };

      webRtcPeer.peerConnection.getReceivers().forEach(receiver => {
        if (receiver.track && receiver.track.kind === 'video') {
          console.log("🎥 Got video track:", receiver.track);
        }
      });
    });
  }
}


/*
function viewerResponse(message) {
	if (message.response != 'accepted') {
		var errorMsg = message.message ? message.message : 'Unknow error';
		console.info('Call not accepted for the following reason: ' + errorMsg);
		dispose();
	} else {
		webRtcPeer.processAnswer(message.sdpAnswer, function(error) {
			if (error)
				return console.error(error);
		});
	}
}
*/
function viewerResponse(message) {


const receivers = webRtcPeer.peerConnection.getReceivers();
const stream = new MediaStream(receivers.map(r => r.track).filter(t => t));
video.srcObject = stream;
video.play().catch(e => console.error("🔴 Video play error:", e));



  if (message.response != 'accepted') {
    var errorMsg = message.message ? message.message : 'Unknown error';
    console.info('Call not accepted for the following reason: ' + errorMsg);
    dispose();
  } else {
    webRtcPeer.processAnswer(message.sdpAnswer, function(error) {
      if (error) {
        return console.error(error);
      }
      console.log("✅ SDP processed, waiting for stream...");

      // ここでSafariでもVideoトラックが来てるか確認できる！
      webRtcPeer.peerConnection.getReceivers().forEach(receiver => {
        if (receiver.track && receiver.track.kind === 'video') {
          console.log("🎥 Got video track:", receiver.track);
        }
      });
    });
  }
}


function presenter() {
	if (!webRtcPeer) {
		showSpinner(video);

		var options = {
  localVideo: video, // または remoteVideo
  onicecandidate: onIceCandidate,
	  /*
  configuration: {
    iceServers: [
      {
        urls: "stun:stun.l.google.com:19302"
      },
      {
        urls: "turn:turn.picton.jp:3478?transport=udp",
        username: "kitayama",
        credential: "celica77"
      }
    ]
  }
  */
configuration: isSafari()
  ? {
      iceTransportPolicy: "relay",
      iceServers: [
        {
          urls: "turn:turn.picton.jp:3478?transport=udp",
          username: "kitayama",
          credential: "celica77"
        }
      ]
    }
  : {
      iceServers: [
        {
          urls: "stun:stun.l.google.com:19302"
        },
        {
          urls: "turn:turn.picton.jp:3478?transport=udp",
          username: "kitayama",
          credential: "celica77"
        }
      ]
    }
};
		webRtcPeer = new kurentoUtils.WebRtcPeer.WebRtcPeerSendonly(options,
				function(error) {
					if (error) {
						return console.error(error);
					}
					webRtcPeer.generateOffer(onOfferPresenter);
				});

		enableStopButton();
	}
}

function onOfferPresenter(error, offerSdp) {
	if (error)
		return console.error('Error generating the offer');
	console.info('Invoking SDP offer callback function ' + location.host);

	console.log('Original SDP offer:\n' + offerSdp);

	var filteredSdp = filterSdpForSafari(offerSdp);
	const sdpOfferToSend = isSafari() ? filterSdpForSafari(offerSdp) : offerSdp;

	console.log('Sending SDP:\n' + sdpOfferToSend);

	var message = {
		id : 'presenter',
		sdpOffer : sdpOfferToSend
	}
	/*
	var message = {
		id : 'presenter',
		sdpOffer : filteredSdp
	}
	*/
	sendMessage(message);
}


function isSafari() {
  return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
}

function viewer() {
	if (!webRtcPeer) {
		showSpinner(video);

		var options = {
  remoteVideo: video, // または remoteVideo
  onicecandidate: onIceCandidate,
			/*
  configuration: {
    iceServers: [
      {
        urls: "stun:stun.l.google.com:19302"
      },
      {
        urls: "turn:turn.picton.jp:3478?transport=udp",
        username: "kitayama",
        credential: "celica77"
      }
    ]
  }
*/
configuration: isSafari()
  ? {
      iceTransportPolicy: "relay",
      iceServers: [
        {
          urls: "turn:turn.picton.jp:3478?transport=udp",
          username: "kitayama",
          credential: "celica77"
        }
      ]
    }
  : {
      iceServers: [
        {
          urls: "stun:stun.l.google.com:19302"
        },
        {
          urls: "turn:turn.picton.jp:3478?transport=udp",
          username: "kitayama",
          credential: "celica77"
        }
      ]
    }
};
		webRtcPeer = new kurentoUtils.WebRtcPeer.WebRtcPeerRecvonly(options,
				function(error) {
					if (error) {
						return console.error(error);
					}
					this.generateOffer(onOfferViewer);
				});

		enableStopButton();
	}
}

function onOfferViewer(error, offerSdp) {
	if (error)
		return console.error('Error generating the offer');
	console.info('Invoking SDP offer callback function ' + location.host);

console.log('Original SDP offer:\n' + offerSdp);

	var filteredSdp = filterSdpForSafari(offerSdp);
		const sdpOfferToSend = isSafari() ? filterSdpForSafari(offerSdp) : offerSdp;
	console.log('Sending SDP:\n' + sdpOfferToSend);

		var message = {
		id : 'viewer',
		sdpOffer : sdpOfferToSend
	}
	/*
	var message = {
		id : 'viewer',
		sdpOffer : filteredSdp 
	}
	*/
	sendMessage(message);
}

function onIceCandidate(candidate) {
	console.log("Local candidate" + JSON.stringify(candidate));


	var message = {
		id : 'onIceCandidate',
		candidate : candidate
	};
	sendMessage(message);
}

function stop() {
	var message = {
		id : 'stop'
	}
	sendMessage(message);
	dispose();
}

function dispose() {
	if (webRtcPeer) {
		webRtcPeer.dispose();
		webRtcPeer = null;
	}
	hideSpinner(video);

	disableStopButton();
}

function disableStopButton() {
	enableButton('#presenter', 'presenter()');
	enableButton('#viewer', 'viewer()');
	disableButton('#stop');
}

function enableStopButton() {
	disableButton('#presenter');
	disableButton('#viewer');
	enableButton('#stop', 'stop()');
}

function disableButton(id) {
	$(id).attr('disabled', true);
	$(id).removeAttr('onclick');
}

function enableButton(id, functionName) {
	$(id).attr('disabled', false);
	$(id).attr('onclick', functionName);
}

function sendMessage(message) {
	var jsonMessage = JSON.stringify(message);
	console.log('Sending message: ' + jsonMessage);
	ws.send(jsonMessage);
}

function showSpinner() {
	for (var i = 0; i < arguments.length; i++) {
		arguments[i].poster = './img/transparent-1px.png';
		arguments[i].style.background = 'center transparent url("./img/spinner.gif") no-repeat';
	}
}

function hideSpinner() {
	for (var i = 0; i < arguments.length; i++) {
		arguments[i].src = '';
		arguments[i].poster = './img/webrtc.png';
		arguments[i].style.background = '';
	}
}

/**
 * Lightbox utility (to display media pipeline image in a modal dialog)
 */
$(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
	event.preventDefault();
	$(this).ekkoLightbox();
});
