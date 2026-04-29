<template>
  <div class="room" @click="closeAll">

  <video ref="rawVideoEl" autoplay playsinline muted class="hidden-ai-element"></video>
  <canvas ref="blurCanvasEl" width="640" height="480" class="hidden-ai-element"></canvas>

    <div class="top-bar">
      <Transition name="pop">
        <div v-if="handRaised" class="hand-top-badge">
          <span>✋</span>
          <span class="hand-badge-name">{{ isHost ? 'Host (You)' : 'Guest (You)' }}</span>
        </div>
      </Transition>
    </div>

    <div class="content-area" :class="{ 'with-emoji': showEmojiPicker, 'with-panel': activePanel }">
      <div class="video-wrap" :class="remoteClass">
        <video ref="remoteVideoEl" autoplay playsinline class="video-fill" :class="{ 'vid-hidden': !remoteCameraOn }"></video>
        
        <div v-if="!remoteCameraOn" class="tile-avatar">
          <div class="tile-avatar-circle" :style="{ backgroundColor: remoteAvatarColor }">
            <svg viewBox="0 0 24 24" width="50%" height="50%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
          </div>
        </div>

        <div class="tile-top-right">
          <div v-if="remoteConnected && !remoteMicOn" class="tile-icon-btn mute-badge">
             <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
          </div>
        </div>
        <div class="tile-bottom-left">
          <div v-if="remoteHandRaised" class="hand-indicator">✋</div>
          <div v-if="remoteConnected" class="tile-name">{{ isHost ? 'Guest' : 'Host' }}</div>
        </div>
      </div>

      <div class="video-wrap" :class="screenClass">
        <video ref="screenVideoEl" autoplay playsinline class="video-fill"></video>
        <div class="screen-presenter-bar">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>
          You are presenting
        </div>
      </div>

      <div 
        ref="pipElRef"
        class="video-wrap" 
        :class="localClass"
        @touchstart="onDragStart"
        @touchmove="onDragMove"
        @touchend="onDragEnd"
      >
        <video ref="localVideoEl" autoplay muted playsinline class="video-fill" :class="{ mirrored: !screenSharing, 'vid-hidden': !cameraOn && !screenSharing }"></video>
        <div v-if="!cameraOn && !screenSharing" class="tile-avatar">
          <div class="tile-avatar-circle">
            <svg viewBox="0 0 24 24" width="50%" height="50%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
          </div>
        </div>
        <div class="tile-top-right">
          <div v-if="!micOn" class="tile-icon-btn mute-badge urgent">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
          </div>
        </div>
        <div class="tile-bottom-left">
          <div v-if="handRaised" class="hand-indicator">✋</div>
          <div class="tile-name">{{ isHost ? 'Host (You)' : 'Guest (You)' }}</div>
        </div>
      </div>
    </div>

    <TransitionGroup name="float-emoji">
      <div v-for="e in floatingEmojis" :key="e.id" class="emoji-float" :style="{ left: e.x + '%' }">
        <span class="emoji-char">{{ e.emoji }}</span><span class="emoji-you-pill">{{ e.senderName }}</span>
      </div>
    </TransitionGroup>

    <Transition name="fade"><div v-if="captionsOn && captionText" class="caption-bar">{{ captionText }}</div></Transition>
    <Transition name="pop"><div v-if="handRaised" class="hand-bottom-pill"><span>✋</span><span>{{ isHost ? 'Host (You)' : 'Guest (You)' }}</span></div></Transition>

    <Transition name="pop-center">
      <div v-if="showEmojiPicker" class="emoji-picker" @click.stop>
        <button v-for="emoji in EMOJIS" :key="emoji" class="emoji-btn" @click="sendReaction(emoji)">{{ emoji }}</button>
      </div>
    </Transition>

    <Transition name="slide-up">
      <div v-if="showReadyDialog" class="ready-dialog">
        <button class="dialog-close" @click="showReadyDialog = false"><svg viewBox="0 0 24 24" width="18" height="18" fill="#5f6368"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
        <h3 class="dialog-title">Your meeting's ready</h3>
        <p class="dialog-desc">Share this link with others you want in the meeting</p>
        <div class="link-row">
          <span class="link-text">{{ meetingLink }}</span>
          <button class="btn-copy" @click="copyLink" :title="copied ? 'Copied!' : 'Copy link'">
            <svg v-if="!copied" viewBox="0 0 24 24" width="18" height="18" fill="#5f6368"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
            <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="#1a73e8"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
          </button>
        </div>
      </div>
    </Transition>

    <Transition name="slide-up">
      <div v-if="showAdmitModal && isHost" class="admit-dialog">
        <h3 class="dialog-title">Someone wants to join</h3>
        <p class="dialog-desc">A guest is asking to join this meeting.</p>
        <div class="admit-actions">
          <button class="btn-deny" @click="denyGuest">Deny entry</button>
          <button class="btn-admit" @click="admitGuest">Admit</button>
        </div>
      </div>
    </Transition>

    <Transition name="slide-panel">
      <div v-if="activePanel" class="side-panel" @click.stop>
        <template v-if="activePanel === 'chat'">
          <div class="panel-header">
            <h3>In-call messages</h3>
            <button class="panel-close" @click="activePanel = null"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
          </div>
          <div class="messages-list" ref="messagesListEl">
            <div v-if="!messages.length" class="no-messages">
              <img src="/empty-chat.png" alt="No messages" class="empty-chat-img" />
              <p>No chat messages yet</p>
            </div>
            <div v-for="msg in messages" :key="msg.id" class="message-item" :class="{ own: msg.isOwn }">
              <div class="msg-meta"><span class="msg-sender">{{ msg.sender }}</span><span class="msg-time">{{ msg.time }}</span></div>
              <p class="msg-text">{{ msg.text }}</p>
            </div>
          </div>
          <div class="chat-input-row">
            <input v-model="messageInput" class="chat-input" placeholder="Send a message" @keyup.enter="sendMessage" />
            <button class="chat-send" @click="sendMessage" :disabled="!messageInput.trim()"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>
          </div>
        </template>
        <template v-if="activePanel === 'people'">
          <div class="panel-header">
            <h3>People</h3>
            <button class="panel-close" @click="activePanel = null"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
          </div>
          
          <div v-if="isHost" class="host-controls">
            <div class="host-controls-header">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="#1a73e8"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
              <span>Host controls</span>
            </div>
            <div class="host-control-item">
              <div class="control-text">
                <strong>Lock Meeting</strong>
                <small>Prevent new guests from joining</small>
              </div>
              <label class="switch">
                <input type="checkbox" v-model="isLocked">
                <span class="slider round"></span>
              </label>
            </div>
          </div>

          <p class="people-count-text">In call ({{ remoteConnected ? 2 : 1 }})</p>
          
          <div class="participant-list">
            <div class="participant-item">
              <div class="participant-avatar">
                 <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
              </div>
              <div class="participant-name">You ({{ isHost ? 'Host' : 'Guest' }})</div>
              <div class="participant-status">
                <svg v-if="!micOn" viewBox="0 0 24 24" width="16" height="16" fill="#ea4335"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
              </div>
            </div>

            <div v-if="remoteConnected" class="participant-item">
              <div class="participant-avatar" :style="{ backgroundColor: remoteAvatarColor }">
                 <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
              </div>
              <div class="participant-name">{{ isHost ? 'Guest' : 'Host' }}</div>
              
              <div v-if="isHost" class="host-actions">
                <button class="action-btn" @click="forceMuteGuest" :disabled="!remoteMicOn" title="Mute participant">
                  <svg viewBox="0 0 24 24" width="18" height="18" :fill="remoteMicOn ? '#e8eaed' : '#5f6368'"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                </button>
                <button class="action-btn remove-btn" @click="kickGuest" title="Remove from call">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="#ea4335"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                </button>
              </div>
              
              <div v-else class="participant-status">
                <svg v-if="!remoteMicOn" viewBox="0 0 24 24" width="16" height="16" fill="#ea4335"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
              </div>
            </div>
          </div>
        </template>
      </div>
    </Transition>

    <div v-if="peerError" class="error-banner">{{ peerError }}</div>

    <Transition name="fade">
      <div v-if="!isHost && !remoteConnected" class="waiting-overlay">
        <div class="waiting-card">
          <template v-if="!rejected">
            <div class="spinner-large"></div>
            <h2 class="waiting-title">Asking to join...</h2>
            <p class="waiting-desc">You'll join the call when the host lets you in.</p>
            <button class="btn-cancel-wait" @click="stopAllMedia(); router.push('/')">Cancel</button>
          </template>
          
          <template v-else>
            <div class="rejected-icon">
               <svg viewBox="0 0 24 24" width="48" height="48" fill="#ea4335"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            </div>
            <h2 class="waiting-title">You can't join this call</h2>
            <p class="waiting-desc">The host has locked the meeting and is not accepting new guests.</p>
            <button class="btn-return" @click="stopAllMedia(); router.push('/')">Return to home screen</button>
          </template>
        </div>
      </div>
    </Transition>

    <div class="bottom-bar">
      <div class="bottom-left">
        <span v-if="isRecording" class="recording-indicator">
          <span class="red-dot"></span> Recording
        </span>
        <span class="call-time">{{ callTime }}</span><span class="sep">|</span><span class="call-code">{{ route.params.code }}</span>
      </div>

      <div class="controls-center" @click.stop>
        <button class="ctrl-btn" :class="{ off: !micOn }" @click="toggleMic" :title="micOn ? 'Turn off microphone' : 'Turn on microphone'">
          <svg v-if="micOn" viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
          <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17c0-.06.02-.11.02-.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
        </button>
        <button class="ctrl-btn" :class="{ off: !cameraOn }" @click="toggleCamera" :title="cameraOn ? 'Turn off camera' : 'Turn on camera'">
          <svg v-if="cameraOn" viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
          <svg v-else viewBox="0 0 24 24" width="20" height="20"><path fill="white" d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/><line x1="2" y1="2" x2="22" y2="22" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>
        </button>
        <button class="ctrl-btn" :class="{ active: screenSharing }" @click="toggleScreenShare" title="Present now">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6zm8 9l-4-4h3V8h2v3h3l-4 4z"/></svg>
        </button>

        <button class="ctrl-btn" :class="{ active: isBlurOn }" @click="toggleBlur" :disabled="isBlurLoading || !cameraOn" title="Blur background">
          <svg v-if="isBlurLoading" viewBox="0 0 24 24" width="20" height="20" class="spinner"><circle cx="12" cy="12" r="10" fill="none" stroke="white" stroke-width="3" stroke-dasharray="30" stroke-linecap="round"/></svg>
          <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 16a7 7 0 110-14 7 7 0 010 14zm-1-7h2v2h-2zm0-4h2v2h-2zm-4 4h2v2H7zm0-4h2v2H7zm8 4h2v2h-2zm0-4h2v2h-2z"/></svg>
        </button>

        <button class="ctrl-btn" :class="{ active: showEmojiPicker }" @click.stop="toggleEmojiPicker" title="Send a reaction">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
        </button>
        <button v-if="isHost" class="ctrl-btn" :class="{ off: isRecording, active: isUploading }" @click="toggleRecording" :disabled="isUploading" :title="isRecording ? 'Stop recording' : 'Start recording'">
          <svg v-if="isRecording" viewBox="0 0 24 24" width="20" height="20" fill="white"><rect x="7" y="7" width="10" height="10"/></svg>
          <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="white"><circle cx="12" cy="12" r="8"/></svg>
        </button>
        <div class="more-btn-wrap">
          <button class="ctrl-btn" :class="{ active: showMoreDropdown }" @click.stop="toggleMoreDropdown">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
          </button>
          <Transition name="pop-center">
            <div v-if="showMoreDropdown" class="more-dropdown" @click.stop>
              <button class="dropdown-item mobile-only-menu" @click="togglePanel('people'); showMoreDropdown = false">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                People
              </button>
              <button class="dropdown-item mobile-only-menu" @click="togglePanel('chat'); showMoreDropdown = false">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                In-call messages
                <span v-if="unreadCount > 0" class="util-badge-menu">{{ unreadCount }}</span>
              </button>
              <button class="dropdown-item" @click="openSettings">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                  <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.06-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.06,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.43-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.49-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                </svg>
                Settings
              </button>
              <button class="dropdown-item" @click="openDocumentPiP">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 7h-8v6h8V7zm2-4H3c-1.1 0-2 .9-2 2v14c0 1.1.9 1.99 2 1.99h18c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16.01H3V4.98h18v14.03z"/></svg>
                Picture-in-picture
              </button>
              <button class="dropdown-item" @click="toggleFullscreen">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                  <path v-if="!isFullscreen" d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                  <path v-else d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>
                </svg>
                {{ isFullscreen ? 'Exit full screen' : 'Full screen' }}
              </button>
              
            </div>
          </Transition>
        </div>
        <button class="ctrl-btn end-call" @click="leaveCall" title="Leave call">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="white"><path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/></svg>
        </button>
      </div>

      <div class="bottom-right" @click.stop>
        <button class="util-btn" :class="{ active: activePanel === 'people' }" @click.stop="togglePanel('people')" title="People">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </button>
        <button class="util-btn" :class="{ active: activePanel === 'chat' }" @click.stop="togglePanel('chat')" title="In-call messages">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
          <span v-if="unreadCount > 0" class="util-badge">{{ unreadCount }}</span>
        </button>
      </div>
    </div>

    <Transition name="fade">
      <div v-if="showSettings" class="settings-overlay" @click="showSettings = false">
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
            <button class="close-btn" @click="showSettings = false">
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
                <video ref="settingsVidPreview" autoplay muted playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1);"></video>
              </div>
            </div>

          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch, reactive, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Peer from 'peerjs'
