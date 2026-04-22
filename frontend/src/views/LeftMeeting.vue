<template>
  <div class="left-wrapper">
    
    <div class="countdown-header">
      <div class="circular-chart-wrap">
        <svg viewBox="0 0 36 36" class="circular-svg">
          <path class="circle-bg"
            d="M18 2.0845
              a 15.9155 15.9155 0 0 1 0 31.831
              a 15.9155 15.9155 0 0 1 0 -31.831"
          />
          <path class="circle-progress"
            :stroke-dasharray="countdownStroke"
            d="M18 2.0845
              a 15.9155 15.9155 0 0 1 0 31.831
              a 15.9155 15.9155 0 0 1 0 -31.831"
          />
        </svg>
        <div class="countdown-number">{{ countdown }}</div>
      </div>
      <span class="returning-text">Returning to home screen</span>
    </div>

    <main class="left-main">
      <h1 class="title">You left the meeting</h1>

      <div class="action-row">
        <button class="btn-rejoin" @click="rejoin">Rejoin</button>
        <button class="btn-home" @click="goHome">Return to home screen</button>
      </div>

      <a href="#" class="feedback-link">Submit feedback</a>

      <div class="safety-card">
        <div class="safety-icon">
          <svg viewBox="0 0 64 80" width="48" height="60" fill="none">
            <path d="M32 4L6 16v22c0 15.5 11 30 26 34 15-4 26-18.5 26-34V16L32 4z" fill="#1a73e8"/>
            <rect x="24" y="28" width="16" height="14" rx="2" fill="white"/>
            <circle cx="32" cy="24" r="6" stroke="white" stroke-width="3" fill="none"/>
            <circle cx="32" cy="35" r="2" fill="#1a73e8"/>
          </svg>
        </div>
        <div class="safety-content">
          <h3 class="safety-title">Your meeting is safe</h3>
          <p class="safety-desc">No one can join a meeting unless invited or admitted by the host</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route  = useRoute()
const router = useRouter()

// Countdown Logic
const countdown = ref(60)
let countdownInterval

// Computes the stroke dash array for the SVG circle (from 100 to 0)
const countdownStroke = computed(() => {
  return `${(countdown.value / 60) * 100}, 100`
})

onMounted(() => {
  countdownInterval = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) {
      clearInterval(countdownInterval)
      goHome() // Force redirect pagka 0
    }
  }, 1000)
})

onUnmounted(() => {
  clearInterval(countdownInterval)
})

function rejoin() {
  sessionStorage.setItem('isHost', sessionStorage.getItem('wasHost') ?? 'false')
  router.push(`/meeting/${route.params.code}/prejoin`)
}

function goHome() {
  router.push('/')
}
</script>

<style scoped>
.left-wrapper {
  min-height: 100vh;
  background: #fff;
  display: flex;
  flex-direction: column;
  font-family: 'Google Sans', Roboto, Arial, sans-serif;
  position: relative;
}

/* Timer Header Styles */
.countdown-header {
  position: absolute;
  top: 24px;
  left: 24px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.circular-chart-wrap {
  position: relative;
  width: 44px;
  height: 44px;
}
.circular-svg {
  display: block;
  width: 100%;
  height: 100%;
}
.circle-bg {
  fill: none;
  stroke: #e6e6e6;
  stroke-width: 2.5;
}
.circle-progress {
  fill: none;
  stroke: #1a73e8; /* Google Blue */
  stroke-width: 2.5;
  stroke-linecap: round;
  transition: stroke-dasharray 1s linear;
}
.countdown-number {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 13px;
  font-weight: 500;
  color: #3c4043;
}
.returning-text {
  font-size: 14px;
  color: #3c4043;
  font-weight: 400;
}

/* Main Content Styles */
.left-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 24px;
  padding: 40px 24px;
}

.title {
  font-size: 40px;
  font-weight: 400;
  color: #202124;
  margin: 0;
  text-align: center;
}

.action-row {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
  justify-content: center;
}

.btn-rejoin {
  background: #fff;
  color: #1a73e8;
  border: 1px solid #dadce0;
  border-radius: 24px;
  padding: 10px 24px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background .2s, border-color .2s;
}
.btn-rejoin:hover { background: #f8f9fa; }

.btn-home {
  background: #1a73e8;
  color: #fff;
  border: none;
  border-radius: 24px;
  padding: 10px 24px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background .2s;
}
.btn-home:hover { background: #1558b0; }

.feedback-link {
  color: #1a73e8;
  font-size: 14px;
  text-decoration: none;
}
.feedback-link:hover { text-decoration: underline; }

.safety-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px 24px;
  max-width: 440px;
  width: 100%;
  position: relative;
  margin-top: 16px;
}

.safety-icon { flex-shrink: 0; }

.safety-content { flex: 1; }
.safety-title {
  font-size: 16px;
  font-weight: 500;
  color: #202124;
  margin: 0 0 6px;
}
.safety-desc {
  font-size: 13px;
  color: #5f6368;
  margin: 0;
  line-height: 1.5;
}
</style>