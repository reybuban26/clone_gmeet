<template>
  <div class="prejoin-container">
    <div class="top-right-actions">
      <div class="menu-wrapper">
        <button class="icon-btn" @click.stop="showTopMenu = !showTopMenu" title="More options">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
        </button>
        
        <Transition name="fade">
          <div v-if="showTopMenu" class="top-dropdown" @click.stop>
            <button class="dropdown-item" @click="alert('Settings coming soon')">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.06-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.06,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.49-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg>
              Settings
            </button>
            <button class="dropdown-item" @click="alert('Captions coming soon')">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19 4H5c-1.11 0-2 .9-2 2v12c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 7H9.5v-.5h-2v3h2V13H11v1.5c0 .28-.22.5-.5.5h-3c-.28 0-.5-.22-.5-.5v-4c0-.28.22-.5.5-.5h3c.28 0 .5.22.5.5V11zm7 0h-1.5v-.5h-2v3h2V13H18v1.5c0 .28-.22.5-.5.5h-3c-.28 0-.5-.22-.5-.5v-4c0-.28.22-.5.5-.5h3c.28 0 .5.22.5.5V11z"/></svg>
              Turn on captions
            </button>
            <button class="dropdown-item">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
              Report a problem
            </button>
            <button class="dropdown-item">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
              Report abuse
            </button>
            <button class="dropdown-item">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/></svg>
              Troubleshooting & help
            </button>

            <div class="dropdown-divider"></div>
            <button class="dropdown-item accordion-toggle" @click="showOtherOptions = !showOtherOptions">
              <div class="accordion-title">
                 <span>Other joining options</span>
                 <svg :class="{ 'rotated': showOtherOptions }" viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
              </div>
            </button>
            <div v-if="showOtherOptions" class="accordion-content">
              <button class="dropdown-item sub-item" @click="presentMeeting">
                 <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M21 3H3c-1.11 0-2 .89-2 2v14c0 1.11.89 2 2 2h18c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 16H3V5h18v14zm-9-2h9V7h-9v10z"/></svg>
                 Present
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>

    <div class="main-content" @click="showTopMenu = false">
      <div class="video-section">
        <div class="video-card">
          <video ref="rawVideoEl" class="hidden-ai-element" autoplay playsinline muted></video>
          <canvas ref="blurCanvasEl" class="hidden-ai-element" width="640" height="480"></canvas>
          
          <video ref="displayVideoEl" class="main-video mirrored" autoplay playsinline muted></video>
          
          <div v-if="!cameraOn" class="camera-off-overlay">
            <div class="avatar-circle">
              <svg viewBox="0 0 24 24" width="48" height="48" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </div>
          </div>

          <div v-if="isCamStarting" class="video-loading">
             <div class="spinner"></div>
             <span>Camera is starting...</span>
          </div>
          <div v-else-if="isAiLoading" class="video-loading">
             <div class="spinner"></div>
             <span>Applying effect...</span>
          </div>

          <div class="video-controls-overlay">
            <div class="center-controls">
              <button class="round-btn" :class="{ 'btn-off': !micOn }" @click="toggleMic" :title="micOn ? 'Turn off microphone' : 'Turn on microphone'">
                <svg v-if="micOn" viewBox="0 0 24 24" width="22" height="22" fill="white"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                <svg v-else viewBox="0 0 24 24" width="22" height="22" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
              </button>
              <button class="round-btn" :class="{ 'btn-off': !cameraOn }" @click="toggleCamera" :title="cameraOn ? 'Turn off camera' : 'Turn on camera'">
                <svg v-if="cameraOn" viewBox="0 0 24 24" width="22" height="22" fill="white"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                <svg v-else viewBox="0 0 24 24" width="22" height="22" fill="white"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/><line x1="2" y1="2" x2="22" y2="22" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>
              </button>
            </div>
            <div class="right-controls">
              <button class="sparkle-btn" @click="showEffectsPanel = true" title="Apply visual effects">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="white">
                  <path d="M5.99988 16.938V19.059L5.05851 20H2.93851L5.99988 16.938ZM22.0015 14.435V16.557L18.5595 20H17.9935L17.9939 18.443L22.0015 14.435ZM8.74988 14H15.2446C16.1628 14 16.9158 14.7071 16.9888 15.6065L16.9946 15.75V20H15.4946V15.75C15.4946 15.6317 15.4124 15.5325 15.3019 15.5066L15.2446 15.5H8.74988C8.63154 15.5 8.5324 15.5822 8.50648 15.6927L8.49988 15.75V20H6.99988V15.75C6.99988 14.8318 7.70699 14.0788 8.60636 14.0058L8.74988 14ZM8.02118 10.4158C8.08088 10.9945 8.26398 11.5367 8.54372 12.0154L1.99951 18.56V16.438L8.02118 10.4158ZM22.0015 9.932V12.055L17.9939 16.065L17.9946 15.75L17.9896 15.5825C17.9623 15.128 17.8246 14.7033 17.6029 14.3348L22.0015 9.932ZM12.0565 4L1.99951 14.06V11.939L9.93551 4H12.0565ZM22.0015 5.432V7.555L16.3346 13.2245C16.0672 13.1089 15.7779 13.0346 15.4746 13.0095L15.2446 13L14.6456 13.0005C14.9874 12.6989 15.2772 12.3398 15.4999 11.9384L22.0015 5.432ZM11.9999 7.00046C13.6567 7.00046 14.9999 8.34361 14.9999 10.0005C14.9999 11.6573 13.6567 13.0005 11.9999 13.0005C10.343 13.0005 8.99988 11.6573 8.99988 10.0005C8.99988 8.34361 10.343 7.00046 11.9999 7.00046ZM11.9999 8.50046C11.1715 8.50046 10.4999 9.17203 10.4999 10.0005C10.4999 10.8289 11.1715 11.5005 11.9999 11.5005C12.8283 11.5005 13.4999 10.8289 13.4999 10.0005C13.4999 9.17203 12.8283 8.50046 11.9999 8.50046ZM7.55851 4L1.99951 9.56V7.438L5.43751 4H7.55851ZM21.0565 4L15.9091 9.14895C15.7923 8.61022 15.5669 8.11194 15.2571 7.67816L18.9345 4H21.0565ZM16.5585 4L14.0148 6.54427C13.5362 6.26463 12.9942 6.08157 12.4157 6.02181L14.4365 4H16.5585Z" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="right-section">
        
        <div v-if="!showEffectsPanel" class="join-card">
          <h2 class="join-title">Ready to join?</h2>
          <p class="join-code">{{ route.params.code }}</p>
          
          <div class="join-actions-wrapper">
            <div class="join-actions">
              <button class="btn-join" :class="{'joining': isJoining}" @click="joinMeeting" :disabled="isJoining">
                <template v-if="!isJoining">Join now</template>
                <template v-else><div class="spinner-mini"></div> Joining...</template>
              </button>
              <button class="btn-present" @click="presentMeeting">Present</button>
            </div>
            <button class="btn-cancel" @click="goBack">Cancel</button>
          </div>
        </div>

        <div v-else class="effects-panel">
          <div class="panel-header">
            <h3>Effects</h3>
            <button class="panel-close" @click="showEffectsPanel = false">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
          </div>

          <div class="effects-tabs">
            <button :class="{ active: effectsTab === 'backgrounds' }" @click="effectsTab = 'backgrounds'">Backgrounds</button>
            <button :class="{ active: effectsTab === 'filters' }" @click="effectsTab = 'filters'">Filters</button>
            <button :class="{ active: effectsTab === 'appearance' }" @click="effectsTab = 'appearance'">Appearance</button>
          </div>

          <div class="effects-scroll-area">
            
            <div v-if="effectsTab === 'backgrounds'" class="tab-pane">
              <div class="effects-grid">
                <button class="effect-btn none-btn" :class="{ active: activeEffect === 'none' }" @click="setVideoEffect('none')">
                  <svg viewBox="0 0 24 24" width="24" height="24" fill="#9aa0a6"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.59-12.42L10 14.17l-2.59-2.58L6 13l4 4 8-8z"/></svg>
                  <span class="eff-label">No effect</span>
                </button>
                <button class="effect-btn blur-btn" :class="{ active: activeEffect === 'blur-light' }" @click="setVideoEffect('blur-light')">
                  <svg viewBox="0 0 24 24" width="24" height="24" fill="#9aa0a6"><path d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 16a7 7 0 110-14 7 7 0 010 14zm-1-7h2v2h-2zm0-4h2v2h-2zm-4 4h2v2H7zm0-4h2v2H7zm8 4h2v2h-2zm0-4h2v2h-2z"/></svg>
                  <span class="eff-label">Slightly blur</span>
                </button>
                <button class="effect-btn blur-btn" :class="{ active: activeEffect === 'blur-heavy' }" @click="setVideoEffect('blur-heavy')">
                  <svg viewBox="0 0 24 24" width="24" height="24" fill="#9aa0a6"><path d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 16a7 7 0 110-14 7 7 0 010 14zm-1-7h2v2h-2zm0-4h2v2h-2zm-4 4h2v2H7zm0-4h2v2H7zm8 4h2v2h-2zm0-4h2v2h-2z"/></svg>
                  <span class="eff-label">Blur</span>
                </button>
              </div>

              <div v-for="category in bgCategories" :key="category.name" class="bg-category">
                <h4 class="cat-title">{{ category.name }}</h4>
                <div class="effects-grid">
                  <button v-for="bg in category.items" :key="bg.id" class="effect-btn img-btn" :class="{ active: activeEffect === bg.url }" @click="setVideoEffect(bg.url)">
                    <img :src="bg.url" alt="bg">
                  </button>
                </div>
              </div>
            </div>

            <div v-if="effectsTab === 'filters'" class="tab-pane">
               <p class="tab-desc">Fun filters and animations (Coming Soon)</p>
               <div class="effects-grid">
                 <div class="filter-placeholder">🐱</div>
                 <div class="filter-placeholder">🕶️</div>
                 <div class="filter-placeholder">🎩</div>
               </div>
            </div>

            <div v-if="effectsTab === 'appearance'" class="tab-pane">
               <div class="appearance-card">
                 <h4>Touch up</h4>
                 <p>Subtly smooths complexion and brightens under eyes</p>
                 <label class="switch"><input type="checkbox" v-model="touchUpOn" @change="updateEffects"><span class="slider round"></span></label>
               </div>
               <div class="appearance-card">
                 <h4>Lighting</h4>
                 <p>Adjusts video brightness automatically</p>
                 <label class="switch"><input type="checkbox" v-model="lightingOn" @change="updateEffects"><span class="slider round"></span></label>
               </div>
               <div class="appearance-card">
                 <h4>Framing</h4>
                 <p>Keeps you in the center of the video</p>
                 <label class="switch"><input type="checkbox" v-model="framingOn" @change="updateEffects"><span class="slider round"></span></label>
               </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const SelfieSegmentation = window.SelfieSegmentation;