import axios from 'axios'
import { useLogger } from '../composables/useLogger'
// IMPORANTE: Import the fix-webm-duration library
import fixWebmDuration from 'fix-webm-duration'

const SelfieSegmentation = window.SelfieSegmentation;
const SVG_MIC_ON = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>`;
const SVG_MIC_OFF = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17c0-.06.02-.11.02-.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>`;
const SVG_CAM_ON = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>`;
const SVG_CAM_OFF = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/><line x1="2" y1="2" x2="22" y2="22" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>`;
const SVG_SHARE = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6zm8 9l-4-4h3V8h2v3h3l-4 4z"/></svg>`;
const SVG_MORE = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>`;
const SVG_END = `<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/></svg>`;
const SVG_AVATAR = `<svg viewBox="0 0 24 24" width="60%" height="60%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>`;
const SVG_MUTE_BADGE = `<svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>`;
const SVG_MSG = `<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>`;
const SVG_HAND = `<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.5 3C13.2239 3 13 3.22386 13 3.5V12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12V5.5C11 5.22386 10.7761 5 10.5 5C10.2239 5 9.99999 5.22386 9.99999 5.5V13.9677C9.99999 15.0757 8.62655 15.5918 7.8969 14.7579L5.34951 11.8466C5.19167 11.6662 4.95459 11.576 4.71675 11.6057C4.15329 11.6761 3.88804 12.3395 4.24762 12.779L8.93807 18.5118C10.2266 20.0867 12.154 21 14.1888 21C17.3982 21 20 18.3982 20 15.1888V7.5C20 7.22386 19.7761 7 19.5 7C19.2239 7 19 7.22386 19 7.5V12C19 12.5523 18.5523 13 18 13C17.4477 13 17 12.5523 17 12V5.5C17 5.22386 16.7761 5 16.5 5C16.2239 5 16 5.22386 16 5.5V12C16 12.5523 15.5523 13 15 13C14.4477 13 14 12.5523 14 12V3.5C14 3.22386 13.7761 3 13.5 3ZM15.9611 3.05823C15.7525 1.88823 14.73 1 13.5 1C12.27 1 11.2475 1.88823 11.0389 3.05823C10.8653 3.0201 10.685 3 10.5 3C9.11928 3 7.99999 4.11929 7.99999 5.5V11.8386L6.85467 10.5296C6.2595 9.84942 5.36551 9.50903 4.46868 9.62113C2.34401 9.88672 1.34381 12.3883 2.6997 14.0455L7.39016 19.7783C9.05854 21.8174 11.5541 23 14.1888 23C18.5028 23 22 19.5028 22 15.1888V7.5C22 6.11929 20.8807 5 19.5 5C19.315 5 19.1347 5.0201 18.9611 5.05823C18.7525 3.88823 17.73 3 16.5 3C16.315 3 16.1347 3.0201 15.9611 3.05823Z"/></svg>`;

const API_URL = import.meta.env.VITE_API_URL
const route  = useRoute()
const router = useRouter()
const { logAction } = useLogger()

const EMOJIS = ['❤️', '👍', '🎉', '👏', '😂', '😮', '😢', '🤔', '👎', '⭐']

const localVideoEl   = ref(null)
const remoteVideoEl  = ref(null)
const screenVideoEl  = ref(null)
const messagesListEl = ref(null)

const rawVideoEl     = ref(null)
const blurCanvasEl   = ref(null)
const isBlurOn       = ref(false)
const isBlurLoading  = ref(false)
let selfieSegmentation = null
let blurRafId          = null
let processedStream    = null

const peer        = ref(null)
const currentCall = ref(null)
const dataConn    = ref(null)

const micOn         = ref(true)
const cameraOn      = ref(true)
const screenSharing = ref(false)
const captionsOn    = ref(false)
const handRaised    = ref(false)
const remoteHandRaised = ref(false)
const remoteMicOn   = ref(true)
const remoteCameraOn = ref(true) // DAGDAG ITO
const remoteAvatarColor = ref('#0f9d58')

const remoteConnected = ref(false)
const peerError       = ref('')
const isHost          = ref(false)
const isLocked        = ref(false)
const rejected        = ref(false)

const showAdmitModal  = ref(false)
const pendingCall     = ref(null)
const pendingConn     = ref(null)

const showReadyDialog  = ref(false)
const showEmojiPicker  = ref(false)
const showMoreDropdown = ref(false)
const isFullscreen     = ref(false)
const copied           = ref(false)
const activePanel      = ref(null)
const callTime         = ref('0:00')

const floatingEmojis = ref([])
const captionText = ref('')
let recognition = null

const messages     = ref([])
const messageInput = ref('')
const unreadCount  = ref(0)

const showSettings = ref(false)
const settingsTab = ref('audio')
const microphones = ref([])
const speakers = ref([])
const cameras = ref([])
const settingsVidPreview = ref(null)

let localStream        = null
let screenStream       = null
let originalVideoTrack = null
let callTimer          = null
let pipWindow          = null 
let pipInitialized     = false 

// --- AUDIO MIXING & RECORDING STATE ---
const isRecording = ref(false)
const isUploading = ref(false) 
let mediaRecorder = null
let recordedChunks = []
let resolveUploadPromise = null
let audioContext = null;
let audioDestination = null;

let audioSources = []; 
let recordingStartTime = null; // DAGDAG: Para i-track ang totoong tagal ng recording

const localClass = computed(() => {
  if (remoteConnected.value && !screenSharing.value) return 'local-pip'
  if (screenSharing.value) return 'local-pip local-pip-cam'
  return 'local-solo'
})

