<template>
  <div class="room" @click="closeAll">
    <div class="top-bar">
      <Transition name="pop">
        <div v-if="handRaised" class="hand-top-badge">
          <span>✋</span>
          <span class="hand-badge-name">You</span>
        </div>
      </Transition>
    </div>

    <div class="content-area">
      <div class="video-wrap" :class="remoteClass">
        <video ref="remoteVideoEl" autoplay playsinline class="video-fill"></video>
        <div class="tile-top-right">
          <div v-if="remoteConnected" class="tile-icon-btn mute-badge">
             <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
          </div>
        </div>
        <div class="tile-bottom-left">
          <div v-if="remoteHandRaised" class="hand-indicator">✋</div>
          <div v-if="remoteConnected" class="tile-name">Guest</div>
        </div>
      </div>

      <div class="video-wrap" :class="screenClass">
        <video ref="screenVideoEl" autoplay playsinline class="video-fill"></video>
        <div class="screen-presenter-bar">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>
          You are presenting
        </div>
      </div>

      <div class="video-wrap" :class="localClass">
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
          <div class="tile-name">Rey Buban (You)</div>
        </div>
      </div>
    </div>

    <TransitionGroup name="float-emoji">
      <div v-for="e in floatingEmojis" :key="e.id" class="emoji-float" :style="{ left: e.x + '%' }">
        <span class="emoji-char">{{ e.emoji }}</span><span class="emoji-you-pill">You</span>
      </div>
    </TransitionGroup>

    <Transition name="fade"><div v-if="captionsOn && captionText" class="caption-bar">{{ captionText }}</div></Transition>
    <Transition name="pop"><div v-if="handRaised" class="hand-bottom-pill"><span>✋</span><span>You</span></div></Transition>

    <Transition name="pop">
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

    <Transition name="slide-panel">
      <div v-if="activePanel" class="side-panel" @click.stop>
        <template v-if="activePanel === 'chat'">
          <div class="panel-header">
            <h3>In-call messages</h3>
            <button class="panel-close" @click="activePanel = null"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
          </div>
          <div class="messages-list" ref="messagesListEl">
            <div v-if="!messages.length" class="no-messages"><p>No chat messages yet</p></div>
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
          <p class="people-count-text">{{ remoteConnected ? 2 : 1 }} in call</p>
        </template>
      </div>
    </Transition>

    <div v-if="peerError" class="error-banner">{{ peerError }}</div>

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
        <button class="ctrl-btn" :class="{ active: showEmojiPicker }" @click.stop="toggleEmojiPicker" title="Send a reaction">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
        </button>
        <button class="ctrl-btn" :class="{ active: captionsOn }" @click="toggleCaptions" title="Turn on captions">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V6h16v12zM6 10h2v2H6zm0 4h8v2H6zm10 0h2v2h-2zm-6-4h8v2h-8z"/></svg>
        </button>
        <div class="more-btn-wrap">
          <button class="ctrl-btn" :class="{ active: showMoreDropdown }" @click.stop="toggleMoreDropdown">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
          </button>
          <Transition name="pop">
            <div v-if="showMoreDropdown" class="more-dropdown" @click.stop>
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
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Peer from 'peerjs'
import axios from 'axios'
import { useLogger } from '../composables/useLogger'

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

const peer        = ref(null)
const currentCall = ref(null)
const dataConn    = ref(null)

const micOn         = ref(true)
const cameraOn      = ref(true)
const screenSharing = ref(false)
const captionsOn    = ref(false)
const handRaised    = ref(false)
const remoteHandRaised = ref(false)

const remoteConnected = ref(false)
const peerError       = ref('')
const isHost          = ref(false)

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

// --- RECORDING STATE ---
const isRecording = ref(false)
let mediaRecorder = null
let recordedChunks = []
let resolveUploadPromise = null // Ginagamit ito para hintayin ang upload bago mag-leave call

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

async function openDocumentPiP() {
  if (!window.documentPictureInPicture) return;
  if (pipWindow && !pipWindow.closed) return;
  showMoreDropdown.value = false;
  
  try {
    pipWindow = await window.documentPictureInPicture.requestWindow({ width: 420, height: 600 });
    pipWindow.addEventListener('pagehide', () => { pipWindow = null; pipInitialized = false; });
    renderCustomPiP();
  } catch (err) { console.error("PiP Error:", err); }
}

