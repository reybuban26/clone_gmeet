<template>
  <div class="prejoin-wrapper">
    <header class="header">
      <div class="header-logo">
        <svg viewBox="0 0 1024 1024" width="32" height="32">
          <path d="M170.666667 256h469.333333c46.933333 0 85.333333 38.4 85.333333 85.333333v341.333334c0 46.933333-38.4 85.333333-85.333333 85.333333H170.666667c-46.933333 0-85.333333-38.4-85.333334-85.333333V341.333333c0-46.933333 38.4-85.333333 85.333334-85.333333z" fill="#4CAF50"/>
          <path d="M938.666667 746.666667l-213.333334-128V405.333333l213.333334-128z" fill="#388E3C"/>
        </svg>
        <span class="logo-text">Meet</span>
      </div>
    </header>

    <main class="prejoin-main">
      <div class="preview-col">
        <video ref="rawVideoEl" autoplay playsinline muted class="hidden-ai-element"></video>
        <canvas ref="blurCanvasEl" width="640" height="480" class="hidden-ai-element"></canvas>
        <div class="video-container">
          <video
            ref="localVideoEl"
            autoplay
            muted
            playsinline
            :class="{ hidden: !cameraOn || permissionError }"
          ></video>

          <div v-if="!cameraOn || permissionError" class="camera-off-placeholder">
            <div v-if="!permissionError" class="avatar-circle">
              <svg viewBox="0 0 24 24" width="48" height="48" fill="white">
                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
              </svg>
            </div>
          </div>

          <div v-if="!micOn && !permissionError" class="top-right-mute">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="white">
              <path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/>
            </svg>
          </div>

          <div class="video-controls-overlay">
            <button
              class="ctrl-btn"
              :class="{ off: !micOn || permissionError }"
              @click="toggleMic"
              :disabled="!!permissionError"
              :title="micOn ? 'Turn off microphone' : 'Turn on microphone'"
            >
              <svg v-if="micOn && !permissionError" viewBox="0 0 24 24" width="20" height="20" fill="white">
                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
              </svg>
              <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="white">
                <path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17c0-.06.02-.11.02-.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/>
              </svg>
            </button>

            <button
              class="ctrl-btn"
              :class="{ off: !cameraOn || permissionError }"
              @click="toggleCamera"
              :disabled="!!permissionError"
              :title="cameraOn ? 'Turn off camera' : 'Turn on camera'"
            >
              <svg v-if="cameraOn && !permissionError" viewBox="0 0 24 24" width="20" height="20" fill="white">
                <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
              </svg>
              <svg v-else viewBox="0 0 24 24" width="20" height="20">
                <path fill="white" d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                <line x1="2" y1="2" x2="22" y2="22" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
              </svg>
            </button>

            <button
              class="ctrl-btn"
              :class="{ active: isBlurOn }"
              @click="toggleBlur"
              :disabled="isBlurLoading || !cameraOn || !!permissionError"
              title="Blur background"
            >
              <svg v-if="isBlurLoading" viewBox="0 0 24 24" width="20" height="20" class="spinner"><circle cx="12" cy="12" r="10" fill="none" stroke="white" stroke-width="3" stroke-dasharray="30" stroke-linecap="round"/></svg>
              <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 16a7 7 0 110-14 7 7 0 010 14zm-1-7h2v2h-2zm0-4h2v2h-2zm-4 4h2v2H7zm0-4h2v2H7zm8 4h2v2h-2zm0-4h2v2h-2z"/></svg>
            </button>
          </div>
        </div>

        <p v-if="permissionError" class="permission-error">
          {{ permissionError }}
        </p>
      </div>

      <div class="info-col">
        <h2 class="info-title">Ready to join?</h2>
        <p class="meeting-code-display">{{ route.params.code }}</p>

        <p v-if="isHost" class="host-badge">You are the host</p>
        <p v-else class="guest-badge">Joining as guest</p>

        <div class="info-actions">
          <button class="btn-join-now" @click="joinNow" :disabled="isJoining || !isCameraReady">
            <div v-if="isJoining || !isCameraReady" class="spinner"></div>
            <span v-else>Join now</span>
          </button>
          <button class="btn-cancel" @click="cancel">
            Cancel
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLogger } from '../composables/useLogger'

const SelfieSegmentation = window.SelfieSegmentation;
const route = useRoute()
const router = useRouter()
const { logAction } = useLogger()

const localVideoEl = ref(null)
const micOn = ref(true)
const cameraOn = ref(true)
const permissionError = ref('')
const isHost = ref(false)
const isJoining = ref(false) 
const isCameraReady = ref(false)

const rawVideoEl     = ref(null)
const blurCanvasEl   = ref(null)
const isBlurOn       = ref(false)
const isBlurLoading  = ref(false)
let selfieSegmentation = null
let blurRafId          = null
let processedStream    = null