const remoteClass = computed(() => {
  if (!remoteConnected.value) return 'wrap-hidden'
  if (screenSharing.value) return 'remote-pip'
  return 'remote-fill'
})

const screenClass = computed(() => {
  return screenSharing.value ? 'screen-fill' : 'wrap-hidden'
})

const meetingLink = computed(() => {
  return `${window.location.origin}/meeting/${route.params.code}`
})

// BAGONG logic para sa PiP sa Mobile at Desktop
async function openDocumentPiP() {
  showMoreDropdown.value = false;

  // 1. DESKTOP PIP: Gamitin ang Custom PiP natin na may buttons
  if ('documentPictureInPicture' in window && window.innerWidth > 768) {
    if (pipWindow && !pipWindow.closed) return;
    try {
      pipWindow = await window.documentPictureInPicture.requestWindow({ width: 320, height: 420 });
      pipWindow.addEventListener('pagehide', () => { pipWindow = null; pipInitialized = false; });
      renderCustomPiP();
      return; // Kapag Desktop, hanggang dito lang. WAG nang bumaba.
    } catch (err) { 
      console.error("Desktop PiP Error:", err); 
      return; // WAG mag-fallback sa Native Video PiP para hindi pumangit ang UI sa laptop!
    }
  }

  // 2. MOBILE FALLBACK: Kapag nasa phone, standard native video PiP lang talaga ang pwede
  const activeVideoEl = remoteConnected.value ? remoteVideoEl.value : localVideoEl.value;
  if (!activeVideoEl || !activeVideoEl.requestPictureInPicture) return;

  try {
    if (document.pictureInPictureElement) {
      await document.exitPictureInPicture();
    } else {
      await activeVideoEl.requestPictureInPicture();
    }
  } catch (err) { console.error("Mobile PiP Fallback Error:", err); }
}

async function handleVisibilityChange() {
  if (document.hidden) {
    // Auto PiP kapag lumipat ng tab (Subukan lang sa Desktop)
    if ('documentPictureInPicture' in window && window.innerWidth > 768 && !pipWindow) {
      try { await openDocumentPiP(); } catch (e) {}
    }
  } else {
    // Kapag bumalik sa tab, isara ang custom PiP
    if (pipWindow) {
      pipWindow.close();
      pipWindow = null;
      pipInitialized = false;
    }
    // Isara din ang native PiP kung sakaling naiwang bukas
    if (document.pictureInPictureElement) {
      document.exitPictureInPicture().catch(()=>{});
    }
  }
}

function renderCustomPiP() {
  if (!pipWindow || pipWindow.closed) return;
  const doc = pipWindow.document;

  doc.body.innerHTML = `
    <style>
      body { margin: 0; background: #202124; font-family: 'Google Sans', sans-serif; overflow: hidden; display: flex; flex-direction: column; height: 100vh; color: white; }
      .main-content { flex: 1; position: relative; overflow: hidden; border-radius: 12px 12px 0 0; margin: 8px 8px 0 8px; background: #182b2d; }
      .video-layer { width: 100%; height: 100%; object-fit: cover; }
      .avatar-layer { position: absolute; inset: 0; background: #182b2d; display: flex; align-items: center; justify-content: center; z-index: 1; }
      .avatar-circle { width: clamp(80px, 40%, 140px); aspect-ratio: 1; border-radius: 50%; background: #2d2e30; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,.4); }
      .name-plate { position: absolute; bottom: 12px; left: 12px; font-size: 13px; font-weight: 500; text-shadow: 0 1px 2px rgba(0,0,0,.8); z-index: 10; padding: 4px 8px; background: rgba(0,0,0,0.5); border-radius: 4px;}
      .top-right-badges { position: absolute; top: 12px; right: 12px; z-index: 10; display: flex; gap: 8px; }
      .badge-mute { width: 28px; height: 28px; background: #ea4335; border-radius: 50%; display: flex; align-items: center; justify-content: center; } 
      
      .local-pip-overlay { position: absolute; bottom: 16px; right: 16px; width: 130px; height: 80px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.5); z-index: 12; background: #182b2d; border: 1px solid rgba(255,255,255,0.1); }
      .local-pip-overlay video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
      .local-pip-overlay .avatar-circle { width: 44px; height: 44px; }
      .local-pip-overlay .name-plate { bottom: 4px; left: 4px; font-size: 11px; padding: 2px 4px; }

      .bottom-bar { height: 80px; background: #202124; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 0 16px; flex-shrink: 0; position: relative;}
      .ctrl-btn { width: 44px; height: 44px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; background: #3c4043; color: white; transition: background .2s; }
      .ctrl-btn:hover { background: #4a4e51; }
      .ctrl-btn.off { background: #fce8e6; color: #ea4335; }
      .ctrl-btn.active { background: #d2e3fc; color: #1967d2; }
      .ctrl-btn.end-call { width: 64px; border-radius: 24px; background: #ea4335; }
      .ctrl-btn.end-call:hover { background: #c5221f; }
      
      .menu-popup { position: absolute; bottom: 85px; background: #2d2e30; border-radius: 8px; padding: 8px 0; display: none; flex-direction: column; min-width: 200px; box-shadow: 0 4px 12px rgba(0,0,0,0.5); z-index: 100;}
      .menu-item { background: none; border: none; color: #e8eaed; padding: 12px 20px; text-align: left; display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 14px;}
      .menu-item:hover { background: rgba(255,255,255,0.1); }
    </style>

    <div class="main-content">
       <video id="main-video" autoplay playsinline muted class="video-layer"></video>
       <div id="main-avatar" class="avatar-layer"><div class="avatar-circle">${SVG_AVATAR}</div></div>
       <div class="top-right-badges">
          <div id="main-mute-badge" class="badge-mute">${SVG_MUTE_BADGE}</div>
       </div>
       
       <div id="main-hand" style="display: none; position: absolute; bottom: 42px; left: 12px; background: rgba(0,0,0,0.6); padding: 4px 8px; border-radius: 6px; font-size: 16px; z-index: 10;">✋</div>
       <div id="main-name" class="name-plate">${isHost.value ? 'Host (You)' : 'Guest (You)'}</div>
       
       <div id="local-pip" class="local-pip-overlay">
          <video id="local-video" autoplay playsinline muted></video>
          <div id="local-avatar" class="avatar-layer"><div class="avatar-circle">${SVG_AVATAR}</div></div>
          <div id="local-mute-badge" class="top-right-badges" style="top:4px; right:4px;"><div class="badge-mute" style="width:20px; height:20px;">${SVG_MUTE_BADGE}</div></div>
          
          <div id="local-hand" style="display: none; position: absolute; bottom: 24px; left: 4px; background: rgba(0,0,0,0.6); padding: 2px 6px; border-radius: 4px; font-size: 12px; z-index: 10;">✋</div>
          <div class="name-plate">${isHost.value ? 'Host (You)' : 'Guest (You)'}</div>
       </div>
    </div>
    
    <div class="bottom-bar">
       <div id="menu-popup" class="menu-popup">
          <button class="menu-item" id="menu-msg">${SVG_MSG} In-call messages</button>
          <button class="menu-item" id="menu-hand">${SVG_HAND} Raise hand</button>
       </div>
       <button id="btn-mic" class="ctrl-btn"></button>
       <button id="btn-cam" class="ctrl-btn"></button>
       <button id="btn-share" class="ctrl-btn"></button>
       <button id="btn-more" class="ctrl-btn">${SVG_MORE}</button>
       <button id="btn-end" class="ctrl-btn end-call">${SVG_END}</button>
    </div>
  `;

  pipWindow.requestAnimationFrame(() => {
    setTimeout(() => {
      if (!pipWindow || pipWindow.closed) return;

      doc.getElementById('btn-mic').onclick = toggleMic;
      doc.getElementById('btn-cam').onclick = toggleCamera;
      doc.getElementById('btn-share').onclick = toggleScreenShare;
      doc.getElementById('btn-end').onclick = () => { pipWindow.close(); leaveCall(); };
      
      const menuPopup = doc.getElementById('menu-popup');
      doc.getElementById('btn-more').onclick = () => {
        menuPopup.style.display = menuPopup.style.display === 'flex' ? 'none' : 'flex';
      };
      
      doc.getElementById('menu-msg').onclick = () => { menuPopup.style.display = 'none'; togglePanel('chat'); window.focus(); };
      doc.getElementById('menu-hand').onclick = () => { menuPopup.style.display = 'none'; toggleHand(); };

      doc.getElementById('main-video').onloadedmetadata = function() { this.play().catch(()=>{}); };
      doc.getElementById('local-video').onloadedmetadata = function() { this.play().catch(()=>{}); };

      pipInitialized = true;
      
      setTimeout(() => {
          updatePiPUI();
          const mainVid = doc.getElementById('main-video');
          if (mainVid && mainVid.paused) mainVid.play().catch(()=>{});
      }, 100);
    }, 100); 
  });
}

function applyStream(vidElement, streamToApply) {
  if (!vidElement) return;
  if (vidElement.srcObject !== streamToApply) {
    vidElement.srcObject = streamToApply;
    vidElement.load(); 
  }
  setTimeout(() => {
    if (streamToApply && vidElement.paused) {
      vidElement.play().catch(()=>{});
    }
  }, 50);
}