async function handleVisibilityChange() {
  if (document.hidden) {
    if (window.documentPictureInPicture && !pipWindow) {
      try { await openDocumentPiP(); } catch (e) {}
    }
  } else {
    if (pipWindow) {
      pipWindow.close();
      pipWindow = null;
      pipInitialized = false;
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
       <div id="main-name" class="name-plate">Rey Buban</div>
       
       <div id="local-pip" class="local-pip-overlay">
          <video id="local-video" autoplay playsinline muted></video>
          <div id="local-avatar" class="avatar-layer"><div class="avatar-circle">${SVG_AVATAR}</div></div>
          <div id="local-mute-badge" class="top-right-badges" style="top:4px; right:4px;"><div class="badge-mute" style="width:20px; height:20px;">${SVG_MUTE_BADGE}</div></div>
          
          <div id="local-hand" style="display: none; position: absolute; bottom: 24px; left: 4px; background: rgba(0,0,0,0.6); padding: 2px 6px; border-radius: 4px; font-size: 12px; z-index: 10;">✋</div>
          <div class="name-plate">You</div>
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
    mainName.textContent = 'Guest';
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
    mainName.textContent = 'Rey Buban (You)';
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

// --- RECORDING INITIALIZATION ---
async function startRecording() {
  if (!localStream) return;
  
  try {
    const displayStream = await navigator.mediaDevices.getDisplayMedia({
      video: { displaySurface: "browser" },
      audio: true
    });
    
    const tracks = [
      ...displayStream.getVideoTracks(),
      ...localStream.getAudioTracks()
    ];
    
    const combinedStream = new MediaStream(tracks);
    
    mediaRecorder = new MediaRecorder(combinedStream, { mimeType: 'video/webm' });
    
    mediaRecorder.ondataavailable = (event) => {
      if (event.data.size > 0) {
        recordedChunks.push(event.data);
      }
    };
    
    mediaRecorder.onstop = async () => {
      const blob = new Blob(recordedChunks, { type: 'video/webm' });
      recordedChunks = []; 
      
      displayStream.getTracks().forEach(track => track.stop());
      
      await uploadRecording(blob);

      // Kung may naghihintay na leaveCall, i-trigger natin para makalipat na
      if (resolveUploadPromise) {
        resolveUploadPromise();
      }
    };

    mediaRecorder.start(); // Kunin na lang sa iisang file para iwas putol
    isRecording.value = true;
    logAction('recording_started', { meeting_code: route.params.code }).catch(() => {});
    
  } catch (error) {
    console.error("Recording failed to start:", error);
  }
}

async function uploadRecording(blob) {
  const code = route.params.code;
  const formData = new FormData();
  
  // Pinalitan natin ng "audio" at "speaker" para pumasok sa Filament backend mo
  formData.append('audio', blob, `screen-record-${code}-${Date.now()}.webm`);
  formData.append('speaker', isHost.value ? 'Host' : 'Guest');
  formData.append('meeting_code', code);
  
  try {
    await axios.post(`${API_URL}/api/meetings/${code}/recordings`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    console.log("Recording uploaded successfully");
  } catch (err) {
    console.error("Failed to upload recording", err);
  }
}

onMounted(async () => {
  const code = route.params.code
  isHost.value   = sessionStorage.getItem('isHost') === 'true'
  micOn.value    = sessionStorage.getItem('micOn') !== 'false'
  cameraOn.value = sessionStorage.getItem('cameraOn') !== 'false'

  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
    originalVideoTrack = localStream.getVideoTracks()[0]
    applyTrackStates()
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
        currentCall.value = call
        call.answer(localStream)
        logAction('peerjs_call_received', { meeting_code: code })
        call.on('stream', (remote) => {
          remoteConnected.value = true
          if (remoteVideoEl.value) remoteVideoEl.value.srcObject = remote
        })
        call.on('close', () => { remoteConnected.value = false })
      })

      peer.value.on('connection', (conn) => {
        dataConn.value = conn
        conn.on('data', handleIncomingData)
      })
      
      startRecording();
      
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
      if (remoteVideoEl.value) remoteVideoEl.value.srcObject = remote
    })
    call.on('close', () => { remoteConnected.value = false })

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
    spawnEmoji(data.emoji)
  } else if (data.type === 'hand') {
    remoteHandRaised.value = data.raised
  }
}

function toggleMic() {
  micOn.value = !micOn.value
  localStream?.getAudioTracks().forEach(t => (t.enabled = micOn.value))
  logAction(micOn.value ? 'mic_unmuted' : 'mic_muted', { meeting_code: route.params.code })
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
         localVideoEl.value.srcObject = localStream;
      }
  }
  
  logAction(cameraOn.value ? 'camera_turned_on' : 'camera_turned_off', { meeting_code: route.params.code })
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

function toggleCaptions() { /* Placeholder */ }
function toggleHand() {
  handRaised.value = !handRaised.value
  if (dataConn.value?.open) dataConn.value.send({ type: 'hand', raised: handRaised.value })
  logAction(handRaised.value ? 'hand_raised' : 'hand_lowered', { meeting_code: route.params.code })
}

