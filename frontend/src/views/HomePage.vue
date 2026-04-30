<template>
  <div class="home-wrapper">
    <header class="header">
      <div class="header-logo">
        <svg viewBox="0 0 32 32" data-name="Layer 1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" width="36" height="36">
          <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
          <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
          <g id="SVGRepo_iconCarrier">
            <path d="M24,21.45V25a2.0059,2.0059,0,0,1-2,2H9V21h9V16Z" fill="#00ac47"></path>
            <polygon fill="#31a950" points="24 11 24 21.45 18 16 18 11 24 11"></polygon>
            <polygon fill="#ea4435" points="9 5 9 11 3 11 9 5"></polygon>
            <rect fill="#4285f4" height="11" width="6" x="3" y="11"></rect>
            <path d="M24,7v4h-.5L18,16V11H9V5H22A2.0059,2.0059,0,0,1,24,7Z" fill="#ffba00"></path>
            <path d="M9,21v6H5a2.0059,2.0059,0,0,1-2-2V21Z" fill="#0066da"></path>
            <path d="M29,8.26V23.74a.9989.9989,0,0,1-1.67.74L24,21.45,18,16l5.5-5,.5-.45,3.33-3.03A.9989.9989,0,0,1,29,8.26Z" fill="#00ac47"></path>
            <polygon fill="#188038" points="24 10.55 24 21.45 18 16 23.5 11 24 10.55"></polygon>
          </g>
        </svg>
        <span class="logo-text">Meet</span>
      </div>
      <div class="header-right">
        <span class="date-time">{{ currentTime }}</span>
        <button class="header-icon-btn" title="Settings" @click="openSettings">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="#5f6368">
            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.06-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.06,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.49-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
          </svg>
        </button>
      </div>
    </header>

    <main class="hero">
      <div class="hero-content">
        <h1 class="hero-title">Secure video conferencing<br />for everyone</h1>
        <p class="hero-subtitle">
          Connect, collaborate, and celebrate from anywhere with Max Meet
        </p>

        <div class="action-row">
          <button class="btn-new-meeting" @click="createMeeting" :disabled="creating">
            <svg v-if="!creating" viewBox="0 0 24 24" width="20" height="20" fill="white">
              <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
            </svg>
            <div v-else class="spinner"></div>
            {{ creating ? 'Creating…' : 'New meeting' }}
          </button>

          <div class="join-row">
            <div class="input-wrapper">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="#5f6368">
                <path d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13z"/>
              </svg>
              <input v-model="joinCode" type="text" placeholder="Enter a code" @keyup.enter="joinMeeting" />
            </div>
            <button class="btn-join" :disabled="!joinCode.trim()" @click="joinMeeting">Join</button>
          </div>
        </div>

        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

        <div class="divider"></div>

        <p class="learn-more">
          <a href="#">Learn more</a> about Max Meet
        </p>
      </div>

      <div class="feature-card">
        <div class="feature-illustration">
          <svg viewBox="0 0 120 120" width="180" height="180">
            <circle cx="60" cy="60" r="60" fill="#e8f0fe"/>
            <path d="M60 20 L32 32 v26c0 24 14 47 28 55 c14-8 28-31 28-55 V32 L60 20z" fill="#1a73e8"/>
            <path d="M60 48 c-4.4 0-8 3.6-8 8 v4 h-2 v12 h20 v-12 h-2 v-4 c0-4.4-3.6-8-8-8zm0 4 c2.2 0 4 1.8 4 4 v4 h-8 v-4 c0-2.2 1.8-4 4-4z" fill="#fff"/>
          </svg>
        </div>
        <h3 class="feature-title" style="margin-top: 10px;">Your meeting is safe</h3>
        <p class="feature-desc" style="font-size: 15px;">
          No one can join a meeting unless invited or admitted by<br/>the host
        </p>
      </div>
    </main>

    <Transition name="fade">
      <div v-if="showSettings" class="settings-overlay" @click="closeSettings">
        <div class="settings-modal" @click.stop>
          <div class="settings-sidebar">
            <h2 class="settings-title">Settings</h2>
            <button class="settings-tab" :class="{ active: settingsTab === 'audio' }" @click="settingsTab = 'audio'">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
              Audio
            </button>
            <button class="settings-tab" :class="{ active: settingsTab === 'video' }" @click="settingsTab = 'video'">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
              Video
            </button>
          </div>
          
          <div class="settings-content">
            <button class="close-btn" @click="closeSettings">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="#5f6368"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>

            <div v-if="settingsTab === 'audio'" class="settings-pane">
              <div class="setting-group">
                <label>Microphone</label>
                <div class="select-wrapper">
                  <svg class="select-icon" viewBox="0 0 24 24" width="20" height="20" fill="#5f6368"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/></svg>
                  <select>
                    <option v-for="mic in microphones" :key="mic.deviceId">{{ mic.label || 'Default Microphone' }}</option>
                  </select>
                </div>
              </div>
              <div class="setting-group">
                <label>Speaker</label>
                <div class="select-wrapper">
                  <svg class="select-icon" viewBox="0 0 24 24" width="20" height="20" fill="#5f6368"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                  <select>
                    <option v-for="spk in speakers" :key="spk.deviceId">{{ spk.label || 'Default Speaker' }}</option>
                  </select>
                </div>
              </div>
            </div>

            <div v-if="settingsTab === 'video'" class="settings-pane video-pane">
              <div class="setting-group">
                <label>Camera</label>
                <div class="select-wrapper">
                  <svg class="select-icon" viewBox="0 0 24 24" width="20" height="20" fill="#5f6368"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                  <select>
                    <option v-for="cam in cameras" :key="cam.deviceId">{{ cam.label || 'Default Camera' }}</option>
                  </select>
                </div>
              </div>
              <div class="video-preview-block">
                <video ref="settingsVidPreview" autoplay muted playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1); background: #000;"></video>
              </div>
            </div>

          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useLogger } from '../composables/useLogger'