function updatePiPUI() {
  if (!pipWindow || pipWindow.closed || !pipInitialized) return;
  const doc = pipWindow.document;

  const btnMic = doc.getElementById('btn-mic');
  const btnCam = doc.getElementById('btn-cam');
  const btnShare = doc.getElementById('btn-share');

  btnMic.className = micOn.value ? 'ctrl-btn' : 'ctrl-btn off';
  btnMic.innerHTML = micOn.value ? SVG_MIC_ON : SVG_MIC_OFF;
  
  btnCam.className = cameraOn.value ? 'ctrl-btn' : 'ctrl-btn off';
  btnCam.innerHTML = cameraOn.value ? SVG_CAM_ON : SVG_CAM_OFF;
  
  btnShare.className = screenSharing.value ? 'ctrl-btn active' : 'ctrl-btn';
  btnShare.innerHTML = SVG_SHARE;

  const mainVid = doc.getElementById('main-video');
  const mainAvatar = doc.getElementById('main-avatar');
  const mainName = doc.getElementById('main-name');
  const mainMute = doc.getElementById('main-mute-badge');
  const mainHand = doc.getElementById('main-hand');

  const localPip = doc.getElementById('local-pip');
  const localVid = doc.getElementById('local-video');
  const localAvatar = doc.getElementById('local-avatar');
  const localMute = doc.getElementById('local-mute-badge');
  const localHand = doc.getElementById('local-hand');

  if (screenSharing.value) {
    applyStream(mainVid, screenStream);
    mainVid.style.objectFit = 'contain';
    mainVid.style.transform = 'scaleX(1)';
    mainAvatar.style.display = 'none';
    mainName.textContent = 'You are presenting';
    mainMute.style.display = 'none';
    mainHand.style.display = 'none';

    localPip.style.display = 'block';
    applyStream(localVid, cameraOn.value ? localStream : null);
    localAvatar.style.display = cameraOn.value ? 'none' : 'flex';
    localMute.style.display = micOn.value ? 'none' : 'flex';
    localHand.style.display = handRaised.value ? 'block' : 'none';

  } else if (remoteConnected.value) {
    applyStream(mainVid, remoteVideoEl.value?.srcObject || null);
    mainVid.style.objectFit = 'cover';
    mainVid.style.transform = 'scaleX(1)';
    mainAvatar.style.display = 'none'; 
    mainName.textContent = isHost.value ? 'Guest' : 'Host';
    mainMute.style.display = 'none';
    mainHand.style.display = remoteHandRaised.value ? 'block' : 'none';

    localPip.style.display = 'block';
    applyStream(localVid, cameraOn.value ? localStream : null);
    localAvatar.style.display = cameraOn.value ? 'none' : 'flex';
    localMute.style.display = micOn.value ? 'none' : 'flex';
    localHand.style.display = handRaised.value ? 'block' : 'none';

  } else {
    applyStream(mainVid, localStream);
    mainVid.style.objectFit = 'cover';
    mainVid.style.transform = 'scaleX(-1)';
    
    localPip.style.display = 'none';
    mainName.textContent = isHost.value ? 'Host (You)' : 'Guest (You)';
    mainMute.style.display = micOn.value ? 'none' : 'flex';
    mainHand.style.display = handRaised.value ? 'block' : 'none';
    
    if (cameraOn.value) {
      mainVid.style.opacity = '1';
      mainAvatar.style.display = 'none';
    } else {
      mainVid.style.opacity = '0';
      mainAvatar.style.display = 'flex';
    }
  }
}

watch([micOn, cameraOn, screenSharing, remoteConnected, handRaised, remoteHandRaised], () => {
  updatePiPUI();
});

// --- AUDIO MIXING & RECORDING INITIALIZATION ---

function initAudioMixing() {
  if (!audioContext) {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    audioDestination = audioContext.createMediaStreamDestination();
  }
  // Isama ang boses mo (local stream)
  if (localStream && localStream.getAudioTracks().length > 0) {
    const localSource = audioContext.createMediaStreamSource(localStream);
    localSource.connect(audioDestination);
    audioSources.push(localSource); 
  }
}

// Function para isama ang boses ng guest
function addRemoteStreamToMix(stream) {
  if (!audioContext || !audioDestination) return;
  if (stream && stream.getAudioTracks().length > 0) {
    const remoteSource = audioContext.createMediaStreamSource(stream);
    remoteSource.connect(audioDestination);
    audioSources.push(remoteSource); 
  }
}

async function startRecording() {
  if (!localStream) return;
  
  try {
    initAudioMixing();
    
    mediaRecorder = new MediaRecorder(audioDestination.stream, { 
      mimeType: 'audio/webm'
    });
    
    mediaRecorder.ondataavailable = (event) => {
      if (event.data.size > 0) {
        recordedChunks.push(event.data);
      }
    };
    
    mediaRecorder.onstop = async () => {
      const blob = new Blob(recordedChunks, { type: 'audio/webm' });
      recordedChunks = []; 
      audioSources = []; 
      
      // DAGDAG: Calculate natin yung totoong duration para ma-fix natin bago i-upload
      const duration = Date.now() - recordingStartTime;
      
      // Fix the WebM blob using the library!
      fixWebmDuration(blob, duration, async (fixedBlob) => {
        await uploadRecording(fixedBlob); // Upload the fixed blob!
        if (resolveUploadPromise) resolveUploadPromise();
      });
    };

    recordingStartTime = Date.now(); // Simulan ang timer
    mediaRecorder.start(1000); 
    isRecording.value = true;
    logAction('recording_started', { meeting_code: route.params.code }).catch(() => {});
    
  } catch (error) {
    console.error("Recording failed to start:", error);
  }
}

async function uploadRecording(blob) {
  const code = route.params.code;
  const formData = new FormData();
  
  formData.append('audio', blob, `audio-record-${code}-${Date.now()}.webm`);
  formData.append('speaker', isHost.value ? 'Host' : 'Guest');
  formData.append('meeting_code', code);
  
  try {
    await axios.post(`${API_URL}/api/meetings/${code}/recordings`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    console.log("Audio Recording uploaded successfully");
  } catch (err) {
    const backendError = err.response?.data?.error || err.response?.data?.message || err.message;
    alert("RECORDING UPLOAD ERROR: " + backendError);
  }
}

onMounted(async () => {
  const code = route.params.code

  if (!sessionStorage.getItem('prejoined_' + code)) {
    router.replace(`/meeting/${code}/prejoin`)
    return // Pigilan ang pag-run ng codes sa ibaba
  }

  const avatarColors = ['#e53935', '#d81b60', '#8e24aa', '#3949ab', '#039be5', '#00897b', '#43a047', '#f4511e', '#ff8f00']
  remoteAvatarColor.value = avatarColors[Math.floor(Math.random() * avatarColors.length)]

  isHost.value   = sessionStorage.getItem('isHost') === 'true'
  micOn.value    = sessionStorage.getItem('micOn') !== 'false'
  cameraOn.value = sessionStorage.getItem('cameraOn') !== 'false'

  const savedBlur = sessionStorage.getItem('blurOn') === 'true'
  if (savedBlur) {
    setTimeout(() => { toggleBlur(); }, 1000); // Auto-on ang blur pagkapasok
  }

  try {
    localStream = await navigator.mediaDevices.getUserMedia({ 
      video: true, 
      audio: {
        echoCancellation: true,
        noiseSuppression: true,
        autoGainControl: true,
        sampleRate: 48000, 
        channelCount: 2    
      } 
    })
    originalVideoTrack = localStream.getVideoTracks()[0]
    applyTrackStates()
    if (rawVideoEl.value) {
      rawVideoEl.value.srcObject = localStream;
      rawVideoEl.value.play().catch(()=>{}); // Piliting mag-play!
    } // I-feed sa hidden video para sa AI
    if (localVideoEl.value) localVideoEl.value.srcObject = localStream
  } catch {
    peerError.value = 'Could not access camera/microphone.'
    return
  }

  startCallTimer()
  peer.value = new Peer()

  peer.value.on('open', async (id) => {
    await logAction('peerjs_connected', { meeting_code: code, peer_id: id })

    if (isHost.value) {
      showReadyDialog.value = true
      await axios.post(`${API_URL}/api/meetings/${code}/peer`, { peer_id: id })

      peer.value.on('call', (call) => {
        // 1. Kung naka-lock ang kwarto, i-reject agad!
        if (isLocked.value) {
          call.close();
          return;
        }
        // 2. Kung bukas ang kwarto, i-hold muna ang tawag at ilabas ang Admit Modal
        pendingCall.value = call;
        showAdmitModal.value = true;
      })

      peer.value.on('connection', (conn) => {
        // 1. Kapag LOCKED ang kwarto, sendan agad ng signal na REJECTED siya
        if (isLocked.value) {
          conn.on('open', () => {
            conn.send({ type: 'rejected' });
            setTimeout(() => conn.close(), 500);
          });
          return;
        }

        // 2. I-hold din ang chat data ng guest habang naghihintay siya
        if (showAdmitModal.value) {
          pendingConn.value = conn;
        } else {
          dataConn.value = conn;
          conn.on('data', handleIncomingData);
        }
      })
    } else {
      await connectToHost(code)
    }
  })

  peer.value.on('error', (err) => {
    peerError.value = `Connection error: ${err.type}`
    logAction('peerjs_error', { meeting_code: code, error: err.type })
  })

  document.addEventListener('fullscreenchange', onFullscreenChange)
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onUnmounted(() => {
  clearInterval(callTimer)
  recognition?.stop()
  stopAllMedia()
  document.removeEventListener('fullscreenchange', onFullscreenChange)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  if (pipWindow) pipWindow.close()
})

async function connectToHost(code, attempts = 0) {
  if (attempts > 20) { peerError.value = 'Could not reach host.'; return }
  try {
    const { data } = await axios.get(`${API_URL}/api/meetings/${code}/peer`)
    if (!data.peer_id) { setTimeout(() => connectToHost(code, attempts + 1), 1000); return }

    const call = peer.value.call(data.peer_id, localStream)
    currentCall.value = call
    call.on('stream', (remote) => {
      remoteConnected.value = true
      rejected.value = false // DAGDAG: Na-accept na!
      if (remoteVideoEl.value) remoteVideoEl.value.srcObject = remote
      // Isama ang boses ni Host sa recording natin as guest
      addRemoteStreamToMix(remote);
    })
    call.on('close', () => { 
      if (!remoteConnected.value) {
        rejected.value = true;
      }
      remoteConnected.value = false 
    })

    const conn = peer.value.connect(data.peer_id)
    dataConn.value = conn
    conn.on('data', handleIncomingData)
  } catch {
    setTimeout(() => connectToHost(code, attempts + 1), 1000)
  }
}

function handleIncomingData(data) {
  if (data.type === 'chat') {
    messages.value.push({ id: Date.now(), sender: data.sender, text: data.text, time: data.time, isOwn: false })
    if (activePanel.value !== 'chat') unreadCount.value++
    scrollMessages()
  } else if (data.type === 'reaction') {
    const sender = isHost.value ? 'Guest' : 'Host'
    spawnEmoji(data.emoji, sender)
  } else if (data.type === 'hand') {
    remoteHandRaised.value = data.raised
  } else if (data.type === 'mic') {
    remoteMicOn.value = data.on
  } else if (data.type === 'camera') {
    remoteCameraOn.value = data.on
  } else if (data.type === 'force_mute') {
    if (micOn.value) {
      toggleMic();
      alert("The host has muted your microphone.");
    }
  } else if (data.type === 'rejected') {
    rejected.value = true;
  } else if (data.type === 'kick') {
    alert("You have been removed from the meeting by the host.");
    leaveCall();
  }
}

function toggleMic() {
  micOn.value = !micOn.value
  localStream?.getAudioTracks().forEach(t => (t.enabled = micOn.value))
  logAction(micOn.value ? 'mic_unmuted' : 'mic_muted', { meeting_code: route.params.code })

  if (dataConn.value?.open) {
    dataConn.value.send({ type: 'mic', on: micOn.value })
  }
}

function toggleCamera() {
  cameraOn.value = !cameraOn.value
  localStream?.getVideoTracks().forEach(t => (t.enabled = cameraOn.value))
  
  if (localVideoEl.value) {
      if (!cameraOn.value) {
         const canvas = document.createElement('canvas');
         canvas.width = 2; canvas.height = 2;
         const ctx = canvas.getContext('2d');
         ctx.fillStyle = '#000'; ctx.fillRect(0, 0, 2, 2);
         localVideoEl.value.srcObject = canvas.captureStream();
      } else {
         localVideoEl.value.srcObject = isBlurOn.value && processedStream ? new MediaStream([processedStream.getVideoTracks()[0], ...localStream.getAudioTracks()]) : localStream;
      }
  }
  
  logAction(cameraOn.value ? 'camera_turned_on' : 'camera_turned_off', { meeting_code: route.params.code })

  // DAGDAG ITO: I-broadcast ang camera status mo
  if (dataConn.value?.open) {
    dataConn.value.send({ type: 'camera', on: cameraOn.value })
  }
}

async function toggleScreenShare() {
  if (screenSharing.value) {
    const sender = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'video')
    if (sender && originalVideoTrack) sender.replaceTrack(originalVideoTrack)
    if (screenVideoEl.value) screenVideoEl.value.srcObject = null
    screenStream = null
    screenSharing.value = false
    logAction('screen_share_stopped', { meeting_code: route.params.code })
    return
  }
  try {
    screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true })
    const screenTrack = screenStream.getVideoTracks()[0]
    const sender = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'video')
    if (sender) sender.replaceTrack(screenTrack)
    if (screenVideoEl.value) screenVideoEl.value.srcObject = screenStream
    screenSharing.value = true
    logAction('screen_share_started', { meeting_code: route.params.code })

    screenTrack.onended = () => {
      const s2 = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'video')
      if (s2 && originalVideoTrack) s2.replaceTrack(originalVideoTrack)
      if (screenVideoEl.value) screenVideoEl.value.srcObject = null
      screenStream = null
      screenSharing.value = false
      logAction('screen_share_stopped', { meeting_code: route.params.code })
    }
  } catch { /* user cancelled */ }
}

