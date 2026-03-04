// viewer-core.js - マルチビュー対応版

const peers = {}; // roomId => WebRtcPeer
const sockets = {}; // roomId => WebSocket

function startViewerDynamic(roomId, containerId) {
    const container = document.getElementById(containerId);
    if (!container)
        return console.error("❌ container not found:", containerId);
    if (peers[roomId]) {
        console.warn(`⚠️ viewer already started for room ${roomId}`);
        return;
    }

    const video = document.createElement("video");
    video.id = `video-${roomId}`;
    video.autoplay = true;
    video.playsInline = true;
    video.muted = true;
    video.className = "w-full max-w-md border rounded my-2 bg-black";

    // ✅ ここ！
    video.addEventListener("loadeddata", () => {
        console.log("📺 video loadeddata fired — trying play()");
        video.play().catch((err) => {
            console.warn("⚠️ video.play() failed:", err);
        });
    });

    container.appendChild(video);
    sockets[roomId] = ws;

    ws.onopen = () => {
        const options = {
            remoteVideo: video,
            onicecandidate: (candidate) => {
                ws.send(
                    JSON.stringify({
                        id: "onIceCandidate",
                        room: roomId,
                        candidate,
                    })
                );
            },
        };

        const peer = new kurentoUtils.WebRtcPeer.WebRtcPeerRecvonly(
            options,
            function (err) {
                if (err) return console.error(err);
                this.generateOffer((error, sdpOffer) => {
                    if (error) return console.error(error);
                    ws.send(
                        JSON.stringify({ id: "viewer", room: roomId, sdpOffer })
                    );
                });
            }
        );

        peers[roomId] = peer;

        ws.onmessage = (msg) => {
            const parsed = JSON.parse(msg.data);
            if (parsed.id === "viewerResponse") {
                peer.processAnswer(parsed.sdpAnswer);
                console.log(`✅ stream received for room ${roomId}`);

                // 🔔 ここで streamStarted を発火！
                if (onStreamStartedCallbacks[roomId]) {
                    try {
                        onStreamStartedCallbacks[roomId]();
                    } catch (e) {
                        console.warn(
                            `⚠️ onStreamStartedCallback error for room ${roomId}`,
                            e
                        );
                    } finally {
                        delete onStreamStartedCallbacks[roomId];
                    }
                }
            } else if (parsed.id === "iceCandidate") {
                peer.addIceCandidate(parsed.candidate);
            } else if (parsed.id === "stopCommunication") {
                stopViewer(roomId);
            }
        };

        ws.onclose = () => {
            console.warn(`🔌 WebSocket closed for room ${roomId}`);
            stopViewer(roomId);
        };

        ws.onerror = (e) => {
            console.error(`❌ WebSocket error for room ${roomId}`, e);
        };
    };
}

function stopViewer(roomId) {
    const video = document.getElementById(`video-${roomId}`);
    if (video) {
        video.srcObject = null;
        video.remove();
    }

    if (peers[roomId]) {
        peers[roomId].dispose();
        delete peers[roomId];
    }

    if (sockets[roomId]) {
        try {
            sockets[roomId].send(JSON.stringify({ id: "stop" }));
        } catch (e) {
            console.warn(`⚠️ Error sending stop to room ${roomId}:`, e);
        } finally {
            sockets[roomId].close();
            delete sockets[roomId];
        }
    }
    if (onStreamEndedCallbacks[roomId]) {
        try {
            onStreamEndedCallbacks[roomId](); // 🔔 コールバック実行！
        } catch (e) {
            console.warn(
                `⚠️ onStreamEndedCallback error for room ${roomId}`,
                e
            );
        } finally {
            delete onStreamEndedCallbacks[roomId]; // メモリリーク防止
        }
    }
    // ✅ Viewer停止コールバックを呼び出す（あれば）
    if (onStreamEndedCallbacks[roomId]) {
        try {
            onStreamEndedCallbacks[roomId]();
        } catch (e) {
            console.warn(
                `⚠️ onStreamEndedCallback error for room ${roomId}`,
                e
            );
        } finally {
            delete onStreamEndedCallbacks[roomId]; // メモリリーク防止
        }
    }
}

window.startViewerDynamic = startViewerDynamic;
window.stopViewer = stopViewer;

const onStreamStartedCallbacks = {}; // roomId => callback

export function setStreamStartedCallback(roomId, cb) {
    onStreamStartedCallbacks[roomId] = cb;
}

const onStreamEndedCallbacks = {}; // roomId => callback

export function setStreamEndedCallback(roomId, cb) {
    onStreamEndedCallbacks[roomId] = cb;
}

export function startViewer(roomId, videoElement, onStreamReady) {
    const ws = new WebSocket(`wss://moon.timesfun.net:8443/call/${roomId}`);

    ws.onopen = () => {
        const options = {
            remoteVideo: videoElement,
            onicecandidate: (candidate) => {
                ws.send(
                    JSON.stringify({
                        id: "onIceCandidate",
                        room: roomId,
                        candidate,
                    })
                );
            },
        };

        const peer = new kurentoUtils.WebRtcPeer.WebRtcPeerRecvonly(
            options,
            function (err) {
                if (err) return console.error(err);
                this.generateOffer((error, sdpOffer) => {
                    if (error) return console.error(error);
                    ws.send(
                        JSON.stringify({ id: "viewer", room: roomId, sdpOffer })
                    );
                });
            }
        );

        peer.peerConnection.addEventListener("track", (event) => {
            if (onStreamReady) {
                console.log("📡 track event: setting stream to video");
                onStreamReady(event.streams[0]);
            }
        });
    };
}