const router = useRouter()
const route = useRoute()

// UI States
const micOn = ref(true)
const cameraOn = ref(true)
const showTopMenu = ref(false)
const showOtherOptions = ref(false)
const showEffectsPanel = ref(false)

const isJoining = ref(false)
const isCamStarting = ref(true)

// Elements
const rawVideoEl = ref(null)
const blurCanvasEl = ref(null)
const displayVideoEl = ref(null)

// Effects States
const effectsTab = ref('backgrounds')
const activeEffect = ref('none')
const touchUpOn = ref(false)
const lightingOn = ref(false)
const framingOn = ref(false)
const isAiLoading = ref(false)

const isHost = ref(false)

let localStream = null
let processedStream = null
let blurRafId = null
let selfieSegmentation = null
const customBgImage = new Image()

// Backgrounds (Parehas sa MeetingRoom)
const bgCategories = ref([
  {
    name: 'Professional',
    items: [
      { id: 'prof1', url: '/backgrounds/professional1.jpeg' },
      { id: 'prof2', url: '/backgrounds/professional2.jpeg' },
      { id: 'prof3', url: '/backgrounds/professional3.jpeg' },
      { id: 'prof4', url: '/backgrounds/professional4.jpeg' },
      { id: 'prof5', url: '/backgrounds/professional5.jpeg' },
      { id: 'prof6', url: '/backgrounds/professional6.jpeg' },
      { id: 'prof7', url: '/backgrounds/professional7.jpeg' },
      { id: 'prof8', url: '/backgrounds/professional8.jpeg' },
      { id: 'prof9', url: '/backgrounds/professional9.jpeg' },
      { id: 'prof10', url: '/backgrounds/professional10.jpeg' },
    ]
  },
  {
    name: 'Creative',
    items: [
      { id: 'crea1', url: '/backgrounds/creatives11.jpeg' },
      { id: 'crea2', url: '/backgrounds/creatives12.jpeg' },
      { id: 'crea3', url: '/backgrounds/creatives13.jpeg' },
      { id: 'crea4', url: '/backgrounds/creatives14.jpeg' },
      { id: 'crea5', url: '/backgrounds/creatives15.jpeg' },
      { id: 'crea6', url: '/backgrounds/creatives16.jpeg' },
      { id: 'crea7', url: '/backgrounds/creatives17.jpeg' },
      { id: 'crea8', url: '/backgrounds/creatives18.jpeg' },
      { id: 'crea9', url: '/backgrounds/creatives19.jpeg' },
      { id: 'crea10', url: '/backgrounds/creatives20.jpeg' },
    ]
  }
])