async function toggleRecording() {
  if (isRecording.value) {
    // STOP RECORDING
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      isRecording.value = false;
      isUploading.value = true; // Paikutin/Ibahin ang kulay ng button habang nag-u-upload
      await new Promise((resolve) => {
        resolveUploadPromise = resolve;
        mediaRecorder.stop();
      });
      isUploading.value = false; // Babalik na sa circle icon kapag tapos na
    }
  } else {
    // START RECORDING
    startRecording();
  }
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
      // Setup ng AI Model
      selfieSegmentation = new SelfieSegmentation({locateFile: (file) => {
        return `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/${file}`;
      }});
      selfieSegmentation.setOptions({ modelSelection: 0 }); // Fast mode
      selfieSegmentation.onResults(onBlurResults);
      await selfieSegmentation.initialize();
    }

    isBlurLoading.value = false;
    processBlurFrame(); // Simulan ang pag-loop

    // Kunin ang video mula sa invisible Canvas at ipakita sa screen natin
    processedStream = blurCanvasEl.value.captureStream(30);
    const processedTrack = processedStream.getVideoTracks()[0];

    if (localVideoEl.value) {
      localVideoEl.value.srcObject = new MediaStream([processedTrack, ...localStream.getAudioTracks()]);
    }
    replaceVideoTrackInPeer(processedTrack); // Ipadala ang blurred video sa Guest!

  } else {
    // Kapag pinatay ang Blur
    cancelAnimationFrame(blurRafId);
    isBlurLoading.value = false;
    if (localVideoEl.value) {
      localVideoEl.value.srcObject = localStream; // Balik sa normal camera
    }
    replaceVideoTrackInPeer(originalVideoTrack);
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

function replaceVideoTrackInPeer(newTrack) {
  if (currentCall.value?.peerConnection) {
    const sender = currentCall.value.peerConnection.getSenders().find(s => s.track?.kind === 'video');
    if (sender) sender.replaceTrack(newTrack);
  }
}
// ==========================

// ==========================
// HOST CONTROLS
// ==========================
function forceMuteGuest() {
  if (dataConn.value?.open) {
    dataConn.value.send({ type: 'force_mute' });
  }
}

function kickGuest() {
  if (confirm("Remove this participant from the meeting?")) {
    if (dataConn.value?.open) dataConn.value.send({ type: 'kick' });
    setTimeout(() => {
      currentCall.value?.close();
      remoteConnected.value = false;
    }, 500);
  }
}
// ==========================

// ==========================
// ADMIT / DENY GUEST LOGIC
// ==========================
function admitGuest() {
  showAdmitModal.value = false;
  
  if (pendingCall.value) {
    currentCall.value = pendingCall.value;
    pendingCall.value.answer(localStream); // Dito pa lang natin sasagutin yung tawag
    logAction('peerjs_call_received', { meeting_code: route.params.code });
    
    currentCall.value.on('stream', (remote) => {
      remoteConnected.value = true;
      rejected.value = false;
      if (remoteVideoEl.value) remoteVideoEl.value.srcObject = remote;
      addRemoteStreamToMix(remote);
    });
    
    currentCall.value.on('close', () => { 
      if (!remoteConnected.value) rejected.value = true;
      remoteConnected.value = false; 
    });
    
    pendingCall.value = null;
  }
  
  if (pendingConn.value) {
    dataConn.value = pendingConn.value;
    dataConn.value.on('data', handleIncomingData);
    pendingConn.value = null;
  }
}

function denyGuest() {
  showAdmitModal.value = false;
  if (pendingCall.value) {
    pendingCall.value.close(); 
    pendingCall.value = null;
  }
  if (pendingConn.value) {
    // DAGDAG ITO: Sabihan yung Guest na na-deny siya!
    pendingConn.value.send({ type: 'rejected' });
    setTimeout(() => {
      pendingConn.value.close();
      pendingConn.value = null;
    }, 500);
  }
}
// ==========================

function toggleHand() {
  handRaised.value = !handRaised.value
  if (dataConn.value?.open) dataConn.value.send({ type: 'hand', raised: handRaised.value })
  logAction(handRaised.value ? 'hand_raised' : 'hand_lowered', { meeting_code: route.params.code })
}

function toggleEmojiPicker() { showEmojiPicker.value = !showEmojiPicker.value; showMoreDropdown.value = false }
function spawnEmoji(emoji, senderName) {
  const id = Date.now() + Math.random()
  floatingEmojis.value.push({ id, emoji, senderName, x: 3 + Math.random() * 20 })
  setTimeout(() => { floatingEmojis.value = floatingEmojis.value.filter(e => e.id !== id) }, 3000)
}

function sendReaction(emoji) {
  spawnEmoji(emoji, 'You') // Kapag ikaw pumindot, "You" ang lalabas
  showEmojiPicker.value = false
  if (dataConn.value?.open) dataConn.value.send({ type: 'reaction', emoji })
}

function toggleMoreDropdown() { showMoreDropdown.value = !showMoreDropdown.value; showEmojiPicker.value = false }
function toggleFullscreen() {
  if (!document.fullscreenElement) document.documentElement.requestFullscreen?.()
  else document.exitFullscreen?.()
  showMoreDropdown.value = false
}
function onFullscreenChange() { isFullscreen.value = !!document.fullscreenElement }

function sendMessage() {
  const text = messageInput.value.trim()
  if (!text) return
  const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  
  messages.value.push({ id: Date.now(), sender: 'You', text, time, isOwn: true })
  
  const code = route.params.code;
  
  axios.post(`${API_URL}/api/meetings/${code}/chats`, {
    meeting_code: code,
    sender: isHost.value ? 'Host' : 'Guest',
    message: text
  }).then(res => {
    console.log("Chat saved to DB!");
  }).catch(err => {
    const backendError = err.response?.data?.error || err.response?.data?.message || err.message;
    alert("CHAT SYNC ERROR: " + backendError);
  });

  if (dataConn.value?.open) {
    dataConn.value.send({ type: 'chat', sender: isHost.value ? 'Host' : 'Guest', text, time })
  }
  
  messageInput.value = ''
  scrollMessages()
}

async function scrollMessages() {
  await nextTick()
  if (messagesListEl.value) messagesListEl.value.scrollTop = messagesListEl.value.scrollHeight
}

function togglePanel(name) {
  activePanel.value = activePanel.value === name ? null : name
  if (name === 'chat') unreadCount.value = 0
}

async function leaveCall() {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    isRecording.value = false;
    isUploading.value = true; 
    await new Promise((resolve) => {
      resolveUploadPromise = resolve;
      mediaRecorder.stop();
    });
  }

  stopAllMedia()
  if (document.fullscreenElement) document.exitFullscreen?.()
  const code = route.params.code;
  router.push(`/meeting/${code}/left`)

  logAction('user_left_call_completed', { meeting_code: code }).catch(() => {})
  axios.patch(`${API_URL}/api/meetings/${code}/end`).catch(() => {})

  currentCall.value?.close()
  dataConn.value?.close()
  peer.value?.destroy()
  clearInterval(callTimer)

  sessionStorage.setItem('wasHost', sessionStorage.getItem('isHost') ?? 'false')
  // DAGDAG ITO: Sinama natin ang 'prejoined_' sa mga buburahin
  ;['isHost', 'meetingCode', 'micOn', 'cameraOn', 'prejoined_' + code].forEach(k => sessionStorage.removeItem(k))
}