function toggleEmojiPicker() { showEmojiPicker.value = !showEmojiPicker.value; showMoreDropdown.value = false }
function spawnEmoji(emoji) {
  const id = Date.now() + Math.random()
  floatingEmojis.value.push({ id, emoji, x: 3 + Math.random() * 20 })
  setTimeout(() => { floatingEmojis.value = floatingEmojis.value.filter(e => e.id !== id) }, 3000)
}
function sendReaction(emoji) {
  spawnEmoji(emoji)
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
  
  // Update UI immediately
  messages.value.push({ id: Date.now(), sender: 'You', text, time, isOwn: true })
  
  // SEND TO BACKEND: Sync chat to database
  const code = route.params.code;
  axios.post(`${API_URL}/api/meetings/${code}/chats`, {
    sender: isHost.value ? 'Host' : 'Guest',
    message: text,
    time: time // <-- DAGDAG: Isinama na natin yung time para tanggapin ng Laravel mo!
  }).catch(err => console.error("Chat sync failed", err));

  // Send to peer
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
  // Hintayin munang matapos ang upload bago lumipat kung may nagre-record
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    isRecording.value = false;
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
  ;['isHost', 'meetingCode', 'micOn', 'cameraOn'].forEach(k => sessionStorage.removeItem(k))
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
      import('vue').then(({ nextTick }) => {
        nextTick(() => {
          if (settingsVidPreview.value && localStream) {
            settingsVidPreview.value.srcObject = localStream;
          }
        });
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
</script>
<style scoped>
/* .. Yung existing styles mo same pa rin ..*/
.room { width: 100vw; height: 100vh; background: #202124; display: flex; flex-direction: column; position: relative; overflow: hidden; font-family: 'Google Sans', Roboto, Arial, sans-serif; color: #e8eaed; }
.top-bar { position: absolute; top: 12px; right: 16px; display: flex; align-items: center; gap: 10px; z-index: 20; }
.hand-top-badge { display: flex; align-items: center; gap: 6px; background: #34a853; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 500; }
/* Tinanggal na yung .avatar-circle dito */
.content-area { flex: 1; position: relative; overflow: hidden; }
.video-wrap { position: absolute; border-radius: 12px; overflow: hidden; background: #202124; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); box-sizing: border-box; }
.wrap-hidden { display: none; }
.local-solo { top: 50%; left: 50%; transform: translate(-50%, -50%); width: min(75vw, 960px); aspect-ratio: 16/9; z-index: 5; box-shadow: 0 8px 24px rgba(0,0,0,.4); }
.remote-fill { inset: 12px; border-radius: 12px; z-index: 4; }
.local-pip { bottom: 24px; right: 24px; width: 280px; aspect-ratio: 16/9; z-index: 12; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.3), 0 4px 8px 3px rgba(0,0,0,0.15); }
.screen-fill { inset: 0; border-radius: 0; z-index: 3; background: #000; }
.screen-fill .video-fill { object-fit: contain; }
.remote-pip { bottom: 24px; right: 24px; width: 280px; aspect-ratio: 16/9; z-index: 11; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.3), 0 4px 8px 3px rgba(0,0,0,0.15); }
.local-pip-cam { bottom: 195px; right: 24px; }
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
.side-panel { position: absolute; top: 0; right: 0; width: 360px; height: 100%; background: #202124; z-index: 25; display: flex; flex-direction: column; box-shadow: -4px 0 16px rgba(0,0,0,.4); border-left: 1px solid #3c4043; }
.panel-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid #3c4043; flex-shrink: 0; }
.panel-header h3 { color: #e8eaed; font-size: 16px; font-weight: 500; margin: 0; }
.panel-close { background: none; border: none; color: #9aa0a6; cursor: pointer; padding: 4px; border-radius: 50%; display: flex; transition: background .2s; }
.panel-close:hover { background: #3c4043; }
.messages-list { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; scrollbar-width: thin; scrollbar-color: #3c4043 transparent; }
.no-messages { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: #5f6368; font-size: 14px; }
.message-item { display: flex; flex-direction: column; gap: 3px; }
.msg-meta { display: flex; align-items: baseline; gap: 8px; }
.msg-sender { color: #e8eaed; font-size: 13px; font-weight: 500; }
.msg-time { color: #5f6368; font-size: 11px; }
.msg-text { color: #bdc1c6; font-size: 14px; margin: 0; line-height: 1.5; }
.message-item.own .msg-sender { color: #8ab4f8; }
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
.slide-panel-enter-active, .slide-panel-leave-active { transition: transform .25s ease; }
.slide-panel-enter-from, .slide-panel-leave-to { transform: translateX(100%); }
.slide-up-enter-active, .slide-up-leave-active { transition: all .3s ease; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px); }
.pop-enter-active, .pop-leave-active { transition: all .15s ease; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(.88); }

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
</style>