function presentMeeting() {
  sessionStorage.setItem('autoPresent', 'true');
  joinMeeting();
}

onMounted(async () => {
  isHost.value = sessionStorage.getItem('isHost') === 'true'
  micOn.value = sessionStorage.getItem('micOn') !== 'false'
  cameraOn.value = sessionStorage.getItem('cameraOn') !== 'false'

  try {
    localStream = await navigator.mediaDevices.getUserMedia({ 
      video: { 
        width: { ideal: 1920 }, 
        height: { ideal: 1080 },
        frameRate: { ideal: 30, max: 60 }     
      },
      audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true } 
    })
    
    isCamStarting.value = false; // <-- TANGGALIN ANG LOADING PAGKATAPOS MAG-ALLOW

    if (rawVideoEl.value) {
      rawVideoEl.value.srcObject = localStream;
      rawVideoEl.value.play().catch(()=>{}); 
    } 

    if (!processedStream && blurCanvasEl.value) {
      processedStream = blurCanvasEl.value.captureStream(30);
    }
    const processedTrack = processedStream.getVideoTracks()[0];
    const finalStream = new MediaStream([processedTrack, ...localStream.getAudioTracks()]);
    
    if (displayVideoEl.value) {
      displayVideoEl.value.srcObject = finalStream;
      displayVideoEl.value.play().catch(()=>{}); 
    }
    processSimpleFrame(); 

  } catch (err) {
    console.error("No camera/mic access", err)
    cameraOn.value = false
    micOn.value = false
    isCamStarting.value = false; // <-- TANGGALIN DIN ANG LOADING KUNG NA-DENY
  }
})