let localStream = null

onMounted(async () => {
  isHost.value = sessionStorage.getItem('isHost') === 'true'
  await requestMedia()
})

onUnmounted(() => {
  stopTracks()
})

async function requestMedia() {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
    if (rawVideoEl.value) {
      rawVideoEl.value.srcObject = localStream;
      rawVideoEl.value.play().catch(()=>{}); // Piliting mag-play!
    }
    if (localVideoEl.value) localVideoEl.value.srcObject = localStream
    await logAction('media_permissions_granted', { meeting_code: route.params.code })
  } catch (err) {
    permissionError.value = 'Camera/microphone access denied. Please allow permissions and refresh.'
    await logAction('media_permissions_denied', {
      meeting_code: route.params.code,
      error: err.message,
    })
  } finally {
    isCameraReady.value = true 
  }
}

function toggleMic() {
  if (!localStream) return
  micOn.value = !micOn.value
  localStream.getAudioTracks().forEach(t => (t.enabled = micOn.value))
  logAction(micOn.value ? 'mic_unmuted' : 'mic_muted', { meeting_code: route.params.code })
}

function toggleCamera() {
  if (!localStream) return
  cameraOn.value = !cameraOn.value
  localStream.getVideoTracks().forEach(t => (t.enabled = cameraOn.value))
  logAction(cameraOn.value ? 'camera_turned_on' : 'camera_turned_off', {
    meeting_code: route.params.code,
  })
}

function joinNow() {
  isJoining.value = true
  logAction('user_joined_room', { meeting_code: route.params.code }).catch(() => {})
  sessionStorage.setItem('micOn', String(micOn.value))
  sessionStorage.setItem('cameraOn', String(cameraOn.value))
  sessionStorage.setItem('blurOn', String(isBlurOn.value)) // DAGDAG ITO
  
  sessionStorage.setItem('prejoined_' + route.params.code, 'true')
  router.push(`/meeting/${route.params.code}`)
}

function cancel() {
  stopTracks()
  router.push('/')
}

function stopTracks() {
  localStream?.getTracks().forEach(t => t.stop())
}