const API_URL = import.meta.env.VITE_API_URL
const router = useRouter()
const { logAction } = useLogger()

const joinCode = ref('')
const creating = ref(false)
const errorMsg = ref('')
const currentTime = ref('')

// Settings State
const showSettings = ref(false)
const settingsTab = ref('audio')
const microphones = ref([])
const speakers = ref([])
const cameras = ref([])
const settingsVidPreview = ref(null) // Reference para sa video tag
let settingsStream = null // Pansamantalang stream para sa settings

let timer

function updateTime() {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) +
    ' • ' + now.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' })
}

onMounted(() => {
  updateTime()
  timer = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  clearInterval(timer)
  closeSettings() // Siguraduhing patay ang camera pag umalis
})

async function createMeeting() {
  creating.value = true
  errorMsg.value = ''
  try {
    const { data } = await axios.post(`${API_URL}/api/meetings/create`)
    const code = data.meeting_code

    sessionStorage.setItem('isHost', 'true')
    sessionStorage.setItem('meetingCode', code)

    logAction('meeting_created', { meeting_code: code }).catch(() => {})
    
    router.push(`/meeting/${code}/prejoin`)
  } catch {
    errorMsg.value = 'Failed to create meeting. Is the backend running?'
    creating.value = false 
  }
}

async function joinMeeting() {
  const code = joinCode.value.trim()
  if (!code) return
  errorMsg.value = ''
  try {
    const { data } = await axios.post(`${API_URL}/api/meetings/verify`, { meeting_code: code })
    if (data.valid) {
      sessionStorage.setItem('isHost', 'false')
      sessionStorage.setItem('meetingCode', code)
      await logAction('user_typed_code', { meeting_code: code })
      router.push(`/meeting/${code}/prejoin`)
    }
  } catch {
    errorMsg.value = 'Meeting not found or has already ended.'
  }
}

async function openSettings() {
  showSettings.value = true;
  try {
    // Buhayin ang camera para makuha ang stream at mga pangalan ng device
    settingsStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true }); 
    const devices = await navigator.mediaDevices.enumerateDevices();
    microphones.value = devices.filter(d => d.kind === 'audioinput');
    speakers.value = devices.filter(d => d.kind === 'audiooutput');
    cameras.value = devices.filter(d => d.kind === 'videoinput');

    // Kung nakabukas agad sa video tab, i-inject agad ang stream
    if (settingsTab.value === 'video') {
      import('vue').then(({ nextTick }) => {
        nextTick(() => {
          if (settingsVidPreview.value) settingsVidPreview.value.srcObject = settingsStream;
        });
      });
    }
  } catch (err) {
    console.warn("Could not fetch devices", err);
  }
}

function closeSettings() {
  showSettings.value = false;
  if (settingsStream) {
    settingsStream.getTracks().forEach(track => track.stop());
    settingsStream = null;
  }
}

import { watch, nextTick } from 'vue'
watch(settingsTab, async (newVal) => {
  if (newVal === 'video' && settingsStream) {
    await nextTick(); // Hintayin mag-render yung <video> element
    if (settingsVidPreview.value) {
      settingsVidPreview.value.srcObject = settingsStream;
    }
  }
})
</script>