onUnmounted(() => {
  cancelAnimationFrame(blurRafId)
  localStream?.getTracks().forEach(t => t.stop())
  processedStream?.getTracks().forEach(t => t.stop())
})

function toggleMic() {
  micOn.value = !micOn.value
  if (localStream) {
    localStream.getAudioTracks().forEach(t => t.enabled = micOn.value)
  }
}

function toggleCamera() {
  cameraOn.value = !cameraOn.value
  if (localStream) {
    localStream.getVideoTracks().forEach(t => t.enabled = cameraOn.value)
  }
  if (!cameraOn.value) {
    cancelAnimationFrame(blurRafId)
  } else {
    updateEffects()
  }
}

// FIX: ITO ANG PIPIGIL SA WHITE SCREEN BUG
function joinMeeting() {
  isJoining.value = true; 
  
  sessionStorage.setItem('micOn', micOn.value)
  sessionStorage.setItem('cameraOn', cameraOn.value)
  sessionStorage.setItem('prejoined_' + route.params.code, 'true')
  
  // DAGDAG ITO: I-save ang lahat ng napiling effects bago tumawid!
  sessionStorage.setItem('activeEffect', activeEffect.value)
  sessionStorage.setItem('touchUpOn', touchUpOn.value)
  sessionStorage.setItem('lightingOn', lightingOn.value)
  sessionStorage.setItem('framingOn', framingOn.value)
  
  setTimeout(() => {
    cancelAnimationFrame(blurRafId)
    if (localStream) {
      localStream.getTracks().forEach(t => t.stop())
    }
    if (processedStream) {
      processedStream.getTracks().forEach(t => t.stop())
    }
    if (selfieSegmentation) {
      selfieSegmentation.close();
    }
    router.push(`/meeting/${route.params.code}`)
  }, 500); 
}