// ==========================
// BACKGROUND BLUR LOGIC
// ==========================
async function toggleBlur() {
  if (!cameraOn.value) return; 
  isBlurOn.value = !isBlurOn.value;

  if (isBlurOn.value) {
    isBlurLoading.value = true;
    if (!selfieSegmentation) {
      selfieSegmentation = new SelfieSegmentation({locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/${file}`});
      selfieSegmentation.setOptions({ modelSelection: 0 }); 
      selfieSegmentation.onResults(onBlurResults);
      await selfieSegmentation.initialize();
    }

    isBlurLoading.value = false;
    processBlurFrame(); 
    processedStream = blurCanvasEl.value.captureStream(30);

    if (localVideoEl.value) localVideoEl.value.srcObject = processedStream;

  } else {
    cancelAnimationFrame(blurRafId);
    isBlurLoading.value = false;
    if (localVideoEl.value) localVideoEl.value.srcObject = localStream; 
  }
}

function processBlurFrame() {
  if (!isBlurOn.value || !rawVideoEl.value) return;
  
  // DAGDAG ITO: Siguraduhing may laman at nagpi-play ang video bago ipasa sa AI
  if (rawVideoEl.value.readyState < 2) {
    blurRafId = requestAnimationFrame(processBlurFrame);
    return;
  }

  selfieSegmentation.send({ image: rawVideoEl.value }).then(() => {
    blurRafId = requestAnimationFrame(processBlurFrame);
  }).catch((err) => {
    console.error("AI Processing Error:", err);
    blurRafId = requestAnimationFrame(processBlurFrame);
  });
}

function onBlurResults(results) {
  const canvas = blurCanvasEl.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  canvas.width = results.image.width;
  canvas.height = results.image.height;

  ctx.save();
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // 1. MASK FEATHERING: Binabaan sa 1px para saktong tanggalin lang ang "bungi-bungi" (jagged edges) nang walang halo effect
  ctx.filter = 'blur(1px)'; 
  ctx.drawImage(results.segmentationMask, 0, 0, canvas.width, canvas.height);

  // 2. FOREGROUND: Ipatong ang original na tao (Tanging tao lang ang lilitaw)
  ctx.globalCompositeOperation = 'source-in';
  ctx.filter = 'none'; 
  ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);

  // 3. BACKGROUND: Ipatong sa likod yung blurred na paligid
  ctx.globalCompositeOperation = 'destination-over';
  ctx.filter = 'blur(12px)'; // 12px ang standard sweet spot ng Google Meet para sa natural depth of field
  ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);

  ctx.restore();
}

</script>

<style scoped>

/* ========================
   AI BLUR FIX
   ======================== */
.hidden-ai-element {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  opacity: 0 !important;
  pointer-events: none !important;
  z-index: -100 !important;
}

/* =========================================
   📱 MOBILE VIEW RESPONSIVENESS FIX
   ========================================= */
@media (max-width: 768px) {
  .prejoin-wrapper {
    height: 100dvh; /* Gamitin ang exact na taas ng phone screen */
  }
  
  .prejoin-main {
    padding: 20px 16px 40px; /* Dagdagan natin ng space sa ibaba para hindi dikit sa edge ng phone */
    gap: 24px;
    align-items: flex-start;
  }

  /* 1. Ayusin ang Video Size para hindi maging sobrang tangkad */
  .video-container {
    width: 100%;
    height: auto;
    aspect-ratio: 16 / 9; /* Sakto sa widescreen format ng camera */
  }

  /* 2. I-gitna ang mga text at buttons sa mobile */
  .info-col {
    align-items: center;
    text-align: center;
    width: 100%;
    max-width: 100%;
  }

  .info-actions {
    justify-content: center;
    width: 100%;
  }

  .btn-join-now, .btn-cancel {
    width: 100%; /* Gawing full-width ang buttons para madaling pindutin */
    justify-content: center;
  }
}

.prejoin-wrapper {
  position: fixed; /* I-lock sa buong screen para hindi gumalaw ang body */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%; /* Fixed height para walang lusot sa ilalim */
  background: #202124;
  display: flex;
  flex-direction: column;
  color: #fff;
  overflow-y: auto; /* Kung kailangang mag-scroll, sa loob lang ng dark background */
  z-index: 50; /* Siguraduhing nasa ibabaw ito ng lahat */
}

.btn-join-now {
  display: flex;
  align-items: center;
  gap: 8px;
}
.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.top-right-mute {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 28px;
  height: 28px;
  background: #ea4335; /* Meet red */
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  box-shadow: 0 1px 4px rgba(0,0,0,0.3);
}

.header {
  display: flex;
  align-items: center;
  padding: 14px 24px;
}
.header-logo { display: flex; align-items: center; gap: 8px; }
.logo-text { font-size: 20px; color: #e8eaed; font-weight: 400; }

.prejoin-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 60px;
  padding: 40px 24px;
  flex-wrap: wrap;
}

/* Preview */
.preview-col { display: flex; flex-direction: column; align-items: center; gap: 16px; }

.video-container {
  position: relative;
  width: 640px;
  max-width: 100%;
  height: 360px; /* Nilagyan natin ng fixed height para hindi mag-collapse */
  background: #3c4043;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
  flex-shrink: 0; /* Para hindi siya paliitin ng flexbox */
}

.video-container video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scaleX(-1);
}

/* Dito yung sikreto: opacity 0 imbes na display:none */
.video-container video.hidden { 
  opacity: 0; 
}

.camera-off-placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #3c4043;
}
.avatar-circle {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: #5e35b1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-controls-overlay {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 16px;
}

.ctrl-btn {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.1);
  outline: none;
  background: #3c4043;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s, transform 0.1s;
}
.ctrl-btn:hover:not(:disabled) { background: #4a4e51; }
.ctrl-btn:active:not(:disabled) { transform: scale(0.95); }
.ctrl-btn.off { background: #ea4335; border-color: transparent; }
.ctrl-btn.off:hover:not(:disabled) { background: #c5221f; }
.ctrl-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.ctrl-btn.active { background: #1a73e8; border-color: transparent; }
.ctrl-btn.active:hover:not(:disabled) { background: #1558b0; }

.permission-error {
  color: #f28b82; /* Mapulang pinkish red */
  font-size: 14px;
  text-align: center;
  max-width: 400px;
  margin-top: 4px;
}

/* Info */
.info-col {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  max-width: 380px;
}

.info-title {
  font-size: 36px;
  font-weight: 400;
  color: #e8eaed;
  margin: 0 0 8px 0;
}

.meeting-code-display {
  font-family: monospace;
  font-size: 16px;
  color: #9aa0a6;
  letter-spacing: 1.5px;
  margin: 0 0 20px 0;
}

.host-badge, .guest-badge {
  font-size: 13px;
  padding: 4px 12px;
  border-radius: 16px;
  font-weight: 500;
  margin: 0 0 24px 0;
}
.host-badge { background: #174ea6; color: #8ab4f8; }
.guest-badge { background: #37474f; color: #9aa0a6; }

.info-actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.btn-join-now {
  background: #1a73e8;
  color: #fff;
  border: none;
  border-radius: 24px;
  padding: 10px 24px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-join-now:hover { background: #1558b0; }

.btn-cancel {
  background: transparent;
  color: #e8eaed; /* White tulad ng screenshot */
  border: 1px solid #5f6368;
  border-radius: 24px;
  padding: 10px 24px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}
.btn-cancel:hover { background: rgba(255,255,255,0.04); border-color: #9aa0a6; }
</style>