<style scoped>
.home-wrapper { min-height: 100vh; background: #fff; display: flex; flex-direction: column; }
.spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Header */
.header { display: flex; align-items: center; justify-content: space-between; padding: 12px 24px; border-bottom: 1px solid #e0e0e0; }
.header-logo { display: flex; align-items: center; gap: 8px; }
.logo-text { font-size: 22px; color: #5f6368; font-weight: 400; }
.header-right { font-size: 14px; color: #5f6368; display: flex; align-items: center; gap: 16px; }
.header-icon-btn { background: transparent; border: none; cursor: pointer; border-radius: 50%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.header-icon-btn:hover { background: #f1f3f4; }

/* Hero */
.hero { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px 40px; gap: 48px; }
.hero-content { text-align: center; max-width: 600px; }
.hero-title { font-size: 44px; font-weight: 400; color: #202124; line-height: 1.2; margin-bottom: 16px; }
.hero-subtitle { font-size: 16px; color: #5f6368; margin-bottom: 36px; }

/* Action row */
.action-row { display: flex; align-items: center; gap: 16px; justify-content: center; flex-wrap: wrap; }
.btn-new-meeting { display: flex; align-items: center; gap: 10px; background: #1a73e8; color: #fff; border: none; border-radius: 4px; padding: 0 24px; height: 48px; font-size: 15px; font-weight: 500; cursor: pointer; transition: background 0.2s; white-space: nowrap; }
.btn-new-meeting:hover:not(:disabled) { background: #1558b0; }
.btn-new-meeting:disabled { opacity: 0.6; cursor: not-allowed; }
.join-row { display: flex; align-items: center; gap: 0; border: 1px solid #dadce0; border-radius: 4px; overflow: hidden; }
.input-wrapper { display: flex; align-items: center; padding: 0 14px; gap: 10px; }
.input-wrapper input { border: none; outline: none; font-size: 15px; color: #202124; width: 220px; height: 46px; background: transparent; }
.input-wrapper input::placeholder { color: #80868b; }
.btn-join { background: transparent; border: none; border-left: 1px solid #dadce0; color: #1a73e8; font-size: 15px; font-weight: 500; padding: 0 20px; height: 48px; cursor: pointer; transition: background 0.2s; }
.btn-join:hover:not(:disabled) { background: #e8f0fe; }
.btn-join:disabled { color: #80868b; cursor: not-allowed; }
.error-msg { color: #d93025; font-size: 14px; margin-top: 12px; }
.divider { width: 100%; height: 1px; background: #e0e0e0; margin: 28px 0 20px; }
.learn-more { font-size: 14px; color: #5f6368; }
.learn-more a { color: #1a73e8; text-decoration: none; }
.learn-more a:hover { text-decoration: underline; }

/* Feature card */
.feature-card { text-align: center; }
.feature-title { font-size: 20px; font-weight: 400; color: #202124; margin-bottom: 10px; }
.feature-desc { font-size: 14px; color: #5f6368; line-height: 1.6; }

/* Settings Modal UI */
.settings-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.settings-modal { width: 800px; height: 500px; background: #fff; border-radius: 8px; display: flex; box-shadow: 0 12px 36px rgba(0,0,0,0.2); overflow: hidden; font-family: 'Google Sans', sans-serif;}
.settings-sidebar { width: 240px; background: #f8f9fa; padding: 20px 0; border-right: 1px solid #e0e0e0; display: flex; flex-direction: column; }
.settings-title { font-size: 22px; font-weight: 400; color: #202124; padding: 0 24px; margin-bottom: 16px; }
.settings-tab { display: flex; align-items: center; gap: 16px; padding: 12px 24px; font-size: 14px; font-weight: 500; color: #5f6368; border: none; background: none; cursor: pointer; text-align: left; }
.settings-tab:hover { background: #f1f3f4; }
.settings-tab.active { background: #e8f0fe; color: #1a73e8; border-top-right-radius: 24px; border-bottom-right-radius: 24px; margin-right: 12px; }
.settings-content { flex: 1; padding: 40px; position: relative; }
.close-btn { position: absolute; top: 16px; right: 16px; background: none; border: none; cursor: pointer; padding: 8px; border-radius: 50%; display: flex; }
.close-btn:hover { background: #f1f3f4; }

.settings-pane { display: flex; flex-direction: column; gap: 32px; }
.video-pane { flex-direction: row; gap: 40px; }
.setting-group { flex: 1; display: flex; flex-direction: column; gap: 8px; }
.setting-group label { font-size: 14px; font-weight: 500; color: #1a73e8; }
.select-wrapper { position: relative; display: flex; align-items: center; }
.select-icon { position: absolute; left: 12px; pointer-events: none; }
.select-wrapper select { width: 100%; height: 48px; border: 1px solid #dadce0; border-radius: 4px; padding: 0 16px 0 44px; font-size: 14px; color: #202124; outline: none; appearance: none; background: transparent; cursor: pointer; }
.select-wrapper select:focus { border: 2px solid #1a73e8; }
.select-wrapper::after { content: "▼"; position: absolute; right: 16px; font-size: 10px; color: #5f6368; pointer-events: none; }

.video-preview-block { width: 220px; height: 124px; background: #f1f3f4; border-radius: 8px; border: 1px solid #dadce0; display: flex; align-items: center; justify-content: center; overflow: hidden; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>