function goBack() {
  cancelAnimationFrame(blurRafId)
  if (localStream) {
    localStream.getTracks().forEach(t => t.stop())
  }
  if (processedStream) {
    processedStream.getTracks().forEach(t => t.stop())
  }
  router.push('/')
}

// ==========================
// VISUAL EFFECTS LOGIC
// ==========================
async function setVideoEffect(effectType) {
  if (!cameraOn.value) return;
  activeEffect.value = effectType;
  if (effectType !== 'none' && effectType !== 'blur-light' && effectType !== 'blur-heavy') {
    customBgImage.src = effectType;
  }
  await updateEffects();
}

async function updateEffects() {
  if (!cameraOn.value) return;

  const hasBgEffect = activeEffect.value !== 'none';
  const hasAppearance = touchUpOn.value || lightingOn.value || framingOn.value;

  // 1. Itigil ang anumang umiikot na loop ngayon
  cancelAnimationFrame(blurRafId); 

  // 2. Kung AI Background ang napili, i-load muna ang AI nang tahimik (Anti-Blink Fix)
  if (hasBgEffect && !selfieSegmentation) {
    isAiLoading.value = true;
    selfieSegmentation = new SelfieSegmentation({locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/${file}`});
    selfieSegmentation.setOptions({ modelSelection: 0 }); 
    selfieSegmentation.onResults(onBlurResults);
    await selfieSegmentation.initialize(); 
    isAiLoading.value = false;
  }

  // 3. Simulan ang tamang drawing loop
  if (hasBgEffect) {
    processBlurFrame(); 
  } else {
    processSimpleFrame();
  }
}

function processSimpleFrame() {
  if (!cameraOn.value || activeEffect.value !== 'none') return;
  
  if (rawVideoEl.value && rawVideoEl.value.readyState >= 2) {
    const canvas = blurCanvasEl.value;
    const ctx = canvas.getContext('2d');
    canvas.width = rawVideoEl.value.videoWidth || 640;
    canvas.height = rawVideoEl.value.videoHeight || 480;

    ctx.save();
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (framingOn.value) {
      ctx.translate(canvas.width / 2, canvas.height / 2);
      ctx.scale(1.2, 1.2);
      ctx.translate(-canvas.width / 2, -canvas.height / 2);
    }

    let filters = [];
    if (lightingOn.value) filters.push('brightness(1.35)');
    if (touchUpOn.value) filters.push('contrast(1.1) saturate(1.2) sepia(0.08)');
    
    ctx.filter = filters.length ? filters.join(' ') : 'none';
    ctx.drawImage(rawVideoEl.value, 0, 0, canvas.width, canvas.height);
    ctx.restore();
  }
  blurRafId = requestAnimationFrame(processSimpleFrame);
}

function processBlurFrame() {
  if (activeEffect.value === 'none' || !rawVideoEl.value) return;
  if (rawVideoEl.value.readyState < 2) {
    blurRafId = requestAnimationFrame(processBlurFrame);
    return;
  }
  selfieSegmentation.send({ image: rawVideoEl.value }).then(() => {
    blurRafId = requestAnimationFrame(processBlurFrame);
  }).catch(() => {
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

  if (framingOn.value) {
    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.scale(1.2, 1.2); 
    ctx.translate(-canvas.width / 2, -canvas.height / 2);
  }

  ctx.filter = 'blur(1px)'; 
  ctx.drawImage(results.segmentationMask, 0, 0, canvas.width, canvas.height);

  ctx.globalCompositeOperation = 'source-in';
  
  let filters = [];
  if (lightingOn.value) filters.push('brightness(1.35)');
  if (touchUpOn.value) filters.push('contrast(1.1) saturate(1.2) sepia(0.08)');
  ctx.filter = filters.length > 0 ? filters.join(' ') : 'none';
  
  ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);

  ctx.globalCompositeOperation = 'destination-over';
  if (activeEffect.value === 'blur-light') {
    ctx.filter = 'blur(8px)'; 
    ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);
  } else if (activeEffect.value === 'blur-heavy' || activeEffect.value === 'blur') { // Suportado parehas na files
    ctx.filter = 'blur(24px)'; 
    ctx.drawImage(results.image, 0, 0, canvas.width, canvas.height);
  } else if (customBgImage.complete && customBgImage.src) {
    ctx.filter = 'none';
    const scale = Math.max(canvas.width / customBgImage.width, canvas.height / customBgImage.height);
    const x = (canvas.width / 2) - (customBgImage.width / 2) * scale;
    const y = (canvas.height / 2) - (customBgImage.height / 2) * scale;
    ctx.drawImage(customBgImage, x, y, customBgImage.width * scale, customBgImage.height * scale);
  }

  ctx.restore();
}
</script>

<style scoped>

.btn-join.joining {
  display: flex; 
  align-items: center; 
  justify-content: center; 
  gap: 8px; 
  opacity: 0.8; 
  cursor: not-allowed;
}
.spinner-mini {
  width: 16px; 
  height: 16px; 
  border: 2px solid rgba(32, 33, 36, 0.3); 
  border-top-color: #202124; 
  border-radius: 50%; 
  animation: spin 1s linear infinite;
}

.prejoin-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #202124;
  color: white;
  font-family: 'Roboto', sans-serif;
  position: relative;
}

/* TOP RIGHT 3-DOTS MENU */
.top-right-actions {
  position: absolute;
  top: 24px;
  right: 24px;
  z-index: 50;
}
.menu-wrapper { position: relative; }
.icon-btn {
  background: transparent;
  border: none;
  color: #e8eaed;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.icon-btn:hover { background: rgba(255, 255, 255, 0.1); }

.top-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  background: #2d2e30;
  border-radius: 8px;
  width: 280px;
  padding: 8px 0;
  box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}
.dropdown-item {
  width: 100%;
  background: transparent;
  border: none;
  color: #e8eaed;
  text-align: left;
  padding: 12px 20px;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: background 0.2s;
}
.dropdown-item:hover { background: rgba(255, 255, 255, 0.08); }
.dropdown-divider { height: 1px; background: #3c4043; margin: 8px 0; }
.accordion-toggle { justify-content: space-between; }
.accordion-title { display: flex; align-items: center; justify-content: space-between; width: 100%; }
.accordion-title svg { transition: transform 0.3s; }
.accordion-title svg.rotated { transform: rotate(180deg); }
.sub-item { padding-left: 56px; }

/* MAIN CONTENT */
.main-content {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 40px;
  max-width: 1200px;
  width: 100%;
  padding: 40px;
}

/* LEFT: VIDEO */
.video-section {
  flex: 1;
  min-width: 320px;
  max-width: 720px;
  display: flex;
  justify-content: flex-end;
}
.video-card {
  position: relative;
  width: 100%;
  aspect-ratio: 16/9;
  background: #3c4043;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
.main-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.main-video.mirrored { transform: scaleX(-1); }

.camera-off-overlay {
  position: absolute;
  inset: 0;
  background: #3c4043;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}
.avatar-circle {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: #1a73e8;
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-loading {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  z-index: 5;
  color: #8ab4f8;
  font-size: 14px;
  font-weight: 500;
}
.spinner {
  width: 24px; height: 24px;
  border: 3px solid rgba(138, 180, 248, 0.3);
  border-top-color: #8ab4f8;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* VIDEO OVERLAYS */
.video-controls-overlay {
  position: absolute;
  bottom: 16px;
  left: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10;
}
.center-controls {
  display: flex;
  gap: 16px;
}
.round-btn {
  width: 44px; height: 44px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.2);
  background: rgba(60, 64, 67, 0.8);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.round-btn:hover { background: rgba(60, 64, 67, 1); border-color: transparent; }
.round-btn.btn-off { background: #ea4335; border-color: transparent; }
.round-btn.btn-off:hover { background: #d93025; }

.right-controls {
  position: absolute;
  right: 16px;
}
.sparkle-btn {
  background: transparent; border: none; cursor: pointer;
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.2s;
}
.sparkle-btn:hover { background: rgba(255, 255, 255, 0.1); }

/* RIGHT: JOIN INFO OR EFFECTS */
.right-section {
  flex: 0 1 400px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* JOIN CARD */
.join-card {
  text-align: center;
}
.join-title { font-size: 32px; font-weight: 400; margin: 0 0 8px; color: #e8eaed; }
.join-code { font-size: 16px; color: #9aa0a6; margin: 0 0 32px; letter-spacing: 0.5px; }

.join-actions-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}
.join-actions { display: flex; gap: 16px; justify-content: center; }
.btn-join {
  background: #8ab4f8; color: #202124; border: none;
  padding: 12px 24px; border-radius: 24px;
  font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s;
}
.btn-join:hover { background: #9bbcf8; }
.btn-present {
  background: transparent; color: #8ab4f8; border: 1px solid #5f6368;
  padding: 12px 24px; border-radius: 24px;
  font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;
}
.btn-present:hover { border-color: #8ab4f8; background: rgba(138, 180, 248, 0.04); }

.btn-cancel {
  background: transparent;
  border: none;
  color: #9aa0a6;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  padding: 8px 16px;
  transition: color 0.2s;
}
.btn-cancel:hover {
  color: #e8eaed;
}

/* EFFECTS PANEL */
.effects-panel {
  background: #2d2e30;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 500px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}
.panel-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px; border-bottom: 1px solid #3c4043;
}
.panel-header h3 { margin: 0; font-size: 16px; font-weight: 500; color: #e8eaed; }
.panel-close { background: transparent; border: none; color: #9aa0a6; cursor: pointer; padding: 4px; }
.panel-close:hover { color: white; }

.effects-tabs { display: flex; border-bottom: 1px solid #3c4043; padding: 0 16px; }
.effects-tabs button {
  flex: 1; background: none; border: none; color: #9aa0a6;
  padding: 12px 0; font-size: 13px; font-weight: 500; cursor: pointer;
  border-bottom: 3px solid transparent; transition: all 0.2s;
}
.effects-tabs button:hover { color: #e8eaed; }
.effects-tabs button.active { color: #8ab4f8; border-bottom-color: #8ab4f8; }

.effects-scroll-area { flex: 1; overflow-y: auto; padding: 16px; scrollbar-width: thin; scrollbar-color: #3c4043 transparent; }
.tab-pane { display: flex; flex-direction: column; gap: 24px; }

.effects-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.effect-btn {
  position: relative; aspect-ratio: 1; border-radius: 8px;
  border: 2px solid transparent; background: #3c4043; cursor: pointer;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  overflow: hidden; padding: 0; transition: transform 0.1s, border-color 0.2s;
}
.effect-btn:hover { transform: scale(1.05); }
.effect-btn.active { border-color: #8ab4f8; }
.effect-btn img { width: 100%; height: 100%; object-fit: cover; }
.eff-label { font-size: 10px; color: #9aa0a6; margin-top: 4px; text-align: center; }

.cat-title { font-size: 13px; color: #e8eaed; font-weight: 500; margin: 0 0 12px 0; }

.tab-desc { font-size: 13px; color: #9aa0a6; text-align: center; margin: 0; }
.filter-placeholder { aspect-ratio: 1; background: #3c4043; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; }
.appearance-card { background: rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; position: relative; }
.appearance-card h4 { margin: 0 0 4px 0; color: #e8eaed; font-size: 14px; font-weight: 500; }
.appearance-card p { margin: 0; color: #9aa0a6; font-size: 12px; max-width: 80%; }
.appearance-card .switch { position: absolute; top: 16px; right: 16px; }

/* TOGGLE SWITCH CSS */
.switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #5f6368; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
input:checked + .slider { background-color: #8ab4f8; }
input:checked + .slider:before { transform: translateX(18px); background-color: #202124; }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }

/* AI FIX */
.hidden-ai-element {
  position: absolute !important; width: 1px !important; height: 1px !important;
  opacity: 0 !important; pointer-events: none !important; z-index: -100 !important;
}

@media (max-width: 768px) {
  .main-content { padding: 24px; gap: 24px; flex-direction: column; }
  .video-section { max-width: 100%; }
  .right-section { flex: auto; width: 100%; }
  .effects-panel { height: 400px; }
}
</style>