function stopAllMedia() {
  if (localStream) {
      localStream.getTracks().forEach(t => {
          t.stop();
          localStream.removeTrack(t);
      });
  }
  if (screenStream) {
      screenStream.getTracks().forEach(t => {
          t.stop();
          screenStream.removeTrack(t);
      });
  }
  if (localVideoEl.value) localVideoEl.value.srcObject = null;
  if (remoteVideoEl.value) remoteVideoEl.value.srcObject = null;
  if (screenVideoEl.value) screenVideoEl.value.srcObject = null;
  
  if (audioContext && audioContext.state !== 'closed') {
    audioContext.close();
  }
  
  if (pipWindow) {
      const pipLocal = pipWindow.document.getElementById('local-video');
      const pipMain = pipWindow.document.getElementById('main-video');
      if (pipLocal) pipLocal.srcObject = null;
      if (pipMain) pipMain.srcObject = null;
      pipWindow.close();
  }
}

function closeAll() { showEmojiPicker.value = false; showMoreDropdown.value = false }
function applyTrackStates() {
  localStream?.getAudioTracks().forEach(t => (t.enabled = micOn.value))
  localStream?.getVideoTracks().forEach(t => (t.enabled = cameraOn.value))
}
function startCallTimer() {
  let secs = 0
  callTimer = setInterval(() => {
    secs++
    callTime.value = `${Math.floor(secs / 60)}:${String(secs % 60).padStart(2, '0')}`
  }, 1000)
}
async function copyLink() {
  await navigator.clipboard.writeText(meetingLink.value)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

async function openSettings() {
  showMoreDropdown.value = false;
  showSettings.value = true;
  try {
    const devices = await navigator.mediaDevices.enumerateDevices();
    microphones.value = devices.filter(d => d.kind === 'audioinput');
    speakers.value = devices.filter(d => d.kind === 'audiooutput');
    cameras.value = devices.filter(d => d.kind === 'videoinput');

    if (settingsTab.value === 'video') {
      nextTick(() => {
        if (settingsVidPreview.value && localStream) {
          settingsVidPreview.value.srcObject = localStream;
        }
      });
    }
  } catch (err) {
    console.warn("Could not fetch devices", err);
  }
}

watch(settingsTab, async (newVal) => {
  if (newVal === 'video' && localStream) {
    await nextTick();
    if (settingsVidPreview.value) {
      settingsVidPreview.value.srcObject = localStream;
    }
  }
})

// =========================================
// 📱 MOBILE PIP DRAGGABLE LOGIC
// =========================================
const pipElRef = ref(null);

const pipPosition = reactive({
  initialX: 0, initialY: 0,
  currentX: 0, currentY: 0,
  xOffset: 0, yOffset: 0,
  active: false,
  isMobile: false
});

function updateIsMobile() {
  pipPosition.isMobile = window.innerWidth <= 768;
  if (!pipPosition.isMobile) {
    resetPipPosition();
  }
}

function setTranslate(xPos, yPos, el) {
  if (!el) return;
  el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
}

function constrainPosition(x, y, pipEl) {
  if (!pipEl) return { x, y };

  const pipRect = pipEl.getBoundingClientRect();
  const sidePanelWidth = activePanel.value ? 360 : 0; 
  const windowPadding = 16; 
  const bottomBarHeight = 72; 

  const maxX = 0; 
  const minX = -(window.innerWidth - pipRect.width - windowPadding * 2 - sidePanelWidth);
  const maxY = windowPadding; 
  const minY = -(window.innerHeight - pipRect.height - bottomBarHeight - windowPadding * 3);

  const constrainedX = Math.min(Math.max(x, minX), maxX);
  const constrainedY = Math.min(Math.max(y, minY), maxY);

  return { x: constrainedX, y: constrainedY };
}

function onDragStart(e) {
  updateIsMobile();
  if (!pipPosition.isMobile || !pipElRef.value) return;

  if (e.type === "touchstart") {
    pipPosition.initialX = e.touches[0].clientX - pipPosition.xOffset;
    pipPosition.initialY = e.touches[0].clientY - pipPosition.yOffset;
  } else {
    pipPosition.initialX = e.clientX - pipPosition.xOffset;
    pipPosition.initialY = e.clientY - pipPosition.yOffset;
  }

  if (e.target === pipElRef.value || pipElRef.value.contains(e.target)) {
    pipPosition.active = true;
    pipElRef.value.style.cursor = 'grabbing';
    pipElRef.value.style.transition = 'none'; 
  }
}

function onDragMove(e) {
  if (!pipPosition.active || !pipElRef.value) return;

  if (e.cancelable) e.preventDefault();

  if (e.type === "touchmove") {
    pipPosition.currentX = e.touches[0].clientX - pipPosition.initialX;
    pipPosition.currentY = e.touches[0].clientY - pipPosition.initialY;
  } else {
    pipPosition.currentX = e.clientX - pipPosition.initialX;
    pipPosition.currentY = e.clientY - pipPosition.initialY;
  }

  const constrained = constrainPosition(pipPosition.currentX, pipPosition.currentY, pipElRef.value);

  pipPosition.xOffset = constrained.x;
  pipPosition.yOffset = constrained.y;

  setTranslate(constrained.x, constrained.y, pipElRef.value);
}

function onDragEnd() {
  if (!pipElRef.value) return;
  pipPosition.active = false;
  pipPosition.initialX = pipPosition.currentX;
  pipPosition.initialY = pipPosition.currentY;
  pipElRef.value.style.cursor = 'grab';
  pipElRef.value.style.transition = 'all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)'; 
}

function resetPipPosition() {
  if (!pipElRef.value) return;
  pipPosition.initialX = 0; pipPosition.initialY = 0;
  pipPosition.currentX = 0; pipPosition.currentY = 0;
  pipPosition.xOffset = 0; pipPosition.yOffset = 0;
  setTranslate(0, 0, pipElRef.value);
}

onBeforeUnmount(() => {
  if (pipPosition.active) onDragEnd();
});
</script>

<style scoped>
/* .. Yung existing styles mo same pa rin ..*/
.room { 
  position: fixed; /* I-lock sa screen para bawal mag-scroll */
  top: 0;
  left: 0;
  width: 100%; 
  height: 100dvh; /* Sakto sa phone kahit may address bar */
  background: #202124; 
  display: flex; 
  flex-direction: column; 
  overflow: hidden; 
  font-family: 'Google Sans', Roboto, Arial, sans-serif; 
  color: #e8eaed; 
  z-index: 50; 
}
.top-bar { position: absolute; top: 12px; right: 16px; display: flex; align-items: center; gap: 10px; z-index: 20; }
.hand-top-badge { display: flex; align-items: center; gap: 6px; background: #34a853; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 500; }
/* Tinanggal na yung .avatar-circle dito */
.content-area { flex: 1; position: relative; overflow: hidden; }
.video-wrap { position: absolute; border-radius: 12px; overflow: hidden; background: #202124; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); box-sizing: border-box; }
.wrap-hidden { display: none; }
.local-pip { bottom: 24px; right: 24px; width: 280px; aspect-ratio: 16/9; z-index: 12; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.3), 0 4px 8px 3px rgba(0,0,0,0.15); }
.remote-pip { bottom: 24px; right: 24px; width: 280px; aspect-ratio: 16/9; z-index: 11; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.3), 0 4px 8px 3px rgba(0,0,0,0.15); }
.local-pip-cam { bottom: 195px; right: 24px; }
.local-solo, .remote-fill, .screen-fill {
  position: absolute;
  top: 70px;
  left: 150px;
  right: 150px;
  bottom: 50px;
  width: auto;
  height: auto;
  border-radius: 12px;
  background: #3c4043;
  transition: all 0.4s ease-in-out;
}

.local-solo { z-index: 5; }
.remote-fill { z-index: 4; }
.screen-fill { z-index: 3; background: #000; }
.screen-fill .video-fill { object-fit: contain; }

/* 2. SHRINK UP: Kapag in-open ang Emoji Picker */
.content-area.with-emoji .local-solo,
.content-area.with-emoji .remote-fill,
.content-area.with-emoji .screen-fill {
  bottom: 86px; /* Aangat yung video screen pataas */
}

/* 3. SHRINK LEFT: Kapag in-open ang Chat o People Panel */
.content-area.with-panel .local-solo,
.content-area.with-panel .remote-fill,
.content-area.with-panel .screen-fill {
  right: 392px; /* Liliit yung video pakaliwa para hindi matakpan ng chat */
}

/* BONUS: I-usog din yung maliliit na PiP sa gilid kapag may chat panel na nag-open */
.content-area.with-panel .local-pip,
.content-area.with-panel .remote-pip,
.content-area.with-panel .local-pip-cam {
  right: 408px;
}
.video-fill { width: 100%; height: 100%; object-fit: cover; }
.video-fill.mirrored { transform: scaleX(-1); }
.video-fill.vid-hidden { opacity: 0; }
.tile-top-right { position: absolute; top: 8px; right: 8px; display: flex; gap: 6px; z-index: 10; }
.tile-icon-btn { width: 32px; height: 32px; border-radius: 50%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); cursor: pointer; }
.mute-badge { background: #ea4335; }
.three-dots:hover { background: rgba(0,0,0,0.8); }
.tile-bottom-left { position: absolute; bottom: 10px; left: 10px; display: flex; align-items: center; gap: 8px; z-index: 10; }
.tile-name { font-size: 13px; color: #fff; background: rgba(0,0,0,0.6); padding: 6px 10px; border-radius: 6px; font-weight: 500; letter-spacing: 0.3px; text-shadow: 0 1px 2px rgba(0,0,0,.5); }
.hand-indicator { background: rgba(0,0,0,0.6); padding: 4px 8px; border-radius: 6px; font-size: 16px; }
.tile-avatar { position: absolute; inset: 0; background: #182b2d; display: flex; align-items: center; justify-content: center; z-index: 1; }
.tile-avatar-circle { width: clamp(80px, 40%, 140px); aspect-ratio: 1; border-radius: 50%; background: #2d2e30; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,.4); }
.screen-presenter-bar { position: absolute; top: 16px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,.75); color: #fff; font-size: 14px; padding: 8px 18px; border-radius: 24px; display: flex; align-items: center; gap: 8px; z-index: 5; pointer-events: none; }
.emoji-float { position: absolute; bottom: 80px; display: flex; flex-direction: column; align-items: center; gap: 4px; z-index: 50; pointer-events: none; animation: floatUp 3s ease-out forwards; }
.emoji-char { font-size: 40px; line-height: 1; }
.emoji-you-pill { background: rgba(0,0,0,.6); color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 10px; }
@keyframes floatUp { 0% { opacity: 1; transform: translateY(0) scale(1); } 70% { opacity: 1; } 100% { opacity: 0; transform: translateY(-260px) scale(1.2); } }
.caption-bar { position: absolute; bottom: 80px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,.75); color: #fff; padding: 10px 20px; border-radius: 8px; font-size: 17px; max-width: 60%; text-align: center; z-index: 20; }
.hand-bottom-pill { position: absolute; bottom: 80px; left: 16px; background: #34a853; color: #fff; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px; z-index: 20; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
.emoji-picker { position: absolute; bottom: 76px; left: 50%; transform: translateX(-50%); background: #2d2e30; border-radius: 40px; padding: 8px 12px; display: flex; gap: 4px; z-index: 30; box-shadow: 0 4px 20px rgba(0,0,0,.5); }
.emoji-btn { background: none; border: none; font-size: 26px; cursor: pointer; padding: 6px; border-radius: 50%; transition: background .15s, transform .15s; line-height: 1; }
.emoji-btn:hover { background: rgba(255,255,255,.1); transform: scale(1.3); }
.more-btn-wrap { position: relative; }
.more-dropdown { position: absolute; bottom: calc(100% + 10px); left: 50%; transform: translateX(-50%); background: #2d2e30; border-radius: 8px; padding: 8px 0; z-index: 30; min-width: 220px; box-shadow: 0 4px 20px rgba(0,0,0,.5); white-space: nowrap; }
.dropdown-item { display: flex; align-items: center; gap: 12px; width: 100%; padding: 12px 20px; background: none; border: none; color: #e8eaed; font-size: 14px; cursor: pointer; text-align: left; transition: background .15s; }
.dropdown-item:hover { background: rgba(255,255,255,.08); }
.ready-dialog { position: absolute; bottom: 80px; left: 16px; width: 290px; background: #fff; border-radius: 12px; padding: 20px; z-index: 20; box-shadow: 0 8px 32px rgba(0,0,0,.4); }
.dialog-close { position: absolute; top: 10px; right: 10px; background: none; border: none; cursor: pointer; padding: 4px; border-radius: 50%; display: flex; }
.dialog-close:hover { background: #f1f3f4; }
.dialog-title { font-size: 16px; font-weight: 500; color: #202124; margin: 0 0 8px; }
.dialog-desc { font-size: 13px; color: #5f6368; margin: 0 0 12px; line-height: 1.5; }
.link-row { display: flex; align-items: center; gap: 8px; background: #f1f3f4; border-radius: 6px; padding: 8px 12px; margin-bottom: 10px; }
.link-text { flex: 1; font-size: 11px; font-family: monospace; color: #202124; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btn-copy { background: none; border: none; cursor: pointer; display: flex; padding: 2px; }
/* BAGONG FLOATING PANEL (Google Meet Style) */
.side-panel { 
  position: absolute; 
  top: 70px; /* Binago: Pantay na ngayon sa taas ng video! */
  right: 16px; 
  bottom: 86px; 
  width: 360px; 
  background: #202124; 
  z-index: 25; 
  display: flex; 
  flex-direction: column; 
  border-radius: 12px; 
  box-shadow: 0 4px 16px rgba(0,0,0,0.3); 
  overflow: hidden;   
}

/* SMOOTH TRANSITION */
.slide-panel-enter-active, .slide-panel-leave-active { 
  transition: all 0.4s ease-in-out;
}
.slide-panel-enter-from, .slide-panel-leave-to { 
  transform: translateX(400px); /* Galing sa kanan tapos papasok */
}

/* EMPTY STATE IMAGE STYLING */
.empty-chat-img {
  width: 140px; /* Sakto lang na laki */
  height: auto;
  margin-bottom: 8px;
  opacity: 0.9;
}
.panel-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #3c4043; flex-shrink: 0; }
.panel-header h3 { color: #e8eaed; font-size: 16px; font-weight: 500; margin: 0; }
.panel-close { background: none; border: none; color: #9aa0a6; cursor: pointer; padding: 4px; border-radius: 50%; display: flex; transition: background .2s; }
.panel-close:hover { background: #3c4043; }
.messages-list { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; scrollbar-width: thin; scrollbar-color: #3c4043 transparent; }
.no-messages { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: #5f6368; font-size: 14px; }
/* Push the first message to the bottom! */
.message-item:first-of-type { 
  margin-top: auto; 
}

/* Base styling para sa lahat ng chat */
.message-item { 
  display: flex; 
  flex-direction: column; 
  gap: 4px; 
  max-width: 85%; 
  width: fit-content;
}

/* 1. STYLING KAPAG IBANG TAO (GUEST) - Nasa Kaliwa */
.message-item:not(.own) { 
  align-self: flex-start; 
  align-items: flex-start; 
}
.message-item:not(.own) .msg-text { 
  background: #3c4043; 
  color: #e8eaed; 
  border-radius: 4px 16px 16px 16px; /* Chat bubble tail sa top-left */
}

/* 2. STYLING KAPAG IKAW (YOU) - Nasa Kanan */
.message-item.own { 
  align-self: flex-end; 
  align-items: flex-end; 
}
.message-item.own .msg-text { 
  background: #1a73e8; /* Blue bubble para sayo */
  color: #ffffff; 
  border-radius: 16px 4px 16px 16px; /* Chat bubble tail sa top-right */
}

/* CHAT BUBBLES & TEXT STYLING */
.msg-text { 
  padding: 8px 14px; 
  margin: 0; 
  font-size: 14px; 
  line-height: 1.4; 
}

/* META INFO (Pangalan at Oras) */
.msg-meta { 
  display: flex; 
  align-items: baseline; 
  gap: 8px; 
  font-size: 11px;
}
.message-item.own .msg-meta {
  flex-direction: row-reverse; /* Para mauna yung Oras bago "You" */
}
.msg-sender { font-weight: 500; }
.message-item:not(.own) .msg-sender { color: #e8eaed; }
.message-item.own .msg-sender { color: #8ab4f8; }
.msg-time { color: #9aa0a6; }
.chat-input-row { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-top: 1px solid #3c4043; flex-shrink: 0; }
.chat-input { flex: 1; background: #3c4043; border: none; border-radius: 24px; padding: 10px 16px; color: #e8eaed; font-size: 14px; outline: none; }
.chat-send { width: 40px; height: 40px; border-radius: 50%; border: none; background: #1a73e8; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.chat-send:hover:not(:disabled) { background: #1558b0; }
.chat-send:disabled { background: #3c4043; color: #5f6368; cursor: not-allowed; }
.people-count-text { color: #9aa0a6; font-size: 13px; padding: 12px 20px 4px; }
.error-banner { position: absolute; top: 60px; left: 50%; transform: translateX(-50%); background: #ea4335; color: #fff; padding: 10px 20px; border-radius: 8px; font-size: 14px; z-index: 30; }

.bottom-bar { display: flex; align-items: center; justify-content: space-between; padding: 0 16px; height: 72px; background: #202124; flex-shrink: 0; position: relative; z-index: 15; }
.bottom-left { display: flex; align-items: center; gap: 8px; color: #e8eaed; font-size: 14px; min-width: 160px; }
.recording-indicator { display: flex; align-items: center; gap: 6px; background: rgba(234, 67, 53, 0.2); color: #ea4335; padding: 4px 10px; border-radius: 4px; font-size: 13px; font-weight: 500; margin-right: 8px; }
.red-dot { width: 8px; height: 8px; background: #ea4335; border-radius: 50%; animation: pulse 1.5s infinite; }
@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

.sep { color: #5f6368; }
.call-code { font-family: monospace; }
.controls-center { display: flex; align-items: center; gap: 6px; position: absolute; left: 50%; transform: translateX(-50%); }
.ctrl-btn { width: 48px; height: 48px; border-radius: 50%; border: none; outline: none; background: #3c4043; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .2s, transform .1s; flex-shrink: 0; }
.ctrl-btn:hover { background: #4a4e51; transform: scale(1.06); }
.ctrl-btn:active { transform: scale(0.96); }
.ctrl-btn.off { background: #ea4335; }
.ctrl-btn.off:hover { background: #c5221f; }
.ctrl-btn.active { background: #1a73e8; }
.ctrl-btn.active:hover { background: #1558b0; }
.ctrl-btn.end-call { background: #ea4335; width: 56px; height: 56px; }
.ctrl-btn.end-call:hover { background: #c5221f; }
.bottom-right { display: flex; align-items: center; gap: 6px; min-width: 100px; justify-content: flex-end; }
.util-btn { position: relative; width: 40px; height: 40px; border-radius: 50%; border: none; outline: none; background: transparent; color: #e8eaed; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .2s; }
.util-btn:hover { background: #3c4043; }
.util-btn.active { background: #1a73e8; }
.util-badge { position: absolute; top: 0; right: 0; background: #ea4335; color: #fff; font-size: 9px; font-weight: 700; width: 14px; height: 14px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

/* Transitions */
.slide-up-enter-active, .slide-up-leave-active { transition: all .3s ease; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px); }
.pop-enter-active, .pop-leave-active { transition: all .15s ease; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(.88); }
.pop-center-enter-active, .pop-center-leave-active { transition: all 0.4s ease-in-out; }
.pop-center-enter-from, .pop-center-leave-to { opacity: 0; transform: translateX(-50%) scale(.88); }

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

.video-preview-block { width: 220px; height: 124px; background: #000; border-radius: 8px; overflow: hidden; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* =========================================
   📱 MOBILE VIEW RESPONSIVENESS FIX
   ========================================= */

.util-badge-menu { background: #ea4335; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 10px; margin-left: auto; }
/* Itago ang mobile menu items sa desktop */
.mobile-only-menu { display: none; }

/* ========================
   PEOPLE PANEL & HOST UI
   ======================== */
.host-controls { background: rgba(26, 115, 232, 0.1); margin: 16px; border-radius: 8px; border: 1px solid rgba(26, 115, 232, 0.3); overflow: hidden; }
.host-controls-header { background: rgba(26, 115, 232, 0.15); padding: 10px 16px; display: flex; align-items: center; gap: 8px; color: #8ab4f8; font-size: 13px; font-weight: 500; }
.host-control-item { display: flex; align-items: center; justify-content: space-between; padding: 16px; }
.control-text { display: flex; flex-direction: column; gap: 4px; }
.control-text strong { color: #e8eaed; font-size: 14px; font-weight: 500; }
.control-text small { color: #9aa0a6; font-size: 12px; }

/* Toggle Switch CSS */
.switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #5f6368; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
input:checked + .slider { background-color: #1a73e8; }
input:checked + .slider:before { transform: translateX(18px); }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }

.participant-list { padding: 0 16px; display: flex; flex-direction: column; gap: 4px; }
.participant-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 8px; transition: background 0.2s; }
.participant-item:hover { background: rgba(255,255,255,0.05); }
.participant-avatar { width: 36px; height: 36px; border-radius: 50%; background: #3c4043; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.participant-name { flex: 1; color: #e8eaed; font-size: 14px; font-weight: 500; }
.participant-status { color: #9aa0a6; display: flex; }
.host-actions { display: flex; gap: 6px; }
.action-btn { width: 32px; height: 32px; border-radius: 50%; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.action-btn:hover:not(:disabled) { background: rgba(255,255,255,0.1); }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.remove-btn:hover { background: rgba(234, 67, 53, 0.15) !important; }

/* ========================
   WAITING ROOM OVERLAY
   ======================== */
.waiting-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(32, 33, 36, 0.7); /* Dark semi-transparent background */
  backdrop-filter: blur(12px); /* I-blur ang camera mo sa likod */
  z-index: 100; /* Takpan ang buong screen pati bottom bar */
  display: flex;
  align-items: center;
  justify-content: center;
}
.waiting-card {
  background: #fff;
  color: #202124;
  padding: 40px;
  border-radius: 12px;
  text-align: center;
  max-width: 400px;
  width: 90%;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 12px 40px rgba(0,0,0,0.4);
}
.spinner-large {
  width: 44px;
  height: 44px;
  border: 4px solid rgba(26, 115, 232, 0.2);
  border-top-color: #1a73e8;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 24px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.waiting-title {
  font-size: 24px;
  font-weight: 400;
  margin: 0 0 12px;
}
.waiting-desc {
  font-size: 14px;
  color: #5f6368;
  margin: 0;
  line-height: 1.5;
}
.rejected-icon {
  margin-bottom: 16px;
}
.btn-return {
  margin-top: 24px;
  background: #1a73e8;
  color: #fff;
  border: none;
  padding: 10px 24px;
  border-radius: 24px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-return:hover { background: #1558b0; }

.btn-cancel-wait {
  margin-top: 24px;
  background: transparent;
  color: #1a73e8;
  border: 1px solid #dadce0;
  padding: 10px 24px;
  border-radius: 24px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-cancel-wait:hover { background: #f8f9fa; }

/* ========================
   ADMIT GUEST MODAL
   ======================== */
.admit-dialog { position: absolute; top: 80px; right: 16px; width: 320px; background: #fff; border-radius: 12px; padding: 20px; z-index: 100; box-shadow: 0 8px 32px rgba(0,0,0,.4); }
.admit-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
.btn-admit { background: #1a73e8; color: #fff; border: none; padding: 10px 20px; border-radius: 24px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
.btn-admit:hover { background: #1558b0; }
.btn-deny { background: transparent; color: #1a73e8; border: none; padding: 10px 20px; border-radius: 24px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
.btn-deny:hover { background: #f1f3f4; }

@media (max-width: 768px) {
  /* 1. Ayusin ang Video Size (Ibalik sa full width ang screen) */
  .local-solo, .remote-fill, .screen-fill {
    top: 16px;
    left: 16px;
    right: 16px;
    bottom: 16px; /* Bigyan ng space yung controls sa ibaba */
  }

  /* 2. Kapag naka-open ang Chat, wag i-squish ang video sa mobile */
  .content-area.with-panel .local-solo,
  .content-area.with-panel .remote-fill,
  .content-area.with-panel .screen-fill {
    right: 16px; 
  }

  /* 3. Paliitin ang Picture-in-Picture (PiP) para hindi takpan ang mukha */
 .local-pip, .remote-pip {
    width: 130px; /* Linalakihan natin para hindi sobrang liit ng mukha mo */
    height: 180px; /* Dagdag: Set specific height para maging vertical */
    aspect-ratio: 9 / 16; /* Dagdag: I-force ang portrait aspect ratio */
    bottom: 120px; /* Inangat natin konti para malayo sa buttons */
    right: 16px;
    z-index: 100; /* Siguraduhing nasa ibabaw ng video */
    overflow: hidden; /* Siguraduhing pantay ang corners */
    cursor: grab; /* DAGDAG ITO: Lagyan ng hand cursor para alam na nadadrag */
  }

  .local-pip .tile-avatar-circle, .remote-pip .tile-avatar-circle {
    width: 70px;
    height: 70px;
  }

  .local-pip .tile-bottom-left, .remote-pip .tile-bottom-left {
    bottom: 4px;
    left: 4px;
    gap: 4px;
  }
  .local-pip .tile-name, .remote-pip .tile-name {
    font-size: 10px; /* Mas maliit na text */
    padding: 3px 6px;
    white-space: nowrap; /* Para hindi mag-two lines ang text */
  }

  /* 4. Gawing full-width ang Chat at People panel sa mobile */
  .side-panel {
    width: 100%;
    right: 0;
    top: 0;
    bottom: 0; 
    border-radius: 0; 
    z-index: 100; 
  }

  /* 5. Ayusin ang Bottom Bar Controls para magkasya lahat */
  .bottom-bar {
    padding: 0 8px;
    justify-content: center;
  }
  
  /* Itago muna ang oras at code sa mobile para iwas siksikan */
  .bottom-left {
    display: none; 
  }

  /* I-gitna nang maayos ang mic/cam buttons at medyo paliitin */
  .controls-center {
    position: relative;
    left: 0;
    transform: none;
    width: 100%;
    justify-content: center;
    gap: 8px;
  }
  .ctrl-btn {
    width: 42px;
    height: 42px;
  }
  .ctrl-btn.end-call {
    width: 48px;
    height: 48px;
  }

  /* I-angat yung Chat at People buttons sa itaas ng video para hindi sumiksik sa mic/cam controls */
  .bottom-right {
    display: none !important; 
  }

  /* Palitawin yung Chat at People buttons sa loob ng 3-dots menu */
  .mobile-only-menu {
    display: flex !important;
  }

  /* Tignan ang spinner para hindi mabanlag sa pag-rotate */
  svg.spinner {
    animation: spin 1s linear infinite;
    transform-origin: center;
  }
}

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
</style>