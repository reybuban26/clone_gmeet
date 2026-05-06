<template>
  <div class="room" @click="closeAll" @mousemove="onDragMove" @touchmove.passive="onDragMove" @mouseup="onDragEnd" @touchend="onDragEnd">

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

    <div class="content-area grid-container" :class="{ 'with-emoji': showEmojiPicker, 'with-panel': activePanel, 'has-screen-share': screenSharing || remoteScreenSharing, ['mode-' + layoutMode]: true }">
      <div v-show="screenSharing || remoteScreenSharing" class="screen-share-wrap">
        <video ref="screenVideoEl" autoplay playsinline muted class="video-fill" :class="{ 'is-remote-screen': remoteScreenSharing }"></video>
        <div v-if="screenSharing" class="screen-presenter-bar">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>
          You are presenting
        </div>
      </div>

      <div class="participants-grid" :class="['layout-' + layoutMode]">
        
        <div class="video-wrap grid-item local-camera" :class="{ 'speaking-border': isLocalSpeaking }" ref="pipElRef" @mousedown="onDragStart" @touchstart.passive="onDragStart">
          <video ref="localVideoEl" autoplay muted playsinline class="video-fill mirrored" :class="{ 'vid-hidden': !cameraOn }"></video>
          
          <div v-if="!cameraOn" class="tile-avatar">
            <div class="tile-avatar-circle">
              <svg viewBox="0 0 24 24" width="50%" height="50%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </div>
          </div>
          
          <div class="tile-top-right">
            <div class="tile-icon-btn" :title="'Network: ' + networkQuality">
              <svg viewBox="0 0 24 24" width="16" height="16" :fill="networkQuality === 'poor' ? '#ea4335' : (networkQuality === 'fair' ? '#fbbc04' : '#fff')">
                <path d="M12.01 21.49L23.64 7c-.45-.34-4.93-4-11.64-4C5.28 3 .81 6.66.36 7l11.63 14.49.01.01.01-.01z"/>
              </svg>
            </div>

            <div v-if="!micOn" class="tile-icon-btn mute-badge urgent">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
            </div>
            <div v-else-if="isLocalSpeaking" class="tile-icon-btn speaking-badge">
              <div class="mini-wave">
                <span class="mini-bar"></span>
                <span class="mini-bar"></span>
                <span class="mini-bar"></span>
              </div>
            </div>
          </div>
          
          <div class="tile-bottom-left">
            <div v-if="handRaised" class="hand-indicator">✋</div>
            <div class="tile-name">{{ isHost ? 'Host (You)' : 'Guest (You)' }}</div>
          </div>
        </div>

        <div v-for="peer in sortedParticipants" :key="peer.peerId" class="video-wrap grid-item remote-camera" :class="{ 'speaking-border': peer.isSpeaking }">
          <video :id="'vid-' + peer.peerId" autoplay playsinline class="video-fill" :class="{ 'vid-hidden': !peer.cameraOn }"></video>
          
          <div v-if="!peer.cameraOn" class="tile-avatar">
            <div class="tile-avatar-circle" :style="{ backgroundColor: peer.avatarColor }">
              <svg viewBox="0 0 24 24" width="50%" height="50%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </div>
          </div>
          
          <div class="tile-top-right">
            <div v-if="!peer.micOn" class="tile-icon-btn mute-badge">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
            </div>
            <div v-else-if="peer.isSpeaking" class="tile-icon-btn speaking-badge">
              <div class="mini-wave">
                <span class="mini-bar"></span>
                <span class="mini-bar"></span>
                <span class="mini-bar"></span>
              </div>
            </div>
          </div>
          
          <div class="tile-bottom-left">
            <div v-if="peer.handRaised" class="hand-indicator">✋</div>
            <div class="tile-name">{{ peer.name || 'Guest' }}</div>
          </div>
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

    

    <Transition name="slide-panel">
      <div v-if="activePanel" class="side-panel" @click.stop>
        <template v-if="activePanel === 'host'">
          <div class="panel-header">
            <h3>Host controls</h3>
            <button class="panel-close" @click="activePanel = null">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
          </div>
          <div class="host-panel-body">
            <div class="host-info-card">
              Use these host settings to keep control of your meeting. Only hosts have access to these controls.
            </div>

            <div class="host-controls-wrapper">
              <div class="host-controls-header">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                Meeting safety
              </div>
              
              <div class="host-control-item">
                <div class="control-text">
                  <strong>Lock meeting</strong>
                  <small>Prevent new guests from joining the call</small>
                </div>
                <label class="switch">
                  <input type="checkbox" v-model="isLocked">
                  <span class="slider round"></span>
                </label>
              </div>

              <div class="host-control-item" style="border-top: 1px solid rgba(26, 115, 232, 0.3);">
                <div class="control-text">
                  <strong>Send chat messages</strong>
                  <small>Let everyone send messages in the chat</small>
                </div>
                <label class="switch">
                  <input type="checkbox" v-model="isChatEnabled">
                  <span class="slider round"></span>
                </label>
              </div>

              <div class="host-control-item" style="border-top: 1px solid rgba(26, 115, 232, 0.3);">
                <div class="control-text">
                  <strong>Screen sharing</strong>
                  <small>Let everyone share their screen</small>
                </div>
                <label class="switch">
                  <input type="checkbox" v-model="isShareScreenEnabled">
                  <span class="slider round"></span>
                </label>
              </div>

              <div class="host-control-item" style="border-top: 1px solid rgba(26, 115, 232, 0.3);">
                <div class="control-text">
                  <strong>Turn on their microphone</strong>
                  <small>Let everyone use their mic</small>
                </div>
                <label class="switch">
                  <input type="checkbox" v-model="isMicEnabled">
                  <span class="slider round"></span>
                </label>
              </div>

              <div class="host-control-item" style="border-top: 1px solid rgba(26, 115, 232, 0.3);">
                <div class="control-text">
                  <strong>Turn on their video</strong>
                  <small>Let everyone use their camera</small>
                </div>
                <label class="switch">
                  <input type="checkbox" v-model="isCameraEnabled">
                  <span class="slider round"></span>
                </label>
              </div>

            </div>
          </div>
        </template>

        <template v-if="activePanel === 'info'">
          <div class="panel-header">
            <h3>Meeting details</h3>
            <button class="panel-close" @click="activePanel = null">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </button>
          </div>
          <div class="info-panel-body">
            <h4 class="info-section-title">Joining info</h4>
            <div class="info-link-text">{{ meetingLink }}</div>
            <button class="btn-copy-info" @click="copyLink">
              <svg v-if="!copied" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
              <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="#1a73e8"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
              {{ copied ? 'Copied meeting link' : 'Copy joining info' }}
            </button>
            <div class="info-divider"></div>
          </div>
        </template>

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
              <p v-if="msg.text" class="msg-text" v-html="formatMessage(msg.text)"></p>
              <div v-if="msg.file" class="msg-file-attachment">
                <img v-if="msg.fileType.startsWith('image/')" :src="msg.fileData" class="chat-image" @click="openImage(msg.fileData)" />
                <a v-else :href="msg.fileData" :download="msg.fileName" class="chat-file-link">
                   <span class="file-icon-wrapper" v-html="getFileIconSVG(msg.fileName)"></span>
                   <span class="file-name-text">{{ msg.fileName }}</span>
                </a>
              </div>
            </div>
          </div>
          <div class="chat-input-row">
            <input type="file" ref="fileInputEl" @change="handleFileUpload" style="display: none;" accept="image/*,.pdf,.doc,.docx,.txt" />
            
            <button class="chat-attach" @click="$refs.fileInputEl.click()" title="Attach file" :disabled="!isHost && !isChatEnabled">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="#9aa0a6"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5a2.5 2.5 0 0 1 5 0v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5a2.5 2.5 0 0 0 5 0V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/></svg>
            </button>
            
            <input 
              v-model="messageInput" 
              class="chat-input" 
              :placeholder="(!isHost && !isChatEnabled) ? 'Chat disabled by host' : 'Send a message'" 
              @keyup.enter="sendMessage" 
              :disabled="!isHost && !isChatEnabled" 
            />
            
            <button class="chat-send" @click="sendMessage" :disabled="(!isHost && !isChatEnabled) || !messageInput.trim()">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
          </div>
        </template>

        <template v-if="activePanel === 'people'">
          <div class="panel-header">
            <h3>People</h3>
            <button class="panel-close" @click="activePanel = null"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
          </div>
          
          <div class="people-panel-body">
            <div class="people-search">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="#9aa0a6"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 12.01 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 12.01 14 9.5 14z"/></svg>
              <input type="text" placeholder="Search for people" />
            </div>
            <div v-if="waitingGuests.length > 0 && isHost" class="people-group-section">
              <div class="pg-header">
                <span>Waiting to be admitted</span>
                <span>{{ waitingGuests.length }}</span>
              </div>
              <div class="pg-actions-top">
                <button class="btn-text-blue" @click="admitAllGuests">Admit all</button>
              </div>
              <div class="participant-item" v-for="guest in waitingGuests" :key="guest.id">
                <div class="participant-avatar"><svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>
                <div class="participant-name">{{ guest.name }}</div>
                <div class="waiting-actions">
                  <button class="btn-text-blue" @click="admitGuest(guest.id)">Admit</button>
                  <div class="menu-wrap">
                    <button class="action-btn" @click="showWaitingOptions = showWaitingOptions === guest.id ? null : guest.id">
                      <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    </button>
                    <div class="context-dropdown" v-if="showWaitingOptions === guest.id">
                      <button @click="denyGuest(guest.id)">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/></svg>
                        Deny
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="people-group-section">
              <div class="pg-header">
                <span>Contributors</span>
                <span>{{ participants.length + 1 }}</span>
              </div>
              
              <div class="participant-item">
                <div class="participant-avatar host-avatar">
                   <svg viewBox="0 0 24 24" width="60%" height="60%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                </div>
                <div class="participant-name">
                  <span>You</span>
                  <span class="host-label">Meeting host</span>
                </div>
                <div class="waiting-actions">
                  <div class="participant-status">
                    <div class="people-mic-indicator" :class="{ 'speaking': micOn && isLocalSpeaking, 'muted': !micOn }">
                        <div v-if="micOn" class="side-wave">
                            <span class="s-bar"></span><span class="s-bar"></span><span class="s-bar"></span>
                        </div>
                        <div v-else class="muted-icon-wrapper">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
                        </div>
                    </div>
                  </div>

                  <div class="menu-wrap">
                    <button class="action-btn" @click="showPinMenu = !showPinMenu" v-html="SVG_3DOTS"></button>
                    <div class="context-dropdown pin-dropdown" v-if="showPinMenu">
                      
                      <div class="submenu-container">
                        <button class="submenu-trigger">
                           <div style="display: flex; align-items: center; gap: 12px;">
                             <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                             <span>Pin to the screen</span>
                           </div>
                           <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" class="arrow-right"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
                        </button>
                        
                        <div class="submenu-popup">
                          <button>For myself only</button>
                          <button>For everyone</button>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <div v-for="p in participants" :key="p.peerId" class="participant-item">
                <div class="participant-avatar guest-avatar" :style="{ backgroundColor: p.avatarColor }">
                   <svg viewBox="0 0 24 24" width="60%" height="60%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                </div>
                <div class="participant-name">{{ p.name }}</div>
                
                <div class="waiting-actions">
                  <div class="participant-status">
                    <div class="people-mic-indicator" :class="{ 'speaking': p.micOn && p.isSpeaking, 'muted': !p.micOn }">
                        <div v-if="p.micOn" class="side-wave">
                            <span class="s-bar"></span><span class="s-bar"></span><span class="s-bar"></span>
                        </div>
                        <div v-else class="muted-icon-wrapper">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
                        </div>
                    </div>
                  </div>

                  <div v-if="isHost" class="host-actions">
                    <button class="action-btn" @click="forceMuteGuest" :disabled="!p.micOn" title="Mute participant">
                      <svg viewBox="0 0 24 24" width="18" height="18" :fill="p.micOn ? '#e8eaed' : '#5f6368'"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                    </button>
                    <button class="action-btn remove-btn" @click="kickGuest" title="Remove from call">
                      <svg viewBox="0 0 24 24" width="18" height="18" fill="#ea4335"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <template v-if="activePanel === 'activities'">
          <div class="panel-header">
            <h3>Activities</h3>
            <button class="panel-close" @click="activePanel = null"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
          </div>
          
          <div class="activities-panel-body">
            
            <div v-if="!isCreatingPoll" class="polls-list-view">
              <div class="act-header-row" v-if="isHost">
                <div class="act-title"><span v-html="SVG_ACTIVITIES"></span> Polls</div>
                <button class="btn-start-poll" @click="isCreatingPoll = true">Start a poll</button>
              </div>
              
              <div v-if="polls.length === 0" class="no-activities">
                 <img src="/empty-chat.png" alt="No polls" class="empty-chat-img" style="opacity: 0.5;" />
                 <p>No active polls yet</p>
              </div>
              
              <div v-for="poll in polls" :key="poll.id" class="poll-card">
                <div class="poll-header-row">
                  <div class="poll-q">{{ poll.question }}</div>
                  <span v-if="poll.isEnded" class="poll-ended-badge">Ended</span>
                </div>
                
                <div class="poll-opts">
                  <div v-for="(opt, idx) in poll.options" :key="idx" class="poll-opt" 
                       @click="!poll.isEnded && votePoll(poll.id, idx)" 
                       :class="{ 'voted': poll.votedIndex === idx, 'disabled': poll.votedIndex !== null || poll.isEnded }">
                    <div class="opt-text-row">
                      <div class="opt-radio">
                         <div v-if="poll.votedIndex === idx" class="radio-filled"></div>
                      </div>
                      <span class="opt-label">{{ opt.text }}</span>
                      <span class="opt-votes" v-if="poll.votedIndex !== null || isHost || poll.isEnded">{{ opt.votes }} vote(s)</span>
                    </div>
                    <div class="opt-progress-bg" v-if="poll.votedIndex !== null || isHost || poll.isEnded">
                      <div class="opt-progress-bar" :style="{ width: poll.totalVotes ? (opt.votes / poll.totalVotes * 100) + '%' : '0%' }"></div>
                    </div>
                  </div>
                </div>

                <div class="poll-footer">
                  <div class="poll-meta" v-if="poll.votedIndex !== null || poll.isEnded || isHost">{{ poll.totalVotes }} total vote(s)</div>
                  
                  <div v-if="isHost" class="poll-host-actions">
                    <button v-if="!poll.isEnded" class="btn-text-blue" @click="endPoll(poll.id)">End poll</button>
                    <button class="btn-icon-trash" @click="deletePoll(poll.id)" title="Delete poll">
                      <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-3.5l-1-1zM18 7H6v12c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7z"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="poll-create-view">
               <div class="create-header">
                 <button class="btn-back" @click="isCreatingPoll = false"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg></button>
                 <h4>Create a poll</h4>
               </div>
               
               <input type="text" class="poll-input main-q" placeholder="Ask a question..." v-model="newPollQuestion" />
               
               <div class="poll-opts-edit">
                 <div v-for="(opt, idx) in newPollOptions" :key="idx" class="opt-edit-row">
                   <input type="text" class="poll-input" :placeholder="'Option ' + (idx + 1)" v-model="newPollOptions[idx]" />
                   <button v-if="newPollOptions.length > 2" class="btn-remove-opt" @click="removePollOption(idx)">
                     <svg viewBox="0 0 24 24" width="16" height="16" fill="#9aa0a6"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                   </button>
                 </div>
                 <button class="btn-add-opt" @click="addPollOption">+ Add option</button>
               </div>
               
               <div class="poll-actions">
                 <button class="btn-launch" @click="launchPoll" :disabled="!newPollQuestion.trim() || newPollOptions.some(o=>!o.trim())">Launch</button>
               </div>
            </div>

          </div>
        </template>

        <template v-if="activePanel === 'effects'">
          <div class="panel-header">
            <h3>Effects</h3>
            <button class="panel-close" @click="activePanel = null"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>
          </div>
          <div class="effects-preview-box">
             <video ref="effectsPreviewEl" autoplay playsinline muted class="mini-preview-vid mirrored"></video>
             <div v-if="isAiLoading" class="preview-loading">Applying...</div>
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
                <button class="effect-btn blur-btn" :class="{ active: activeEffect === 'blur' || activeEffect === 'blur-heavy' }" @click="setVideoEffect('blur-heavy')">
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
            <p v-if="rejectedReason === 'locked'" class="waiting-desc">The host has locked the meeting and is not accepting new guests.</p>
            <p v-else class="waiting-desc">The host denied your request to join the meeting.</p>
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
        <button 
          class="ctrl-btn mic-btn" 
          :class="{ 'off': !micOn, 'is-on': micOn, 'is-speaking': isLocalSpeaking }" 
          @click="toggleMic" 
          :title="(!isHost && !isMicEnabled) ? 'Microphone disabled by host' : (micOn ? 'Turn off microphone' : 'Turn on microphone')"
          :disabled="!isHost && !isMicEnabled"
        >
          
          <div v-if="micOn" class="mic-wave">
            <span class="wave-bar"></span>
            <span class="wave-bar"></span>
            <span class="wave-bar"></span>
          </div>

          <div class="mic-icon-circle">
            <svg v-if="micOn" viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M19 11h-1.7c0 .74-.16 1.43-.43 2.05l1.23 1.23c.56-.98.9-2.09.9-3.28zm-4.02.17c0-.06.02-.11.02-.17V5c0-1.66-1.34-3-3-3S9 3.34 9 5v.18l5.98 5.99zM4.27 3L3 4.27l6.01 6.01V11c0 1.66 1.33 3 2.99 3 .22 0 .44-.03.65-.08l1.66 1.66c-.71.33-1.5.52-2.31.52-2.76 0-5.3-2.1-5.3-5.1H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c.91-.13 1.77-.45 2.54-.9L19.73 21 21 19.73 4.27 3z"/></svg>
          </div>
          
        </button>

        <div class="ctrl-group" :class="{ off: !cameraOn }">
          <button 
            class="ctrl-group-main" 
            @click="toggleCamera" 
            :title="(!isHost && !isCameraEnabled) ? 'Camera disabled by host' : (cameraOn ? 'Turn off camera' : 'Turn on camera')"
            :disabled="!isHost && !isCameraEnabled"
          >
            <svg v-if="cameraOn" viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
            <svg v-else viewBox="0 0 24 24" width="20" height="20"><path fill="white" d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/><line x1="2" y1="2" x2="22" y2="22" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>
          </button>
          <button class="ctrl-group-arrow" @click="showCamOptions = !showCamOptions; showMoreDropdown = false; showEmojiPicker = false">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="white"><path d="M7 10l5 5 5-5z"/></svg>
          </button>

          <Transition name="pop-center">
            <div v-if="showCamOptions" class="cam-mini-menu" @click.stop>
              <div class="menu-cam-title">Available cameras</div>
              <div class="menu-cam-item active-cam">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="#8ab4f8"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                HD Web Camera
              </div>
              <div class="menu-divider"></div>
              <button class="menu-action-btn" @click="togglePanel('effects'); showCamOptions = false">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="#e8eaed"><path d="M5.99988 16.938V19.059L5.05851 20H2.93851L5.99988 16.938ZM22.0015 14.435V16.557L18.5595 20H17.9935L17.9939 18.443L22.0015 14.435ZM8.74988 14H15.2446C16.1628 14 16.9158 14.7071 16.9888 15.6065L16.9946 15.75V20H15.4946V15.75C15.4946 15.6317 15.4124 15.5325 15.3019 15.5066L15.2446 15.5H8.74988C8.63154 15.5 8.5324 15.5822 8.50648 15.6927L8.49988 15.75V20H6.99988V15.75C6.99988 14.8318 7.70699 14.0788 8.60636 14.0058L8.74988 14ZM8.02118 10.4158C8.08088 10.9945 8.26398 11.5367 8.54372 12.0154L1.99951 18.56V16.438L8.02118 10.4158ZM22.0015 9.932V12.055L17.9939 16.065L17.9946 15.75L17.9896 15.5825C17.9623 15.128 17.8246 14.7033 17.6029 14.3348L22.0015 9.932ZM12.0565 4L1.99951 14.06V11.939L9.93551 4H12.0565ZM22.0015 5.432V7.555L16.3346 13.2245C16.0672 13.1089 15.7779 13.0346 15.4746 13.0095L15.2446 13L14.6456 13.0005C14.9874 12.6989 15.2772 12.3398 15.4999 11.9384L22.0015 5.432ZM11.9999 7.00046C13.6567 7.00046 14.9999 8.34361 14.9999 10.0005C14.9999 11.6573 13.6567 13.0005 11.9999 13.0005C10.343 13.0005 8.99988 11.6573 8.99988 10.0005C8.99988 8.34361 10.343 7.00046 11.9999 7.00046ZM11.9999 8.50046C11.1715 8.50046 10.4999 9.17203 10.4999 10.0005C10.4999 10.8289 11.1715 11.5005 11.9999 11.5005C12.8283 11.5005 13.4999 10.8289 13.4999 10.0005C13.4999 9.17203 12.8283 8.50046 11.9999 8.50046ZM7.55851 4L1.99951 9.56V7.438L5.43751 4H7.55851ZM21.0565 4L15.9091 9.14895C15.7923 8.61022 15.5669 8.11194 15.2571 7.67816L18.9345 4H21.0565ZM16.5585 4L14.0148 6.54427C13.5362 6.26463 12.9942 6.08157 12.4157 6.02181L14.4365 4H16.5585Z" /></svg>
                Apply visual effects
              </button>
            </div>
          </Transition>
        </div>

        <button 
          class="ctrl-btn" 
          :class="{ active: screenSharing }" 
          @click="toggleScreenShare" 
          :disabled="!isHost && !isShareScreenEnabled"
          :title="(!isHost && !isShareScreenEnabled) ? 'Screen sharing disabled by host' : 'Present now'"
        >
          <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6zm8 9l-4-4h3V8h2v3h3l-4 4z"/></svg>
        </button>

        <button class="ctrl-btn" :class="{ active: showEmojiPicker }" @click.stop="toggleEmojiPicker" title="Send a reaction">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
        </button>

        <button class="ctrl-btn" :class="{ active: handRaised }" @click.stop="toggleHand" :title="handRaised ? 'Lower hand' : 'Raise hand'">
          <svg viewBox="0 0 128 128" width="22" height="22" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet"><radialGradient id="IconifyId17ecdb2904d178eab19847" cx="57.16" cy="33.264" r="89.842" gradientUnits="userSpaceOnUse"><stop stop-color="#FFCA28" offset=".353"></stop><stop stop-color="#FFB300" offset=".872"></stop></radialGradient><path d="M50.9 122.3c-23.88 0-30.28-12.73-31.45-23.41c-.55-5.04-1.27-61.23-1.27-61.8c0-2.78.71-7.48 5.46-7.48c4.46 0 6.46 3.77 6.46 7.5l.46 23.24c.02.82.68 1.65 1.5 1.65s1.48-.84 1.5-1.66l.59-38.24c0-3.76 2-7.57 6.46-7.57s6.46 3.74 6.46 7.48l.48 37.92c.01.82.68 1.47 1.5 1.47s1.49-.66 1.5-1.48l.57-46.71c0-3.75 2-7.52 6.46-7.52s6.46 3.77 6.46 7.5l.59 46.46a1.5 1.5 0 0 0 3 0L68.1 25c0-3.75 2-7.52 6.46-7.52s6.46 3.77 6.46 7.5c.11 12.42.38 44.85.34 46.01c-.08.92.26 1.52.56 1.85c.4.44.98.69 1.63.69c1.44 0 2.87-1.14 3.27-1.49c1.78-1.54 3.31-3.95 4.93-6.5c2.02-3.18 4.12-6.48 6.36-7.39c1.76-.71 3.89-1.11 6.01-1.11c3.4 0 5.53 1 5.89 1.98c1.56 4.3-.32 5.95-5.09 9.6l-.92.71c-4.73 3.65-7.8 11.08-10.52 17.63c-1.11 2.67-2.15 5.2-3.23 7.32c-.65 1.28-1.21 2.79-1.86 4.53c-3.46 9.36-8.72 23.49-37.49 23.49z" fill="url(#IconifyId17ecdb2904d178eab19847)"></path><path d="M57.59 7.2c2.29 0 4.96 1.57 4.96 6.04l.59 46.62c.02 1.64 1.36 3.14 3 3.14s2.98-1.5 3-3.14l.47-34.78c0-2.77 1.3-6.04 4.96-6.04s4.96 3.21 4.96 6c.15 17.6.37 44.16.34 45.88c-.1 1.46.46 2.4.95 2.95c.69.76 1.67 1.17 2.74 1.17c1.9 0 3.61-1.3 4.25-1.86c1.94-1.68 3.53-4.18 5.21-6.83c1.81-2.85 3.86-6.07 5.66-6.8c1.56-.63 3.55-1 5.45-1c2.78 0 4.24.74 4.5 1.04c1.12 3.12.24 4.14-4.61 7.84l-.92.71c-5.05 3.89-8.2 11.52-10.99 18.25c-1.09 2.65-2.13 5.14-3.18 7.21c-.69 1.36-1.3 2.98-1.93 4.69c-1.68 4.51-3.77 10.13-8.78 14.57c-6.02 5.35-14.96 7.94-27.33 7.94c-12.57 0-27.96-3.83-29.96-22.08c-.45-4.12-1.07-45.9-1.26-61.62c0-3.98 1.53-6 4.16-6c2.29 0 5.16 1.57 5.16 6v.06l.26 22.96c.03 1.63 1.17 2.87 2.8 2.87h.01c1.64 0 2.97-1.25 2.99-2.88l.59-38.16c0-2.77 1.3-5.98 4.96-5.98s4.96 3.24 4.96 6.05l.45 37.91c.04 1.65.99 2.97 2.99 2.97s3.01-1.32 3.03-2.96l.59-46.74c0-4.43 2.68-6 4.97-6m0-3c-4.95 0-7.96 4.03-7.96 9l-.57 46.7l-.48-37.91c0-4.97-3.01-9-7.96-9s-7.96 4.03-7.96 9l-.59 38.15l-.47-23.03c0-4.97-3.02-9-7.96-9c-4.95 0-6.96 4.03-6.96 9c0 0 .72 56.78 1.28 61.94c.93 8.5 5.74 24.75 32.94 24.75c35.1 0 36.64-20.86 40.71-28.84c3.79-7.43 7.09-19.64 13.33-24.45c4.96-3.83 8.62-6.12 6.5-12c-.71-1.96-3.81-2.97-7.3-2.97c-2.2 0-4.56.4-6.57 1.22c-4.6 1.87-7.84 10.79-11.7 14.14c-.72.62-1.66 1.13-2.29 1.13c-.46 0-.76-.27-.7-.95c.06-.69-.34-46.09-.34-46.09c0-4.97-3.01-9-7.96-9s-7.96 4.03-7.96 9l-.47 34.65l-.6-46.44c0-4.98-3.01-9-7.96-9z" fill="#EDA600"></path><defs><path id="IconifyId17ecdb2904d178eab19848" d="M107.92 57.8c-2.27-2.53-8.01-3.72-13.54-1.54c-4.65 1.83-9.96 19.19-9.96 19.19l-3.87-6.4s-65.53 21.5-64.6 30s7.74 24.75 34.94 24.75c35.1 0 36.64-20.86 40.71-28.84c3.79-7.43 8.56-24.71 14.42-26.55c3.8-1.18 3.82-8.48 1.9-10.61z"></path></defs><clipPath id="IconifyId17ecdb2904d178eab19849"><use xlink:href="#IconifyId17ecdb2904d178eab19848"></use></clipPath><g clip-path="url(#IconifyId17ecdb2904d178eab19849)"><path d="M83.91 69.48C73 73.64 66.57 83.91 62.62 97.38c-.54 1.86 1.17 2.4 1.83.58c6.86-18.88 23.87-25.11 23.87-25.11l-4.41-3.37z" fill="#EDA600"></path></g></svg>
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
              <button class="dropdown-item mobile-only-menu" @click="togglePanel('activities'); showMoreDropdown = false">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M14 5C14 4.44772 14.4477 4 15 4H19C19.5523 4 20 4.44772 20 5V9C20 9.55228 19.5523 10 19 10C18.4477 10 18 9.55228 18 9V6H15C14.4477 6 14 5.55228 14 5ZM4.29289 13.2929C4.68342 12.9024 5.31658 12.9024 5.70711 13.2929L7.5 15.0858L9.29289 13.2929C9.68342 12.9024 10.3166 12.9024 10.7071 13.2929C11.0976 13.6834 11.0976 14.3166 10.7071 14.7071L8.91421 16.5L10.7071 18.2929C11.0976 18.6834 11.0976 19.3166 10.7071 19.7071C10.3166 20.0976 9.68342 20.0976 9.29289 19.7071L7.5 17.9142L5.70711 19.7071C5.31658 20.0976 4.68342 20.0976 4.29289 19.7071C3.90237 19.3166 3.90237 18.6834 4.29289 18.2929L6.08579 16.5L4.29289 14.7071C3.90237 14.3166 3.90237 13.6834 4.29289 13.2929ZM6 7.5C6 6.67157 6.67157 6 7.5 6C8.32843 6 9 6.67157 9 7.5C9 8.32843 8.32843 9 7.5 9C6.67157 9 6 8.32843 6 7.5ZM7.5 4C5.567 4 4 5.567 4 7.5C4 9.433 5.567 11 7.5 11C9.433 11 11 9.433 11 7.5C11 5.567 9.433 4 7.5 4ZM16.5 15C15.6716 15 15 15.6716 15 16.5C15 17.3284 15.6716 18 16.5 18C17.3284 18 18 17.3284 18 16.5C18 15.6716 17.3284 15 16.5 15ZM13 16.5C13 14.567 14.567 13 16.5 13C18.433 13 20 14.567 20 16.5C20 18.433 18.433 20 16.5 20C14.567 20 13 18.433 13 16.5ZM14 11C14.5523 11 15 10.5523 15 10C15 9.44772 14.5523 9 14 9C13.4477 9 13 9.44772 13 10C13 10.5523 13.4477 11 14 11ZM13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12ZM16 9C16.5523 9 17 8.55228 17 8C17 7.44772 16.5523 7 16 7C15.4477 7 15 7.44772 15 8C15 8.55228 15.4477 9 16 9Z"></path></svg>
                Activities
                <span v-if="unreadActivitiesCount > 0" class="util-badge-menu">{{ unreadActivitiesCount }}</span>
              </button>
              <button v-if="isHost" class="dropdown-item mobile-only-menu" @click="togglePanel('host'); showMoreDropdown = false">
                <span v-html="SVG_HOST" style="width: 18px; height: 18px; display: inline-block;"></span>
                Host controls
              </button>
              <button class="dropdown-item mobile-only-menu" @click="togglePanel('info'); showMoreDropdown = false">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                Meeting details
              </button>
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
        
        <button class="util-btn" :class="{ active: activePanel === 'info' }" @click.stop="togglePanel('info')" title="Meeting details">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
        </button>

        <button v-if="isHost" class="util-btn" :class="{ active: activePanel === 'host' }" @click.stop="togglePanel('host')" title="Host controls" v-html="SVG_HOST" style="width: 20px; height: 20px; padding: 10px; box-sizing: content-box;"></button>
        
        <div class="people-group-wrapper" @mouseleave="showPeopleHover = false; showAdmitHover = false">
          
          <Transition name="fade">
            <div v-if="waitingGuests.length > 0 && isHost" class="admit-pill-wrapper" @mouseenter="showAdmitHover = true; showPeopleHover = false">
              <button class="admit-green-pill" @click.stop="togglePanel('people')">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                Admit {{ waitingGuests.length }} guest{{ waitingGuests.length > 1 ? 's' : '' }}
              </button>

              <div v-if="showAdmitHover" class="hover-popup admit-hover-popup" @click.stop>
                <div class="ah-header">Waiting to join <span class="ah-badge">Visible to hosts</span></div>
                <div class="ah-actions">
                   <button class="ah-btn-admit" @click="admitGuest(waitingGuests[0].id)">Admit</button>
                   <button class="ah-btn-deny" @click="denyGuest(waitingGuests[0].id)">Deny</button>
                </div>
                <div class="ah-guest-info">
                   <div class="ah-avatar"><svg viewBox="0 0 24 24" width="24" height="24" fill="#9aa0a6"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
                   <div class="ah-details">
                     <strong>1 unconfirmed user</strong>
                     <span>{{ waitingGuests[0].name }}</span>
                   </div>
                </div>
                <button class="ah-view-all" @click="togglePanel('people'); showAdmitHover = false">View all ({{ waitingGuests.length }}) &gt;</button>
              </div>
            </div>
          </Transition>

          <div class="people-btn-wrapper" @mouseenter="showPeopleHover = true; showAdmitHover = false">
            <button class="util-btn" :class="{ active: activePanel === 'people' }" @click.stop="togglePanel('people')" title="People">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
              <span class="util-badge people-count-badge">{{ participants.length + 1 }}</span>
            </button>
            
            <div v-if="showPeopleHover && !waitingGuests.length" class="hover-popup people-hover-popup" @click.stop>
               <div class="ph-title">People</div>
               <button v-if="isHost" class="ph-btn-host" @click="togglePanel('host'); showPeopleHover = false">Host controls</button>
               <div class="ph-list-box">
                  <div class="ph-joined-text">{{ participants.length + 1 }} joined</div>
                  <div class="ph-just-you" v-if="participants.length === 0">Just you</div>
                  <div class="ph-avatars">
                     <div class="ph-avatar host-avatar">
                        <svg viewBox="0 0 24 24" width="60%" height="60%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                     </div>
                     <div class="ph-avatar guest-avatar" v-for="p in participants" :key="p.peerId" :style="{ backgroundColor: p.avatarColor }">
                        <svg viewBox="0 0 24 24" width="60%" height="60%" fill="white"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                     </div>
                  </div>
               </div>
               <button class="ph-view-all" @click="togglePanel('people'); showPeopleHover = false">View everyone in this call &gt;</button>
            </div>
          </div>
        </div>

        <button class="util-btn" :class="{ active: activePanel === 'chat' }" @click.stop="togglePanel('chat')" title="In-call messages">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
          <span v-if="unreadCount > 0" class="util-badge">{{ unreadCount }}</span>
        </button>

        <button class="util-btn" :class="{ active: activePanel === 'activities' }" @click.stop="togglePanel('activities')" title="Activities" style="margin-left: -4px;">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M14 5C14 4.44772 14.4477 4 15 4H19C19.5523 4 20 4.44772 20 5V9C20 9.55228 19.5523 10 19 10C18.4477 10 18 9.55228 18 9V6H15C14.4477 6 14 5.55228 14 5ZM4.29289 13.2929C4.68342 12.9024 5.31658 12.9024 5.70711 13.2929L7.5 15.0858L9.29289 13.2929C9.68342 12.9024 10.3166 12.9024 10.7071 13.2929C11.0976 13.6834 11.0976 14.3166 10.7071 14.7071L8.91421 16.5L10.7071 18.2929C11.0976 18.6834 11.0976 19.3166 10.7071 19.7071C10.3166 20.0976 9.68342 20.0976 9.29289 19.7071L7.5 17.9142L5.70711 19.7071C5.31658 20.0976 4.68342 20.0976 4.29289 19.7071C3.90237 19.3166 3.90237 18.6834 4.29289 18.2929L6.08579 16.5L4.29289 14.7071C3.90237 14.3166 3.90237 13.6834 4.29289 13.2929ZM6 7.5C6 6.67157 6.67157 6 7.5 6C8.32843 6 9 6.67157 9 7.5C9 8.32843 8.32843 9 7.5 9C6.67157 9 6 8.32843 6 7.5ZM7.5 4C5.567 4 4 5.567 4 7.5C4 9.433 5.567 11 7.5 11C9.433 11 11 9.433 11 7.5C11 5.567 9.433 4 7.5 4ZM16.5 15C15.6716 15 15 15.6716 15 16.5C15 17.3284 15.6716 18 16.5 18C17.3284 18 18 17.3284 18 16.5C18 15.6716 17.3284 15 16.5 15ZM13 16.5C13 14.567 14.567 13 16.5 13C18.433 13 20 14.567 20 16.5C20 18.433 18.433 20 16.5 20C14.567 20 13 18.433 13 16.5ZM14 11C14.5523 11 15 10.5523 15 10C15 9.44772 14.5523 9 14 9C13.4477 9 13 9.44772 13 10C13 10.5523 13.4477 11 14 11ZM13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12ZM16 9C16.5523 9 17 8.55228 17 8C17 7.44772 16.5523 7 16 7C15.4477 7 15 7.44772 15 8C15 8.55228 15.4477 9 16 9Z"></path></svg>
          <span v-if="unreadActivitiesCount > 0" class="util-badge">{{ unreadActivitiesCount }}</span>
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
const SVG_HAND = `<svg viewBox="0 0 128 128" width="18" height="18" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet"><radialGradient id="IconifyId17ecdb2904d178eab19847" cx="57.16" cy="33.264" r="89.842" gradientUnits="userSpaceOnUse"><stop stop-color="#FFCA28" offset=".353"></stop><stop stop-color="#FFB300" offset=".872"></stop></radialGradient><path d="M50.9 122.3c-23.88 0-30.28-12.73-31.45-23.41c-.55-5.04-1.27-61.23-1.27-61.8c0-2.78.71-7.48 5.46-7.48c4.46 0 6.46 3.77 6.46 7.5l.46 23.24c.02.82.68 1.65 1.5 1.65s1.48-.84 1.5-1.66l.59-38.24c0-3.76 2-7.57 6.46-7.57s6.46 3.74 6.46 7.48l.48 37.92c.01.82.68 1.47 1.5 1.47s1.49-.66 1.5-1.48l.57-46.71c0-3.75 2-7.52 6.46-7.52s6.46 3.77 6.46 7.5l.59 46.46a1.5 1.5 0 0 0 3 0L68.1 25c0-3.75 2-7.52 6.46-7.52s6.46 3.77 6.46 7.5c.11 12.42.38 44.85.34 46.01c-.08.92.26 1.52.56 1.85c.4.44.98.69 1.63.69c1.44 0 2.87-1.14 3.27-1.49c1.78-1.54 3.31-3.95 4.93-6.5c2.02-3.18 4.12-6.48 6.36-7.39c1.76-.71 3.89-1.11 6.01-1.11c3.4 0 5.53 1 5.89 1.98c1.56 4.3-.32 5.95-5.09 9.6l-.92.71c-4.73 3.65-7.8 11.08-10.52 17.63c-1.11 2.67-2.15 5.2-3.23 7.32c-.65 1.28-1.21 2.79-1.86 4.53c-3.46 9.36-8.72 23.49-37.49 23.49z" fill="url(#IconifyId17ecdb2904d178eab19847)"></path><path d="M57.59 7.2c2.29 0 4.96 1.57 4.96 6.04l.59 46.62c.02 1.64 1.36 3.14 3 3.14s2.98-1.5 3-3.14l.47-34.78c0-2.77 1.3-6.04 4.96-6.04s4.96 3.21 4.96 6c.15 17.6.37 44.16.34 45.88c-.1 1.46.46 2.4.95 2.95c.69.76 1.67 1.17 2.74 1.17c1.9 0 3.61-1.3 4.25-1.86c1.94-1.68 3.53-4.18 5.21-6.83c1.81-2.85 3.86-6.07 5.66-6.8c1.56-.63 3.55-1 5.45-1c2.78 0 4.24.74 4.5 1.04c1.12 3.12.24 4.14-4.61 7.84l-.92.71c-5.05 3.89-8.2 11.52-10.99 18.25c-1.09 2.65-2.13 5.14-3.18 7.21c-.69 1.36-1.3 2.98-1.93 4.69c-1.68 4.51-3.77 10.13-8.78 14.57c-6.02 5.35-14.96 7.94-27.33 7.94c-12.57 0-27.96-3.83-29.96-22.08c-.45-4.12-1.07-45.9-1.26-61.62c0-3.98 1.53-6 4.16-6c2.29 0 5.16 1.57 5.16 6v.06l.26 22.96c.03 1.63 1.17 2.87 2.8 2.87h.01c1.64 0 2.97-1.25 2.99-2.88l.59-38.16c0-2.77 1.3-5.98 4.96-5.98s4.96 3.24 4.96 6.05l.45 37.91c.04 1.65.99 2.97 2.99 2.97s3.01-1.32 3.03-2.96l.59-46.74c0-4.43 2.68-6 4.97-6m0-3c-4.95 0-7.96 4.03-7.96 9l-.57 46.7l-.48-37.91c0-4.97-3.01-9-7.96-9s-7.96 4.03-7.96 9l-.59 38.15l-.47-23.03c0-4.97-3.02-9-7.96-9c-4.95 0-6.96 4.03-6.96 9c0 0 .72 56.78 1.28 61.94c.93 8.5 5.74 24.75 32.94 24.75c35.1 0 36.64-20.86 40.71-28.84c3.79-7.43 7.09-19.64 13.33-24.45c4.96-3.83 8.62-6.12 6.5-12c-.71-1.96-3.81-2.97-7.3-2.97c-2.2 0-4.56.4-6.57 1.22c-4.6 1.87-7.84 10.79-11.7 14.14c-.72.62-1.66 1.13-2.29 1.13c-.46 0-.76-.27-.7-.95c.06-.69-.34-46.09-.34-46.09c0-4.97-3.01-9-7.96-9s-7.96 4.03-7.96 9l-.47 34.65l-.6-46.44c0-4.98-3.01-9-7.96-9z" fill="#EDA600"></path><defs><path id="IconifyId17ecdb2904d178eab19848" d="M107.92 57.8c-2.27-2.53-8.01-3.72-13.54-1.54c-4.65 1.83-9.96 19.19-9.96 19.19l-3.87-6.4s-65.53 21.5-64.6 30s7.74 24.75 34.94 24.75c35.1 0 36.64-20.86 40.71-28.84c3.79-7.43 8.56-24.71 14.42-26.55c3.8-1.18 3.82-8.48 1.9-10.61z"></path></defs><clipPath id="IconifyId17ecdb2904d178eab19849"><use xlink:href="#IconifyId17ecdb2904d178eab19848"></use></clipPath><g clip-path="url(#IconifyId17ecdb2904d178eab19849)"><path d="M83.91 69.48C73 73.64 66.57 83.91 62.62 97.38c-.54 1.86 1.17 2.4 1.83.58c6.86-18.88 23.87-25.11 23.87-25.11l-4.41-3.37z" fill="#EDA600"></path></g></svg>`;
const SVG_ACTIVITIES = `<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M14 5C14 4.44772 14.4477 4 15 4H19C19.5523 4 20 4.44772 20 5V9C20 9.55228 19.5523 10 19 10C18.4477 10 18 9.55228 18 9V6H15C14.4477 6 14 5.55228 14 5ZM4.29289 13.2929C4.68342 12.9024 5.31658 12.9024 5.70711 13.2929L7.5 15.0858L9.29289 13.2929C9.68342 12.9024 10.3166 12.9024 10.7071 13.2929C11.0976 13.6834 11.0976 14.3166 10.7071 14.7071L8.91421 16.5L10.7071 18.2929C11.0976 18.6834 11.0976 19.3166 10.7071 19.7071C10.3166 20.0976 9.68342 20.0976 9.29289 19.7071L7.5 17.9142L5.70711 19.7071C5.31658 20.0976 4.68342 20.0976 4.29289 19.7071C3.90237 19.3166 3.90237 18.6834 4.29289 18.2929L6.08579 16.5L4.29289 14.7071C3.90237 14.3166 3.90237 13.6834 4.29289 13.2929ZM6 7.5C6 6.67157 6.67157 6 7.5 6C8.32843 6 9 6.67157 9 7.5C9 8.32843 8.32843 9 7.5 9C6.67157 9 6 8.32843 6 7.5ZM7.5 4C5.567 4 4 5.567 4 7.5C4 9.433 5.567 11 7.5 11C9.433 11 11 9.433 11 7.5C11 5.567 9.433 4 7.5 4ZM16.5 15C15.6716 15 15 15.6716 15 16.5C15 17.3284 15.6716 18 16.5 18C17.3284 18 18 17.3284 18 16.5C18 15.6716 17.3284 15 16.5 15ZM13 16.5C13 14.567 14.567 13 16.5 13C18.433 13 20 14.567 20 16.5C20 18.433 18.433 20 16.5 20C14.567 20 13 18.433 13 16.5ZM14 11C14.5523 11 15 10.5523 15 10C15 9.44772 14.5523 9 14 9C13.4477 9 13 9.44772 13 10C13 10.5523 13.4477 11 14 11ZM13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12ZM16 9C16.5523 9 17 8.55228 17 8C17 7.44772 16.5523 7 16 7C15.4477 7 15 7.44772 15 8C15 8.55228 15.4477 9 16 9Z"></path></svg>`;
const networkQuality = ref('good'); // 'good' (Green), 'fair' (Yellow), 'poor' (Red)
let networkInterval = null;

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

// BAGONG VARIABLES PARA SA EFFECTS SIDE PANEL
const activeEffect     = ref('none')
const isAiLoading      = ref(false)
const showCamOptions   = ref(false)
const effectsPreviewEl = ref(null) // Para sa maliit na video sa loob ng panel
const effectsTab       = ref('backgrounds') // 'backgrounds', 'filters', 'appearance'
const touchUpOn  = ref(false)
const lightingOn = ref(false)
const framingOn  = ref(false)

const SVG_SPEAKING_BLUE = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14c0 .55-.45 1-1 1s-1-.45-1-1v-4c0-.55.45-1 1-1s1 .45 1 1v4zm4 0c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1s1 .45 1 1v8zm4-2c0 .55-.45 1-1 1s-1-.45-1-1v-4c0-.55.45-1 1-1s1 .45 1 1v4z"/></svg>`;
const SVG_3DOTS = `<svg viewBox="0 0 24 24" width="20" height="20" fill="#9aa0a6"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>`;
const SVG_HOST = `<svg viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M19.85 8.75l4.15.83v4.84l-4.15.83 2.35 3.52-3.43 3.43-3.52-2.35-.83 4.15H9.58l-.83-4.15-3.52 2.35-3.43-3.43 2.35-3.52L0 14.42V9.58l4.15-.83L1.8 5.23 5.23 1.8l3.52 2.35L9.58 0h4.84l.83 4.15 3.52-2.35 3.43 3.43-2.35 3.52zm-1.57 5.07l4-.81v-2l-4-.81-.54-1.3 2.29-3.43-1.43-1.43-3.43 2.29-1.3-.54-.81-4h-2l-.81 4-1.3.54-3.43-2.29-1.43 1.43L6.38 8.9l-.54 1.3-4 .81v2l4 .81.54 1.3-2.29 3.43 1.43 1.43 3.43-2.29 1.3.54.81 4h2l.81-4 1.3-.54 3.43 2.29 1.43-1.43-2.29-3.43.54-1.3zm-8.186-4.672A3.43 3.43 0 0 1 12 8.57 3.44 3.44 0 0 1 15.43 12a3.43 3.43 0 1 1-5.336-2.852zm.956 4.274c.281.188.612.288.95.288A1.7 1.7 0 0 0 13.71 12a1.71 1.71 0 1 0-2.66 1.422z"></path></svg>`;

let selfieSegmentation = null
let blurRafId          = null
let processedStream    = null
const customBgImage    = new Image()

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

// DAGDAG ITO:
const remoteScreenSharing = ref(false)

const participants = ref([]) // Array para sa maraming guests
const isLocalSpeaking = ref(false) // Trigger para sa blue border mo
let localAudioAnalyser = null
let localAudioDataArray = null
let audioLoopId = null

// COMPUTED: Laging ilagay sa una (top-left) kung sino ang nagsasalita
const sortedParticipants = computed(() => {
  return [...participants.value].sort((a, b) => {
    // Kung nagsasalita si A pero si B hindi, mauna si A (-1)
    if (a.isSpeaking && !b.isSpeaking) return -1;
    if (!a.isSpeaking && b.isSpeaking) return 1;
    return 0;
  });
});

// COMPUTED: Alamin kung anong layout ang dapat gamitin
const layoutMode = computed(() => {
  // KAPAG MAY SCREEN SHARE:
  if (screenSharing.value || remoteScreenSharing.value) {
    if (participants.value.length > 0) return 'sidebar'; // May guest = Sidebar sa gilid
    return 'share'; // Solo = Floating PiP over screen share
  }
  
  // KAPAG WALANG SCREEN SHARE:
  if (participants.value.length === 1) return 'pip'; // 1-on-1 = Floating PiP
  return 'grid'; // Solo = Grid (full screen)
});

// ==========================================
// AUDIO ANALYZER LOGIC (Para sa Blue Border)
// ==========================================
let peerAudioContexts = []; // Para madaling patayin kapag umalis

function monitorAudioVolume(stream, participantObj, isLocal = false) {
  try {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const analyser = audioCtx.createAnalyser();
    analyser.fftSize = 256;
    
    const source = audioCtx.createMediaStreamSource(stream);
    source.connect(analyser);
    
    const dataArray = new Uint8Array(analyser.frequencyBinCount);

    const checkVolume = () => {
      if (audioCtx.state === 'closed') return;
      analyser.getByteFrequencyData(dataArray);
      
      let sum = 0;
      for (let i = 0; i < dataArray.length; i++) sum += dataArray[i];
      const average = sum / dataArray.length;
      
      const speaking = average > 15; // Threshold ng lakas ng boses
      
      if (isLocal) {
        // Iilaw lang kung nagsasalita AT nakabukas ang mic
        isLocalSpeaking.value = speaking && micOn.value; 
      } else if (participantObj) {
        participantObj.isSpeaking = speaking && participantObj.micOn;
      }
      
      requestAnimationFrame(checkVolume);
    };
    
    checkVolume();
    if (!isLocal) peerAudioContexts.push(audioCtx);
    return audioCtx;
  } catch (err) {
    console.warn("Audio analyzer error:", err);
    return null;
  }
}

const remoteConnected = ref(false)
const peerError       = ref('')
const isHost          = ref(false)
const isLocked        = ref(false)
const isChatEnabled   = ref(true)
const isShareScreenEnabled = ref(true)
const isMicEnabled         = ref(true) 
const isCameraEnabled      = ref(true)

watch(isChatEnabled, (newVal) => {
  if (isHost.value && dataConn.value?.open) {
    dataConn.value.send({ type: 'toggle_chat', enabled: newVal });
  }
});

watch(isShareScreenEnabled, (newVal) => {
  if (isHost.value && dataConn.value?.open) {
    dataConn.value.send({ type: 'toggle_screenshare', enabled: newVal });
  }
});

watch(isMicEnabled, (newVal) => {
  if (isHost.value && dataConn.value?.open) dataConn.value.send({ type: 'toggle_mic_permission', enabled: newVal });
});


watch(isCameraEnabled, (newVal) => {
  if (isHost.value && dataConn.value?.open) dataConn.value.send({ type: 'toggle_camera_permission', enabled: newVal });
});
const rejected        = ref(false)
const rejectedReason = ref('');

const waitingGuests = ref([])
const showPeopleHover = ref(false)
const showAdmitHover = ref(false)
const showWaitingOptions = ref(null)
const showPinMenu = ref(false)

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

function playSound(fileName) {
  try {
    // Dito pa lang gagawa ng Audio para sure na may user interaction na!
    const audio = new Audio(`/sounds/${fileName}.ogg`);
    audio.volume = 1.0; 
    audio.play().catch(e => {
      console.warn("Browser blocked sound:", e);
    });
  } catch (err) {
    console.error("Audio play error:", err);
  }
}

const unreadActivitiesCount = ref(0);
const polls = ref([]); // Listahan ng mga active polls
const isCreatingPoll = ref(false); // Para sa View ng Host
const newPollQuestion = ref('');
const newPollOptions = ref(['', '']);

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

let audioMixerCtx      = null;

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
  // Kung may kausap O KAYA nag-share screen ka, dapat laging naka-PiP (maliit sa gilid) ang camera mo
  if (remoteConnected.value || screenSharing.value) return 'local-pip'
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

function addPollOption() { newPollOptions.value.push(''); }
function removePollOption(index) { newPollOptions.value.splice(index, 1); }

function launchPoll() {
  if (!newPollQuestion.value.trim() || newPollOptions.value.some(o => !o.trim())) return;
  const poll = {
    id: Date.now().toString(),
    question: newPollQuestion.value.trim(),
    options: newPollOptions.value.map(opt => ({ text: opt.trim(), votes: 0 })),
    totalVotes: 0,
    votedIndex: null,
    isEnded: false // BAGONG DAGDAG: Status kung tapos na ang botohan
  };
  polls.value.unshift(poll);
  isCreatingPoll.value = false;
  newPollQuestion.value = '';
  newPollOptions.value = ['', ''];
  if (dataConn.value?.open) dataConn.value.send({ type: 'new_poll', poll });
}

// BAGONG DAGDAG: I-lock ang poll
function endPoll(pollId) {
  const p = polls.value.find(p => p.id === pollId);
  if (p) p.isEnded = true;
  if (dataConn.value?.open) dataConn.value.send({ type: 'end_poll', pollId });
}

// BAGONG DAGDAG: Burahin ang poll
function deletePoll(pollId) {
  if(confirm("Are you sure you want to delete this poll?")) {
    polls.value = polls.value.filter(p => p.id !== pollId);
    if (dataConn.value?.open) dataConn.value.send({ type: 'delete_poll', pollId });
  }
}

function votePoll(pollId, optIndex) {
  const p = polls.value.find(p => p.id === pollId);
  // Bawal bumoto nang dalawang beses
  if (!p || p.votedIndex !== null) return; 
  p.votedIndex = optIndex;
  p.options[optIndex].votes++;
  p.totalVotes++;
  // I-broadcast ang boto
  if (dataConn.value?.open) dataConn.value.send({ type: 'poll_vote', pollId, optIndex });
}

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

function startNetworkMonitor() {
  // I-check ang ping (Round Trip Time) kada 3 segundo
  networkInterval = setInterval(async () => {
    if (!currentCall.value || !currentCall.value.peerConnection) {
      networkQuality.value = 'good';
      return;
    }
    
    try {
      const stats = await currentCall.value.peerConnection.getStats();
      let rtt = 0; // Ping in seconds

      stats.forEach(report => {
        // Kunin ang Round Trip Time mula sa WebRTC connection
        if (report.type === 'candidate-pair' && report.state === 'succeeded') {
          if (report.currentRoundTripTime !== undefined) {
            rtt = report.currentRoundTripTime; 
          }
        }
      });

      // Logic para malaman ang kulay
      if (rtt > 0.3) {
        // Ping is > 300ms (Ma-lag)
        networkQuality.value = 'poor'; 
      } else if (rtt > 0.15) {
        // Ping is > 150ms (Medyo delay)
        networkQuality.value = 'fair';
      } else {
        // Smooth
        networkQuality.value = 'good';
      }
    } catch (err) {
      console.warn("Could not fetch network stats", err);
    }
  }, 3000);
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

  if (screenStream && screenStream.getAudioTracks().length > 0) {
    const screenSource = audioContext.createMediaStreamSource(screenStream);
    screenSource.connect(audioDestination);
    audioSources.push(screenSource);
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

  // KUNIN ANG SAVED EFFECTS MULA SA PRE-JOIN
  const savedEffect = sessionStorage.getItem('activeEffect');
  if (savedEffect) {
    activeEffect.value = savedEffect;
    // KUNG ANG NAPILI AY IMAGE BACKGROUND, I-LOAD AGAD ITO SA MEMORY!
    if (savedEffect !== 'none' && !savedEffect.startsWith('blur')) {
      customBgImage.src = savedEffect; 
    }
  }
  
  touchUpOn.value = sessionStorage.getItem('touchUpOn') === 'true';
  lightingOn.value = sessionStorage.getItem('lightingOn') === 'true';
  framingOn.value = sessionStorage.getItem('framingOn') === 'true';

  try {
    localStream = await navigator.mediaDevices.getUserMedia({ 
      video: {
        width: { ideal: 1920 }, 
        height: { ideal: 1080 },
        frameRate: { ideal: 30, max: 60 }      
      },
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
    
    // === ANTI-BLINK FIX START ===
    if (rawVideoEl.value) {
      rawVideoEl.value.srcObject = localStream;
      rawVideoEl.value.play().catch(()=>{}); // Piliting mag-play!
    } 

    // Simulan agad ang Canvas! Wag nang gamitin ang localStream direkta.
    if (!processedStream && blurCanvasEl.value) {
      processedStream = blurCanvasEl.value.captureStream(30);
    }
    const processedTrack = processedStream.getVideoTracks()[0];
    const finalStream = new MediaStream([processedTrack, ...localStream.getAudioTracks()]);
    
    if (localVideoEl.value) {
      localVideoEl.value.srcObject = finalStream;
    }
    
    // Kung may napiling effect, AI ang magha-handle. Kung wala, normal loop lang.
    if (activeEffect.value !== 'none' || touchUpOn.value || lightingOn.value || framingOn.value) {
      updateEffects(); 
    } else {
      processSimpleFrame(); 
    } 
    
    monitorAudioVolume(localStream, null, true);

    playSound('join');

  } catch {
    peerError.value = 'Could not access camera/microphone.'
    return
  }

  startCallTimer()
  peer.value = new Peer()

  if (sessionStorage.getItem('autoPresent') === 'true') {
    sessionStorage.removeItem('autoPresent');
    
    setTimeout(() => {
      toggleScreenShare();
    }, 1000); 
  }

  peer.value.on('open', async (id) => {
    await logAction('peerjs_connected', { meeting_code: code, peer_id: id })

    if (isHost.value) {
      showReadyDialog.value = true
      await axios.post(`${API_URL}/api/meetings/${code}/peer`, { peer_id: id })

      peer.value.on('call', (call) => {
        if (isLocked.value) {
          call.close();
          return;
        }
        // Ilagay sa Waiting Queue imbes na Modal
        waitingGuests.value.push({
          id: call.peer,
          name: `Guest ${waitingGuests.value.length + 1}`,
          call: call,
          conn: null
        });
        playSound('knock');
      })

      peer.value.on('connection', (conn) => {
        if (isLocked.value) {
          conn.on('open', () => {
            conn.send({ type: 'rejected', reason: 'locked' }); 
            setTimeout(() => conn.close(), 500);
          });
          return;
        }

        // Hanapin kung nasa waiting queue
        const guest = waitingGuests.value.find(g => g.id === conn.peer);
        if (guest) {
          guest.conn = conn;
        } else if (!participants.value.find(p => p.peerId === conn.peer)) {
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
  clearInterval(networkInterval)
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

    const videoTrack = processedStream ? processedStream.getVideoTracks()[0] : localStream.getVideoTracks()[0];
    const streamToSend = new MediaStream([videoTrack, ...localStream.getAudioTracks()]);
    const call = peer.value.call(data.peer_id, streamToSend)
    
    currentCall.value = call
    currentCall.value.on('stream', (remote) => {

      if (!remoteConnected.value) {
        playSound('join');
      }

      remoteConnected.value = true;
      rejected.value = false;

      // ==========================================
      // DAGDAG ITO: I-push sa Grid Array at i-set ang AI
      // ==========================================
      const newPeerId = currentCall.value.peer || Date.now().toString();
      participants.value = [{
        peerId: newPeerId,
        name: isHost.value ? 'Guest' : 'Host',
        avatarColor: remoteAvatarColor.value,
        isSpeaking: false,
        micOn: remoteMicOn.value,
        cameraOn: remoteCameraOn.value,
        handRaised: remoteHandRaised.value
      }];
      
      // Simulan ang pakikinig sa boses ng guest
      monitorAudioVolume(remote, participants.value[0], false);
      
      // I-attach ang video stream sa dynamic ID na ginawa ng v-for loop natin
      nextTick(() => {
        const vidEl = document.getElementById('vid-' + newPeerId);
        if (vidEl) vidEl.srcObject = remote;
      });
      // ==========================================

      addRemoteStreamToMix(remote);
      // (kung may startNetworkMonitor() sa loob ng admitGuest, panatilihin iyon dito)
      if (isHost.value) startNetworkMonitor(); 
    });
    call.on('close', () => { 
      if (!remoteConnected.value) {
        rejected.value = true;
      } else {
        playSound('leave'); 
      }
      remoteConnected.value = false 
      participants.value = [];
    })

    const conn = peer.value.connect(data.peer_id)
    dataConn.value = conn
    conn.on('data', handleIncomingData)
  } catch {
    setTimeout(() => connectToHost(code, attempts + 1), 1000)
  }
}

// --- DAGDAG: LOGIC PARA SA PAG-RECEIVE NG FILES ---
function handleIncomingData(data) {
  if (data.type === 'chat') {
    messages.value.push({ id: Date.now(), sender: data.sender, text: data.text, time: data.time, isOwn: false })
    if (activePanel.value !== 'chat') {
      unreadCount.value++;
      playSound('chat');
    }
    scrollMessages()
    
  } else if (data.type === 'chat_file') {
    const blob = new Blob([data.fileBlob], { type: data.fileType });
    const fileUrl = URL.createObjectURL(blob);

    messages.value.push({
      id: Date.now(),
      sender: data.sender,
      time: data.time,
      isOwn: false,
      file: true,
      fileName: data.fileName,
      fileType: data.fileType,
      fileData: fileUrl // <-- Render ang link!
    });
    
    if (activePanel.value !== 'chat') {
      unreadCount.value++;
      playSound('chat');
    }
    scrollMessages()
    
  } else if (data.type === 'reaction') {
    const sender = isHost.value ? 'Guest' : 'Host'
    spawnEmoji(data.emoji, sender)
  } else if (data.type === 'hand') {
    remoteHandRaised.value = data.raised;
    if (participants.value.length) participants.value[0].handRaised = data.raised;
    if (data.raised) playSound('hand');
    
  } else if (data.type === 'mic') {
    remoteMicOn.value = data.on;
    if (participants.value.length) participants.value[0].micOn = data.on;
    
  } else if (data.type === 'camera') {
    remoteCameraOn.value = data.on;
    if (participants.value.length) participants.value[0].cameraOn = data.on;

  } else if (data.type === 'toggle_chat') {
    isChatEnabled.value = data.enabled;

  } else if (data.type === 'toggle_share_permission') {
    isShareScreenEnabled.value = data.enabled;
    // Kung kasalukuyang nag-pe-present ang guest tapos biglang pinatay ng host, itigil agad!
    if (!data.enabled && screenSharing.value) {
      toggleScreenShare();
      alert("The host has disabled screen sharing for participants.");
    }

  } else if (data.type === 'toggle_mic_permission') {
    isMicEnabled.value = data.enabled;
    if (!data.enabled && micOn.value) {
      toggleMic();
      alert("The host muted your microphone and disabled turning it back on.");
    }

  } else if (data.type === 'toggle_camera_permission') {
    isCameraEnabled.value = data.enabled;
    if (!data.enabled && cameraOn.value) {
      toggleCamera();
      alert("The host turned off your camera and disabled turning it back on.");
    }

  } else if (data.type === 'screen_share') {
    remoteScreenSharing.value = data.on;
    
    nextTick(() => {
      if (data.on && screenVideoEl.value && participants.value.length > 0) {
        const remoteVid = document.getElementById('vid-' + participants.value[0].peerId);
        if (remoteVid && remoteVid.srcObject) {
          screenVideoEl.value.srcObject = remoteVid.srcObject;
        }
      } else if (!data.on && screenVideoEl.value && !screenSharing.value) {
        screenVideoEl.value.srcObject = null;
      }
    });

  } else if (data.type === 'force_mute') {
    if (micOn.value) {
      toggleMic();
      alert("The host has muted your microphone.");
    }
  } else if (data.type === 'new_poll') {
    polls.value.unshift(data.poll);
    if (activePanel.value !== 'activities') unreadActivitiesCount.value++;

  // ACTIVITIES: Receive Vote
  } else if (data.type === 'poll_vote') {
    const p = polls.value.find(p => p.id === data.pollId);
    if (p) { p.options[data.optIndex].votes++; p.totalVotes++; }

  } else if (data.type === 'end_poll') {
    const p = polls.value.find(p => p.id === data.pollId);
    if (p) p.isEnded = true;

  // BAGONG DAGDAG: Kapag binura ng host ang poll
  } else if (data.type === 'delete_poll') {
    polls.value = polls.value.filter(p => p.id !== data.pollId);

  // ACTIVITIES: Sync existing polls sa mga bagong join na guest
  } else if (data.type === 'sync_polls') {
    polls.value = data.polls;

  } else if (data.type === 'rejected') {
    rejected.value = true;
    rejectedReason.value = data.reason || 'locked';
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
  if (localStream) {
    localStream.getVideoTracks().forEach(t => t.enabled = cameraOn.value)
  }
  if (!cameraOn.value) {
    cancelAnimationFrame(blurRafId)
    
    // DAGDAG ITO: Gawing itim (black screen) ang canvas kapag pinatay ang camera
    // Para hindi mag-freeze ang mukha mo sa screen ng kausap mo!
    if (blurCanvasEl.value) {
      const ctx = blurCanvasEl.value.getContext('2d');
      ctx.fillStyle = '#3c4043'; // Dark gray/black
      ctx.fillRect(0, 0, blurCanvasEl.value.width, blurCanvasEl.value.height);
    }
  } else {
    updateEffects()
  }
  
  if (dataConn.value?.open) {
    dataConn.value.send({ type: 'camera', on: cameraOn.value })
  }
}

async function toggleScreenShare() {
  if (screenSharing.value) {
    // 1. REVERT VIDEO: Ibalik sa Camera ang video
    const vidSender = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'video')
    const videoTrackToRestore = processedStream ? processedStream.getVideoTracks()[0] : originalVideoTrack;
    if (vidSender && videoTrackToRestore) vidSender.replaceTrack(videoTrackToRestore)

    // 2. REVERT AUDIO: Ibalik sa pure Microphone lang ang audio (Tanggalin ang Screen Audio)
    const audSender = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'audio')
    const localAudioTrack = localStream?.getAudioTracks()[0];
    if (audSender && localAudioTrack) audSender.replaceTrack(localAudioTrack)

    // 3. Patayin ang Audio Mixer para hindi mag-memory leak
    if (audioMixerCtx && audioMixerCtx.state !== 'closed') {
      audioMixerCtx.close();
      audioMixerCtx = null;
    }

    if (screenVideoEl.value) screenVideoEl.value.srcObject = null
    screenStream = null
    screenSharing.value = false
    if (dataConn.value?.open) dataConn.value.send({ type: 'screen_share', on: false })
    logAction('screen_share_stopped', { meeting_code: route.params.code })
    return
  }

  if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
    alert("Screen sharing is not supported on your current mobile browser. Please use a Desktop/Laptop.");
    return;
  }

  try {
    // Kuhanin ang Screen pati na ang System Audio
    screenStream = await navigator.mediaDevices.getDisplayMedia({ 
      video: true, 
      audio: true 
    })
    const screenVideoTrack = screenStream.getVideoTracks()[0]
    const screenAudioTrack = screenStream.getAudioTracks()[0]

    // REPLACE VIDEO: Ipadala ang screen video sa PeerJS
    const vidSender = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'video')
    if (vidSender) vidSender.replaceTrack(screenVideoTrack)

    // MIX AUDIO: Kung nag-share ng "System Audio" ang user (ex. tab audio)
    if (screenAudioTrack) {
      audioMixerCtx = new (window.AudioContext || window.webkitAudioContext)();
      const dest = audioMixerCtx.createMediaStreamDestination();

      // Isaksak ang Microphone sa Mixer
      const localAudioTrack = localStream?.getAudioTracks()[0];
      if (localAudioTrack) {
        const micSource = audioMixerCtx.createMediaStreamSource(new MediaStream([localAudioTrack]));
        micSource.connect(dest);
      }

      // Isaksak ang Screen/YouTube Audio sa Mixer
      const screenSource = audioMixerCtx.createMediaStreamSource(new MediaStream([screenAudioTrack]));
      screenSource.connect(dest);

      // Kunin ang Pinaghalong Audio (Mic + Screen)
      const mixedAudioTrack = dest.stream.getAudioTracks()[0];

      // Ipadala ang Pinaghalong Audio sa kausap!
      const audSender = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'audio')
      if (audSender) audSender.replaceTrack(mixedAudioTrack)

      if (isRecording.value && typeof audioContext !== 'undefined' && audioContext && audioDestination) {
        const recScreenSource = audioContext.createMediaStreamSource(new MediaStream([screenAudioTrack]));
        recScreenSource.connect(audioDestination);
        audioSources.push(recScreenSource);
      }
    }

    if (screenVideoEl.value) screenVideoEl.value.srcObject = screenStream
    screenSharing.value = true
    if (dataConn.value?.open) dataConn.value.send({ type: 'screen_share', on: true })
    logAction('screen_share_started', { meeting_code: route.params.code })

    // Kapag pinindot ang "Stop sharing" sa popup bar ng browser
    screenVideoTrack.onended = () => {
      // Ibalik sa normal ang Video at Audio
      const s2 = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'video')
      const videoTrackToRestore = processedStream ? processedStream.getVideoTracks()[0] : originalVideoTrack;
      if (s2 && videoTrackToRestore) s2.replaceTrack(videoTrackToRestore)

      const a2 = currentCall.value?.peerConnection?.getSenders().find(s => s.track?.kind === 'audio')
      const lAudio = localStream?.getAudioTracks()[0];
      if (a2 && lAudio) a2.replaceTrack(lAudio)

      if (audioMixerCtx && audioMixerCtx.state !== 'closed') {
        audioMixerCtx.close();
        audioMixerCtx = null;
      }

      if (screenVideoEl.value) screenVideoEl.value.srcObject = null
      screenStream = null
      screenSharing.value = false
      if (dataConn.value?.open) dataConn.value.send({ type: 'screen_share', on: false })
      logAction('screen_share_stopped', { meeting_code: route.params.code })
    }
  } catch (err) { 
    // Kung hindi NotAllowedError (user cancelled), ibig sabihin may technical error ang browser
    if (err.name !== 'NotAllowedError') {
      alert("Error sharing screen: " + err.message);
    }
  }
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

// ==========================
// VISUAL EFFECTS (Blur & Virtual Background)
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

  cancelAnimationFrame(blurRafId); 

  if (hasBgEffect && !selfieSegmentation) {
    isAiLoading.value = true;
    selfieSegmentation = new SelfieSegmentation({locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/${file}`});
    selfieSegmentation.setOptions({ modelSelection: 0 }); 
    selfieSegmentation.onResults(onBlurResults);
    await selfieSegmentation.initialize(); 
    isAiLoading.value = false;
  }

  if (hasBgEffect) {
    processBlurFrame(); 
  } else {
    processSimpleFrame();
  }
}

// BAGONG FUNCTION: Simple Loop para sa Lighting at Touchup (Walang AI Background)
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
  }).catch((err) => {
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

  // TANGGAL NA ANG ctx.scale(-1, 1) DITO TULAD SA PREJOIN

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
  } else if (activeEffect.value === 'blur-heavy' || activeEffect.value === 'blur') { 
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
function admitGuest(guestId) {
  const index = waitingGuests.value.findIndex(g => g.id === guestId);
  if (index === -1) return;
  const guest = waitingGuests.value.splice(index, 1)[0];
  showAdmitHover.value = false;

  if (guest.call) {
    currentCall.value = guest.call;
    let videoTrack = processedStream ? processedStream.getVideoTracks()[0] : localStream.getVideoTracks()[0];
    if (screenSharing.value && screenStream) videoTrack = screenStream.getVideoTracks()[0];
    
    const streamToAnswer = new MediaStream([videoTrack, ...localStream.getAudioTracks()]);
    guest.call.answer(streamToAnswer);
    logAction('peerjs_call_received', { meeting_code: route.params.code });
    
    currentCall.value.on('stream', (remote) => {
      if (!remoteConnected.value) {
        playSound('join');
      }
      remoteConnected.value = true;
      rejected.value = false;
      const newPeerId = currentCall.value.peer || Date.now().toString();
      participants.value = [{
        peerId: newPeerId, name: guest.name, avatarColor: remoteAvatarColor.value,
        isSpeaking: false, micOn: remoteMicOn.value, cameraOn: remoteCameraOn.value, handRaised: remoteHandRaised.value
      }];
      monitorAudioVolume(remote, participants.value[0], false);
      nextTick(() => {
        const vidEl = document.getElementById('vid-' + newPeerId);
        if (vidEl) vidEl.srcObject = remote;
      });
      addRemoteStreamToMix(remote);
      startNetworkMonitor();
    });
    currentCall.value.on('close', () => { 
      if (!remoteConnected.value) rejected.value = true;
      else playSound('leave'); // BAGONG DAGDAG: Tumunog kapag umalis o na-disconnect ang guest
      remoteConnected.value = false; participants.value = [];
    });
  }
  
  if (guest.conn) {
    dataConn.value = guest.conn;
    dataConn.value.on('data', handleIncomingData);
    setTimeout(() => {
      if (dataConn.value?.open) {
        dataConn.value.send({ type: 'screen_share', on: screenSharing.value });
        dataConn.value.send({ type: 'mic', on: micOn.value });
        dataConn.value.send({ type: 'camera', on: cameraOn.value });
        dataConn.value.send({ type: 'toggle_chat', enabled: isChatEnabled.value });
        dataConn.value.send({ type: 'toggle_share_permission', enabled: isShareScreenEnabled.value });
        dataConn.value.send({ type: 'toggle_mic_permission', enabled: isMicEnabled.value });
        dataConn.value.send({ type: 'toggle_camera_permission', enabled: isCameraEnabled.value });
        dataConn.value.send({ type: 'sync_polls', polls: polls.value });
      }
    }, 800);
  }
}

function denyGuest(guestId) {
  const index = waitingGuests.value.findIndex(g => g.id === guestId);
  if (index === -1) return;
  const guest = waitingGuests.value.splice(index, 1)[0];
  showWaitingOptions.value = null;

  if (guest.call) guest.call.close();
  if (guest.conn) {
    guest.conn.send({ type: 'rejected', reason: 'denied' });
    setTimeout(() => guest.conn.close(), 500);
  }
}

function admitAllGuests() {
  // Simpleng loop para i-admit lahat ng nasa queue
  const ids = waitingGuests.value.map(g => g.id);
  ids.forEach(id => admitGuest(id));
}
// ==========================

function toggleHand() {
  handRaised.value = !handRaised.value

  if (handRaised.value) {
    playSound('hand');
  }

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

// --- DAGDAG: CHAT ENHANCEMENTS LOGIC ---

// Function para mag-generate ng tamang SVG Icon base sa File Type
function getFileIconSVG(fileName) {
  if (!fileName) return '';
  const ext = fileName.split('.').pop().toLowerCase();

  // PDF (Red)
  if (['pdf'].includes(ext)) {
    return `<svg viewBox="0 0 24 24" width="20" height="20" fill="#ea4335"><path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM14 10h1v-1.5h-1V10z"/></svg>`;
  } 
  // Word / Docs (Blue)
  else if (['doc', 'docx', 'txt', 'rtf'].includes(ext)) {
    return `<svg viewBox="0 0 24 24" width="20" height="20" fill="#4285f4"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>`;
  } 
  // Excel / Sheets / CSV (Green)
  else if (['xls', 'xlsx', 'csv'].includes(ext)) {
    return `<svg viewBox="0 0 24 24" width="20" height="20" fill="#34a853"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14H6v-3h4v3zm0-5H6V9h4v3zm5 5h-4v-3h4v3zm0-5h-4V9h4v3z"/></svg>`;
  } 
  // PowerPoint / Slides (Yellow)
  else if (['ppt', 'pptx'].includes(ext)) {
    return `<svg viewBox="0 0 24 24" width="20" height="20" fill="#fbbc04"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm-6 10h2v6H8v-6zm3-1h3c1.1 0 2 .9 2 2v2c0 1.1-.9 2-2 2h-3v-6zm2 3v-1h-1v1h1zm1-8V3.5L18.5 9H13z"/></svg>`;
  } 
  // ZIP / RAR / Archive (Orange/Yellow Folder)
  else if (['zip', 'rar', '7z', 'tar'].includes(ext)) {
    return `<svg viewBox="0 0 24 24" width="20" height="20" fill="#fbbc04"><path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6 10H6v-2h8v2zm4-4H6v-2h12v2z"/></svg>`;
  } 
  // Default / Other Files (Gray)
  else {
    return `<svg viewBox="0 0 24 24" width="20" height="20" fill="#9aa0a6"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>`;
  }
}

// Function para gawing clickable ang mga URL
function formatMessage(text) {
  if (!text) return '';
  // Simpleng regex para hanapin ang mga URL
  const urlRegex = /(https?:\/\/[^\s]+)/g;
  return text.replace(urlRegex, (url) => {
    return `<a href="${url}" target="_blank" class="chat-inline-link">${url}</a>`;
  });
}

// Function para i-handle ang pagpili ng file
const fileInputEl = ref(null);

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 15 * 1024 * 1024) {
    alert("File is too large. Please select a file smaller than 15MB.");
    return;
  }

  const code = route.params.code;
  const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

  // 1. I-display agad sa sariling screen (Gamit ang Object URL para mas magaan sa memory!)
  const fileUrl = URL.createObjectURL(file);
  messages.value.push({
    id: Date.now(),
    sender: 'You',
    time: time,
    isOwn: true,
    file: true,
    fileName: file.name,
    fileType: file.type,
    fileData: fileUrl 
  });
  scrollMessages();

  // 2. I-send ang File object diretso sa PeerJS (Awtomatikong pinuputol ng PeerJS ang 15MB kaya di magla-lag!)
  if (dataConn.value?.open) {
    dataConn.value.send({
      type: 'chat_file',
      sender: isHost.value ? 'Host' : 'Guest',
      time: time,
      fileName: file.name,
      fileType: file.type,
      fileBlob: file // <-- Mismong file na ang ipapadala, hindi na mabigat na Base64 text
    });
  }

  // 3. I-upload sa Laravel Backend gamit ang FormData!
  const formData = new FormData();
  formData.append('meeting_code', code);
  formData.append('sender', isHost.value ? 'Host' : 'Guest');
  formData.append('file', file); // <-- Ipasa ang file sa Laravel
  formData.append('message', 'Attached File');

  axios.post(`${API_URL}/api/meetings/${code}/chats`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }).then(res => {
    console.log("File uploaded to Laravel successfully!");
  }).catch(err => {
    console.error("FILE UPLOAD ERROR: ", err);
  });

  event.target.value = ''; // I-reset ang input
}

// Function para lakihan ang image pag kinlick
function openImage(dataUrl) {
  const w = window.open("");
  w.document.write(`<img src="${dataUrl}" style="max-width:100%; max-height:100vh; display:block; margin:auto;"/>`);
}

// --- END DAGDAG ---

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
  if (name === 'activities') unreadActivitiesCount.value = 0

  // DAGDAG ITO: Buhayin ang Mini-Preview kapag binuksan ang Effects Panel
  if (name === 'effects') {
    setTimeout(() => {
      if (effectsPreviewEl.value && processedStream) {
        // LAGING ikabit sa Canvas Stream para auto-sync sa main video!
        effectsPreviewEl.value.srcObject = new MediaStream([processedStream.getVideoTracks()[0], ...localStream.getAudioTracks()]);
      }
    }, 100);
  }
}

async function leaveCall() {

  playSound('leave');

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
  
  // FIX: I-check muna kung HOST ang umaalis. 
  // Kung HOST siya, i-eend ang meeting sa database. 
  // Kung GUEST lang siya, hindi papansinin ang code na ito kaya buhay pa rin ang room!
  if (isHost.value) {
    axios.patch(`${API_URL}/api/meetings/${code}/end`).catch(() => {})
  }

  currentCall.value?.close()
  dataConn.value?.close()
  peer.value?.destroy()
  clearInterval(callTimer)
  clearInterval(networkInterval)

  sessionStorage.setItem('wasHost', sessionStorage.getItem('isHost') ?? 'false')
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

  if (audioMixerCtx && audioMixerCtx.state !== 'closed') {
    audioMixerCtx.close();
  }
  
  if (pipWindow) {
      const pipLocal = pipWindow.document.getElementById('local-video');
      const pipMain = pipWindow.document.getElementById('main-video');
      if (pipLocal) pipLocal.srcObject = null;
      if (pipMain) pipMain.srcObject = null;
      pipWindow.close();
  }
}

function closeAll() { showEmojiPicker.value = false; showMoreDropdown.value = false; showPinMenu.value = false; showWaitingOptions.value = null; }
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

/* ========================
   ACTIVITIES & POLLS UI
   ======================== */
.activities-panel-body { flex: 1; display: flex; flex-direction: column; overflow-y: auto; background: #202124; }
.polls-list-view, .poll-create-view { display: flex; flex-direction: column; padding: 16px; gap: 16px; flex: 1; }
.act-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.act-title { display: flex; align-items: center; gap: 8px; font-size: 15px; color: #8ab4f8; font-weight: 500; }
.btn-start-poll { background: #8ab4f8; color: #202124; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 500; cursor: pointer; }
.btn-start-poll:hover { background: #aecbfa; }
.no-activities { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #9aa0a6; font-size: 14px; gap: 12px; }

/* POLL CARD */
.poll-card { background: rgba(255,255,255,0.05); border-radius: 8px; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.poll-q { font-size: 15px; font-weight: 500; color: #e8eaed; line-height: 1.4; }
.poll-opts { display: flex; flex-direction: column; gap: 8px; }
.poll-opt { background: #2d2e30; border-radius: 8px; padding: 12px; cursor: pointer; transition: background 0.2s, border 0.2s; border: 1px solid #3c4043; overflow: hidden; position: relative; }
.poll-opt:hover:not(.disabled) { background: #3c4043; }
.poll-opt.voted { border-color: #8ab4f8; }
.poll-opt.disabled { cursor: default; }

/* BAGONG DAGDAG PARA SA POLL CARD HEADER & FOOTER */
.poll-header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.poll-ended-badge { background: #3c4043; color: #9aa0a6; font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0; }
.poll-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; min-height: 24px; }
.poll-host-actions { display: flex; align-items: center; gap: 12px; margin-left: auto; }
.btn-icon-trash { background: none; border: none; color: #9aa0a6; cursor: pointer; padding: 6px; border-radius: 50%; display: flex; transition: background 0.2s, color 0.2s; margin-right: -6px; }
.btn-icon-trash:hover { background: rgba(234, 67, 53, 0.1); color: #ea4335; }

.opt-text-row { display: flex; align-items: center; gap: 12px; position: relative; z-index: 2; }
.opt-radio { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #5f6368; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.poll-opt.voted .opt-radio { border-color: #8ab4f8; }
.radio-filled { width: 8px; height: 8px; border-radius: 50%; background: #8ab4f8; }
.opt-label { flex: 1; font-size: 14px; color: #e8eaed; }
.opt-votes { font-size: 12px; color: #9aa0a6; font-weight: 500; }

.opt-progress-bg { position: absolute; top: 0; left: 0; height: 100%; width: 100%; background: transparent; z-index: 1; pointer-events: none; }
.opt-progress-bar { height: 100%; background: rgba(138, 180, 248, 0.15); transition: width 0.4s ease; border-radius: 8px 0 0 8px; }
.poll-meta { font-size: 12px; color: #9aa0a6; text-align: right; }

/* CREATE POLL UI */
.create-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.create-header h4 { margin: 0; color: #e8eaed; font-size: 16px; font-weight: 500; }
.btn-back { background: none; border: none; color: #9aa0a6; cursor: pointer; padding: 4px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.btn-back:hover { background: #3c4043; color: white; }

.poll-input { width: 100%; background: #2d2e30; border: 1px solid #5f6368; border-radius: 4px; padding: 12px 16px; color: #e8eaed; font-size: 14px; outline: none; transition: border-color 0.2s; }
.poll-input:focus { border-color: #8ab4f8; }
.poll-input.main-q { font-size: 15px; font-weight: 500; }

.poll-opts-edit { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
.opt-edit-row { display: flex; align-items: center; gap: 8px; }
.btn-remove-opt { background: none; border: none; cursor: pointer; display: flex; padding: 8px; border-radius: 50%; }
.btn-remove-opt:hover { background: #3c4043; }
.btn-add-opt { background: none; border: none; color: #8ab4f8; font-size: 14px; font-weight: 500; cursor: pointer; text-align: left; padding: 8px 0; align-self: flex-start; }
.btn-add-opt:hover { color: #aecbfa; }

.poll-actions { display: flex; justify-content: flex-end; margin-top: auto; }
.btn-launch { background: #8ab4f8; color: #202124; border: none; padding: 10px 24px; border-radius: 24px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
.btn-launch:hover:not(:disabled) { background: #aecbfa; }
.btn-launch:disabled { background: #3c4043; color: #5f6368; cursor: not-allowed; }

/* ========================
   DISABLED BOTTOM BAR BUTTONS
   ======================== */
.ctrl-group-main:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.ctrl-group:has(.ctrl-group-main:disabled):hover {
  background: #3c4043; /* Huwag i-highlight kung bawal i-click */
}

/* ========================
   DISABLED BOTTOM BAR BUTTONS
   ======================== */
.ctrl-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.ctrl-btn:disabled:hover {
  background: #3c4043; /* Tanggalin ang hover effect kapag disabled */
  transform: none;
}

/* ========================
   DISABLED CHAT UI
   ======================== */
.chat-input:disabled {
  background: #2d2e30;
  color: #5f6368;
  cursor: not-allowed;
}
.chat-input:disabled::placeholder {
  color: #ea4335; /* Kulay pula na gray para halatang disabled */
  font-weight: 500;
}
.chat-attach:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* ========================
   HOST CONTROLS PANEL UI
   ======================== */
.host-panel-body {
  display: flex;
  flex-direction: column;
  padding: 16px;
  gap: 16px;
  flex: 1;
  overflow-y: auto;
  border-radius: 0 0 12px 12px;
  scrollbar-width: thin; 
  scrollbar-color: #3c4043 transparent;
}
.host-info-card {
  background: rgba(255,255,255,0.05);
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 13px;
  color: #9aa0a6;
  line-height: 1.5;
}
.host-controls-wrapper {
  border: 1px solid rgba(26, 115, 232, 0.3);
  border-radius: 8px;
  overflow: hidden;
}
.host-controls-header {
  background: rgba(26, 115, 232, 0.15);
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #8ab4f8;
  font-size: 14px;
  font-weight: 500;
}
.host-control-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background: transparent;
}
.control-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding-right: 16px;
}
.control-text strong { color: #e8eaed; font-size: 14px; font-weight: 500; }
.control-text small { color: #9aa0a6; font-size: 12px; line-height: 1.4; }

/* Toggle Switch CSS (Kung wala pa ito sa CSS mo, idagdag ito) */
.switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #5f6368; transition: .4s; }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
input:checked + .slider { background-color: #1a73e8; }
input:checked + .slider:before { transform: translateX(18px); }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }

/* New Mic Indicator UI */
.people-mic-indicator {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.people-mic-indicator.muted {
    background-color: #ea4335; /* Standard Meet Muted Red */
}

.people-mic-indicator.speaking {
    background-color: #8ab4f8; /* Standard Meet Speaking Blue */
}

.people-mic-indicator .muted-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Container ng sound waves (Tahimik State) */
.people-mic-indicator .side-wave {
  display: flex;
  align-items: center;
  gap: 3px;
  height: 16px;
}

/* Mga tuldok kapag tahimik */
.people-mic-indicator .side-wave .s-bar {
  width: 3px;
  height: 4px;
  background-color: #8ab4f8; /* FIX: Pinalitan natin ng BLUE mula sa Gray! */
  border-radius: 4px;
  transition: background-color 0.2s, height 0.2s;
}

/* ========================
   WAVE ANIMATION (Kapareho ng Main Mic Button)
   ======================== */
.people-mic-indicator.speaking {
  background-color: #a8c7fa; /* Light blue circle */
}

/* Kapag nagsasalita, magiging dark blue at magwi-wave */
.people-mic-indicator.speaking .side-wave .s-bar {
  background-color: #0842a0; /* Dark blue bars sa loob */
  animation: side-bounce-wave 0.5s infinite alternate ease-in-out;
}

/* Iba't ibang height para maganda ang wave effect */
.people-mic-indicator.speaking .side-wave .s-bar:nth-child(1) { height: 10px; animation-delay: 0s; }
.people-mic-indicator.speaking .side-wave .s-bar:nth-child(2) { height: 16px; animation-delay: 0.15s; }
.people-mic-indicator.speaking .side-wave .s-bar:nth-child(3) { height: 12px; animation-delay: 0.3s; }

@keyframes side-bounce-wave {
  0% { transform: scaleY(0.4); opacity: 0.8; }
  100% { transform: scaleY(1); opacity: 1; }
}
/* ========================
   DYNAMIC MIC BUTTON (Google Meet Style)
   ======================== */
.ctrl-btn.mic-btn {
  position: relative;
  width: 48px;
  height: 48px;
  padding: 0;
  transition: width 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), background 0.2s;
}

/* Lalapad siya (Pill-shape) kapag ON ang mic, kahit di nagsasalita */
.ctrl-btn.mic-btn.is-on {
  width: 86px;
  border-radius: 24px;
  background: #3c4043;
}

/* Ang bilog na naglalaman ng mismong Mic Icon */
.mic-icon-circle {
  position: absolute;
  right: 0;
  top: 0;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}

/* Mas light ang background ng bilog kapag naka-ON */
.ctrl-btn.mic-btn.is-on .mic-icon-circle {
  background: #4a4e51; 
}

/* Container ng sound waves (Nasa kaliwa) */
.mic-wave {
  position: absolute;
  left: 14px;
  top: 0;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Kapag TAHIMIK: tatlong maliit na tuldok (dots) */
.mic-wave .wave-bar {
  width: 4px;
  height: 4px;
  background: #8ab4f8; /* Kulay asul */
  border-radius: 4px;
  transition: height 0.2s ease;
}

/* Kapag NAGSASALITA: Gagalaw at hahaba ang mga tuldok */
.ctrl-btn.mic-btn.is-speaking .wave-bar {
  animation: bounce-wave 0.5s infinite alternate ease-in-out;
}
.ctrl-btn.mic-btn.is-speaking .wave-bar:nth-child(1) { height: 10px; animation-delay: 0s; }
.ctrl-btn.mic-btn.is-speaking .wave-bar:nth-child(2) { height: 16px; animation-delay: 0.15s; }
.ctrl-btn.mic-btn.is-speaking .wave-bar:nth-child(3) { height: 12px; animation-delay: 0.3s; }

@keyframes bounce-wave {
  0% { transform: scaleY(0.5); opacity: 0.6; }
  100% { transform: scaleY(1); opacity: 1; }
}

/* ========================
   SPLIT BUTTONS & EFFECTS MENU
   ======================== */
.ctrl-group { display: flex; align-items: center; background: #3c4043; border-radius: 24px; position: relative; height: 48px; transition: background 0.2s; }
.ctrl-group.off { background: #ea4335 !important; }
.ctrl-group:hover:not(.off) { background: #4a4e51; }
.ctrl-group.off:hover { background: #c5221f !important; }
.ctrl-group-main { width: 48px; height: 100%; background: transparent; border: none; border-radius: 24px 0 0 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.ctrl-group-arrow { width: 24px; height: 100%; background: transparent; border: none; border-left: 1px solid rgba(255, 255, 255, 0.2); border-radius: 0 24px 24px 0; display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0; }
.ctrl-group-arrow:hover { background: rgba(255, 255, 255, 0.1); }

/* CAM MINI MENU */
.cam-mini-menu { position: absolute; bottom: calc(100% + 12px); left: 50%; transform: translateX(-50%); background: #2d2e30; border-radius: 8px; padding: 8px 0; width: 240px; box-shadow: 0 4px 16px rgba(0,0,0,0.5); z-index: 100; }
.menu-cam-title { padding: 8px 20px; font-size: 11px; text-transform: uppercase; color: #9aa0a6; letter-spacing: 0.5px; font-weight: 500; }
.menu-cam-item { padding: 10px 20px; display: flex; align-items: center; gap: 12px; font-size: 14px; color: #e8eaed; }
.active-cam { color: #8ab4f8; }
.menu-divider { height: 1px; background: #3c4043; margin: 8px 0; }
.menu-action-btn { width: 100%; text-align: left; background: transparent; border: none; padding: 10px 20px; color: #e8eaed; font-size: 14px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: background 0.2s; }
.menu-action-btn:hover { background: rgba(255,255,255,0.08); }

/* EFFECTS SIDE PANEL */
.effects-preview-box { margin: 16px; height: 140px; background: #000; border-radius: 8px; overflow: hidden; position: relative; flex-shrink: 0; border: 1px solid #3c4043; }
.mini-preview-vid { width: 100%; height: 100%; object-fit: cover; }
.mini-preview-vid.mirrored { transform: scaleX(-1); }
.preview-loading { position: absolute; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; color: #8ab4f8; font-size: 13px; font-weight: 500; }

.effects-tabs { display: flex; border-bottom: 1px solid #3c4043; padding: 0 16px; flex-shrink: 0; }
.effects-tabs button { flex: 1; background: none; border: none; color: #9aa0a6; padding: 12px 0; font-size: 13px; font-weight: 500; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; }
.effects-tabs button:hover { color: #e8eaed; }
.effects-tabs button.active { color: #8ab4f8; border-bottom-color: #8ab4f8; }

.effects-scroll-area { flex: 1; overflow-y: auto; padding: 16px; scrollbar-width: thin; scrollbar-color: #3c4043 transparent; }
.tab-pane { display: flex; flex-direction: column; gap: 24px; }

.effects-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.effect-btn { position: relative; aspect-ratio: 1; border-radius: 8px; border: 2px solid transparent; background: #3c4043; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; padding: 0; transition: transform 0.1s, border-color 0.2s; }
.effect-btn:hover { transform: scale(1.05); }
.effect-btn.active { border-color: #8ab4f8; }
.effect-btn img { width: 100%; height: 100%; object-fit: cover; }
.eff-label { font-size: 10px; color: #9aa0a6; margin-top: 4px; }
.cat-title { font-size: 13px; color: #e8eaed; font-weight: 500; margin: 0 0 12px 0; }

.tab-desc { font-size: 13px; color: #9aa0a6; text-align: center; margin: 0; }
.filter-placeholder { aspect-ratio: 1; background: #3c4043; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; }
.appearance-card { background: rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; position: relative; }
.appearance-card h4 { margin: 0 0 4px 0; color: #e8eaed; font-size: 14px; font-weight: 500; }
.appearance-card p { margin: 0; color: #9aa0a6; font-size: 12px; max-width: 80%; }
.appearance-card .switch { position: absolute; top: 16px; right: 16px; }

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
.video-wrap { position: relative; border-radius: 12px; overflow: hidden; background: #202124; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); box-sizing: border-box; }
.wrap-hidden { display: none; }

.grid-container {
  display: flex;
  padding: 16px;
  padding-bottom: 16px; 
  height: 100%;
  box-sizing: border-box;
  /* PERFECT SYNC: Set to 0.6s to match the side panel animation exactly */
  transition: padding 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* SHRINK EFFECT KAPAG MAY CHAT/PANEL */
.grid-container.with-panel {
  padding-right: 390px; 
}

/* =======================================
   ANIMATIONS PARA SA CHAT PANEL & EMOJI
   ======================================= */

/* 1. SHRINK UP: Kapag in-open ang Emoji Picker */
.grid-container.with-emoji {
  padding-bottom: 86px; 
}
.grid-container.with-emoji .participants-grid.layout-pip .remote-camera {
  bottom: 86px;
}
.grid-container.with-emoji .participants-grid.layout-pip .local-camera {
  bottom: 100px;
}

/* 2. SHRINK LEFT: Kapag in-open ang Chat/People Panel */
.grid-container.with-panel .participants-grid.layout-pip .remote-camera {
  right: 392px; 
  left: 16px;
}
.grid-container.with-panel .participants-grid.layout-pip .local-camera {
  right: 412px; 
}

/* 3. SHRINK UP + LEFT: Sabay na naka-open ang Chat at Emoji */
.grid-container.with-panel.with-emoji .participants-grid.layout-pip .local-camera {
  bottom: 100px;
  right: 412px;
}

/* ANG GRID MISMO */
.participants-grid {
  flex: 1; 
  display: grid;
  gap: 16px;
  /* FIX: Mula 320px, ginawa nating 240px. Hahayaan muna nitong lumiit ang mga camera bago ito mag-wrap o bumaba sa next line! */
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  grid-auto-rows: 1fr; /* Para laging pantay ang pag-hati nila ng height sa screen */
  align-content: stretch;
  justify-content: center;
  height: 100%;
  transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* Kapag walang screen share, okupahin ang buong screen */
.grid-container:not(.has-screen-share) .participants-grid {
  flex: 100%;
}

/* Kapag may Screen Share, magiging isang linya (column) na lang ang mga camera sa gilid */
.grid-sidebar .grid-item {
  width: 100%;
  height: 180px;
  flex-shrink: 0;
}

/* INDIVIDUAL VIDEO TILES */
.grid-item {
  position: relative;
  background: #3c4043;
  border-radius: 12px;
  overflow: hidden;
  width: 100%;
  height: 100%;
  /* FIX: Mula 220px, binaba natin sa 120px ang minimum height para kayang lumiit ng video kapag napuno na ang screen */
  min-height: 120px; 
  max-height: 100%;
  transition: box-shadow 0.2s, border 0.2s, all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
  border: 4px solid transparent;
}

/* ACTIVE SPEAKER BORDER! */
.speaking-border {
  border-color: #8ab4f8;
  box-shadow: 0 0 16px rgba(138, 180, 248, 0.4);
}
.speaking-badge {
  background: rgba(0,0,0,0.6); /* Gawing dark ang background para umangat ang blue waves */
}

/* ========================
   MINI SOUND WAVES (Para sa Video Tiles)
   ======================== */
.mini-wave {
  display: flex;
  align-items: center;
  gap: 3px;
  height: 14px;
}

.mini-wave .mini-bar {
  width: 3px;
  background: #8ab4f8; /* Kulay asul na bars */
  border-radius: 2px;
  animation: mini-bounce 0.5s infinite alternate ease-in-out;
}

.mini-wave .mini-bar:nth-child(1) { height: 6px; animation-delay: 0s; }
.mini-wave .mini-bar:nth-child(2) { height: 12px; animation-delay: 0.15s; }
.mini-wave .mini-bar:nth-child(3) { height: 8px; animation-delay: 0.3s; }

@keyframes mini-bounce {
  0% { transform: scaleY(0.4); opacity: 0.7; }
  100% { transform: scaleY(1.1); opacity: 1; }
}

/* ========================
   SMART LAYOUT: PICTURE-IN-PICTURE (1-ON-1)
   ======================== */
.participants-grid.layout-pip {
  display: block; /* Patayin ang Grid pansamantala */
  width: 100%;
  height: 100%;
}

.participants-grid.layout-pip .remote-camera {
  position: absolute;
  top: 16px;
  left: 16px;
  right: 16px;
  bottom: 16px;
  width: auto;
  height: auto;
  z-index: 4;
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.participants-grid.layout-pip .local-camera {
  position: absolute;
  bottom: 100px;
  right: 170px;
  width: 280px;
  height: auto;
  min-height: unset; 
  max-height: unset; 
  aspect-ratio: 16 / 9;
  z-index: 12;
  box-shadow: 0 4px 12px rgba(0,0,0,0.5);
  border-width: 3px; 
  /* FIX: 0.5s para sumabay! */
  transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* ========================
   SCREEN SHARE WRAPPER (Ang pinapanood na screen)
   ======================== */
.screen-share-wrap {
  position: absolute;
  top: 16px; left: 16px; right: 16px; bottom: 16px;
  background: #000;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
/* Siguraduhing contain at black bg */
.screen-share-wrap .video-fill {
  object-fit: contain !important;
  background: #202124;
}

/* ========================
   MODE-SHARE (SOLO + Nagpe-present)
   ======================== */
.grid-container.mode-share .participants-grid {
  position: absolute; top: 0; left: 0; right: 0; bottom: 0;
  pointer-events: none; /* Para ma-click ang screen sa likod */
  z-index: 10;
  display: block; /* Patayin ang Grid layout dito */
}
.grid-container.mode-share .participants-grid .local-camera {
  pointer-events: auto; /* Ibalik ang click sa mismong camera */
  position: absolute;
  width: 280px; height: auto; aspect-ratio: 16/9;
  min-height: unset; max-height: unset;
  bottom: 100px; right: 32px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.5);
  border-width: 3px; border-radius: 12px;
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.grid-container.with-panel.mode-share .participants-grid .local-camera {
  right: 408px;
}
.grid-container.with-panel.mode-share .screen-share-wrap {
  right: 392px;
}

/* ========================
   MODE-SIDEBAR (MAY GUEST + Nagpe-present)
   ======================== */
/* Paliitin ang Screen Share para bigyan ng space ang mga camera sa gilid */
.grid-container.mode-sidebar .screen-share-wrap {
  right: 312px; /* Space para sa 280px camera + margin */
}

/* I-ayos ang mga cameras sa kanang gilid (Vertical Column) */
.grid-container.mode-sidebar .participants-grid {
  position: absolute;
  top: 16px; right: 16px; bottom: 16px;
  width: 280px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow-y: auto;
  z-index: 10;
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
/* Style ng bawat camera sa sidebar */
.grid-container.mode-sidebar .participants-grid .grid-item {
  width: 100%;
  height: 180px; /* Fixed height para pantay */
  min-height: unset; max-height: unset;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  border-width: 3px; border-radius: 12px;
  transition: all 0.4s;
  flex-shrink: 0;
}

/* Kapag nagbukas ng chat, i-usog lahat (pati Sidebar at Screen) */
.grid-container.with-panel.mode-sidebar .participants-grid {
  right: 392px;
}
.grid-container.with-panel.mode-sidebar .screen-share-wrap {
  right: 688px; /* 392 (chat) + 296 (sidebar + margin) */
}

/* IPALIT ITO: Kapag pinapanood ng Guest ang screen mo, itatago ang maliit mong camera para hindi madoble ang video mo sa screen niya */
.content-area:has(.is-remote-screen) .remote-camera { display: none !important; }

.local-solo { z-index: 5; }
.remote-fill { z-index: 4; }
.screen-fill { 
  z-index: 3; 
  background: #000; 
  display: flex; /* Idagdag ito para mag-center nang maayos */
  align-items: center; 
  justify-content: center; 
}
.screen-fill .video-fill { 
  object-fit: contain !important; /* Force natin na contain palagi */
  width: 100%; 
  height: 100%; 
}

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
.is-remote-screen {
  object-fit: contain !important;
  background: #000;
}
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
  top: 16px; 
  right: 16px; 
  bottom: 88px; 
  width: 360px; 
  background: #202124; 
  z-index: 25; 
  display: flex; 
  flex-direction: column; 
  border-radius: 12px; 
  box-shadow: 0 4px 16px rgba(0,0,0,0.3); 
  overflow: visible; /* FIX: Pinalitan ng 'visible' mula 'hidden' para makalabas ang mga dropdown! */
}

/* SMOOTH TRANSITION */
.slide-panel-enter-active, .slide-panel-leave-active { 
  transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1); /* FIX: Pantay na ang bilis nila ng camera! */
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
.panel-header { 
  display: flex; 
  align-items: center; 
  justify-content: space-between; 
  padding: 16px 20px; 
  border-bottom: 1px solid #3c4043; 
  flex-shrink: 0; 
  border-radius: 12px 12px 0 0; /* FIX: Binigyan ng round kanto ang itaas */
}
.messages-list, .people-panel-body, .info-panel-body, .effects-scroll-area {
  border-radius: 0 0 12px 12px;
}
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
.pop-center-enter-active, .pop-center-leave-active { transition: all 0.5s ease-in-out; }
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

/* ========================
   MEETING DETAILS (INFO) PANEL
   ======================== */
.info-panel-body { 
  padding: 20px; 
  display: flex; 
  flex-direction: column; 
  gap: 16px; 
}
.info-section-title { 
  font-size: 14px; 
  font-weight: 500; 
  color: #e8eaed; 
  margin: 0; 
}
.info-link-text { 
  font-size: 13px; 
  color: #e8eaed; 
  word-break: break-all; 
  line-height: 1.4;
}
.btn-copy-info { 
  display: flex; 
  align-items: center; 
  gap: 12px; 
  background: transparent; 
  border: none; 
  color: #8ab4f8; 
  font-size: 14px; 
  font-weight: 500; 
  cursor: pointer; 
  padding: 0; 
  transition: color 0.2s; 
}
.btn-copy-info:hover { color: #aecbfa; }
.info-divider { 
  height: 1px; 
  background: #3c4043; 
  margin: 8px 0; 
}
.info-attachments-text { 
  font-size: 13px; 
  color: #9aa0a6; 
}

/* ========================
   BOTTOM RIGHT & HOVER POPUPS
   ======================== */
.people-group-wrapper { display: flex; align-items: center; position: relative; gap: 8px; margin-right: 6px;}
.people-btn-wrapper, .admit-pill-wrapper { position: relative; }

.people-count-badge { background: transparent; border-left: 1px solid rgba(255,255,255,0.2); border-radius: 0; padding-left: 6px; position: static; height: auto; width: auto; font-size: 14px; font-weight: 500; color: #e8eaed; margin-left: 4px; }
.util-btn.active .people-count-badge { border-color: transparent; }

/* GREEN PILL */
.admit-green-pill { background: #a8dab5; color: #0d652d; border: none; padding: 0 16px; height: 40px; border-radius: 20px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: background 0.2s; }
.admit-green-pill:hover { background: #81c995; }

/* HOVER POPUPS */
.hover-popup { 
  position: absolute; 
  bottom: calc(100% + 16px); 
  right: 0; 
  background: #2d2e30; 
  border-radius: 12px; 
  box-shadow: 0 8px 24px rgba(0,0,0,0.5); 
  z-index: 100; 
  width: 320px; 
  display: flex; 
  flex-direction: column; 
  /* Tinanggal natin ang overflow: hidden dito! */
  cursor: default; 
}

/* INVISIBLE BRIDGE: Sasakupin nito ang gap para hindi mawala ang popup kapag inangat ang mouse */
.hover-popup::after {
  content: '';
  position: absolute;
  top: 100%; /* Magsisimula mismo sa ilalim ng popup */
  left: 0;
  width: 100%;
  height: 24px; /* Sapat na haba para takpan ang dead zone papunta sa button */
  background: transparent;
}
.admit-hover-popup { padding: 16px; gap: 16px; }
.ah-header { font-size: 16px; font-weight: 500; color: #e8eaed; display: flex; align-items: center; gap: 12px; }
.ah-badge { font-size: 11px; background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px; color: #9aa0a6; }
.ah-actions { display: flex; gap: 12px; }
.ah-btn-admit { flex: 1; background: #8ab4f8; color: #202124; border: none; padding: 10px; border-radius: 24px; font-size: 14px; font-weight: 500; cursor: pointer; }
.ah-btn-admit:hover { background: #9bbcf8; }
.ah-btn-deny { flex: 1; background: transparent; color: #8ab4f8; border: 1px solid #5f6368; padding: 10px; border-radius: 24px; font-size: 14px; font-weight: 500; cursor: pointer; }
.ah-btn-deny:hover { background: rgba(138,180,248,0.04); border-color: #8ab4f8; }
.ah-guest-info { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px; }
.ah-avatar { width: 40px; height: 40px; border-radius: 50%; background: #3c4043; display: flex; align-items: center; justify-content: center; }
.ah-details { display: flex; flex-direction: column; }
.ah-details strong { font-size: 14px; color: #e8eaed; font-weight: 500; }
.ah-details span { font-size: 12px; color: #9aa0a6; }
.ah-view-all { background: none; border: none; color: #8ab4f8; font-size: 14px; font-weight: 500; cursor: pointer; text-align: left; padding: 4px 0 0 0; }
.ah-view-all:hover { text-decoration: underline; }

.people-hover-popup { padding: 16px; gap: 16px; }
.ph-title { font-size: 16px; font-weight: 500; color: #e8eaed; }
.ph-btn-host { background: transparent; border: 1px solid #5f6368; color: #8ab4f8; padding: 10px; border-radius: 24px; font-size: 14px; font-weight: 500; cursor: pointer; text-align: center; }
.ph-btn-host:hover { background: rgba(138,180,248,0.04); border-color: #8ab4f8; }
.ph-list-box { background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px; display: flex; flex-direction: column; gap: 8px; }
.ph-joined-text { font-size: 14px; font-weight: 500; color: #e8eaed; }
.ph-just-you { font-size: 13px; color: #9aa0a6; }
.ph-avatars { display: flex; gap: 8px; margin-top: 4px; }
.ph-avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 500; font-size: 16px; }
.host-avatar { background: #8e24aa; }
.guest-avatar { background: #00897b; }
.ph-view-all { background: none; border: none; color: #8ab4f8; font-size: 14px; font-weight: 500; cursor: pointer; text-align: center; padding: 8px 0 0 0; border-top: 1px solid #3c4043; margin-top: auto;}
.ph-view-all:hover { text-decoration: underline; }

/* ========================
   PEOPLE PANEL BODY UI
   ======================== */
/* ========================
   PEOPLE PANEL BODY UI
   ======================== */
.people-panel-body { 
  display: flex; 
  flex-direction: column; 
  padding: 16px; 
  gap: 24px; 
  flex: 1;
  /* TINANGGAL: overflow-y: auto; dahil pinuputol nito ang menu! */
}
.people-search { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.05); padding: 10px 16px; border-radius: 4px; border: 1px solid #5f6368; }
.people-search input { background: transparent; border: none; color: #e8eaed; font-size: 14px; flex: 1; outline: none; }
.people-group-section { 
  display: flex; 
  flex-direction: column; 
  border: 1px solid #3c4043; 
  border-radius: 8px; 
  overflow: visible; /* FIX: Ginawang visible para makalabas ang Dropdown Menu! */
}
.pg-header { border-radius: 8px 8px 0 0; }
.participant-item:last-child { border-radius: 0 0 8px 8px; }
.pg-header { display: flex; justify-content: space-between; padding: 12px 16px; background: rgba(255,255,255,0.02); font-size: 13px; font-weight: 500; color: #e8eaed; border-bottom: 1px solid #3c4043; }
.pg-actions-top { display: flex; justify-content: flex-end; padding: 8px 16px; }
.btn-text-blue { background: none; border: none; color: #8ab4f8; font-size: 14px; font-weight: 500; cursor: pointer; }
.btn-text-blue:hover { color: #aecbfa; }

.participant-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; transition: background 0.2s; }
.participant-item:hover { background: rgba(255,255,255,0.05); }
.participant-avatar { width: 36px; height: 36px; border-radius: 50%; background: #3c4043; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: white; font-weight: 500; }
.participant-name { flex: 1; display: flex; flex-direction: column; color: #e8eaed; font-size: 14px; font-weight: 500; }
.host-label { font-size: 12px; color: #9aa0a6; font-weight: 400; margin-top: 2px;}
.waiting-actions { display: flex; align-items: center; gap: 12px; }

.menu-wrap { position: relative; display: flex; align-items: center; }
.context-dropdown { 
  position: absolute; 
  top: 100%; 
  right: 0; 
  background: #2d2e30; 
  border-radius: 8px; 
  box-shadow: 0 4px 16px rgba(0,0,0,0.5); 
  padding: 8px 0; 
  z-index: 1000; /* Tinaasan ng bongga para hindi matakpan ng kahit ano */
}
.context-dropdown button { background: none; border: none; color: #e8eaed; display: flex; align-items: center; gap: 12px; padding: 10px 20px; width: 100%; text-align: left; cursor: pointer; font-size: 14px; white-space: nowrap; }
.context-dropdown button:hover { background: rgba(255,255,255,0.1); }

/* Blue Speaking Indicator */
/* ========================
   NEW BLUE WAVE BADGE (People Panel)
   ======================== */
.blue-wave-badge {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #a8c7fa; /* Light blue background */
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse-blue 1s infinite alternate;
}
.blue-wave-badge .mini-wave {
  gap: 2px;
  height: 12px;
}
.blue-wave-badge .mini-bar {
  width: 3px;
  background: #0842a0; /* Dark blue wave sa loob */
}

@keyframes pulse-blue {
  0% { transform: scale(0.9); opacity: 0.8; }
  100% { transform: scale(1.1); opacity: 1; }
}

/* ========================
   SUBMENU (Pin Dropdown)
   ======================== */
.pin-dropdown {
  width: max-content;
  padding: 0 !important; /* Reset padding para sa full width hover */
}

.submenu-container {
  position: relative;
  width: 100%;
  padding: 8px 0;
}

.submenu-trigger {
  justify-content: space-between;
  width: 100%;
}

.submenu-popup {
  display: none;
  position: absolute;
  right: 100%; 
  /* FIX: Ibinaba natin mula -8px papuntang 0 para hindi matakpan ang 'Meeting host' text */
  top: 0; 
  background: #2d2e30;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.5);
  padding: 8px 0;
  width: max-content; 
  z-index: 1001; 
}

.submenu-popup button {
  background: none; 
  border: none; 
  color: #e8eaed; 
  display: block; 
  width: 100%; 
  text-align: left; 
  cursor: pointer; 
  font-size: 14px; 
  white-space: nowrap;
  /* Reduced horizontal padding, especially on the right */
  padding: 10px 16px 10px 20px; 
}

.submenu-popup button:hover {
  background: rgba(255,255,255,0.1);
}

/* Invisible Bridge para hindi magsara kapag tinawid ang mouse pakaliwa */
.submenu-container::before {
  content: '';
  position: absolute;
  right: 100%;
  top: 0;
  width: 12px;
  height: 100%;
}

.submenu-container:hover .submenu-popup {
  display: block; /* Lilitaw lang kapag ni-hover ang container! */
}

/* ======================== */
/*    MOBILE RESPONSIVE     */
/* ======================== */
@media (max-width: 768px) {
  /* 1. Ayusin ang Video Size (Ibalik sa full width ang screen) */
  .local-solo, .remote-fill, .screen-fill {
    top: 16px;
    left: 16px;
    right: 16px;
    bottom: 16px; /* Bigyan ng space yung controls sa ibaba */
  }

  .screen-fill .video-fill {
    object-fit: contain !important;
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

/* MOBILE FIX PARA SA PIP (Layout-PiP) */
@media (max-width: 768px) {
  /* ========================
     MOBILE FIX PARA SA LAHAT NG LAYOUT MODES
     ======================== */
     
  /* 1. NORMAL PIP (Kapag walang screen share, 1-on-1) */
  .participants-grid.layout-pip .remote-camera {
    top: 16px; left: 16px; right: 16px; bottom: 16px;
  }
  .participants-grid.layout-pip .local-camera {
    position: absolute !important;
    bottom: 30px !important;
    right: 30px !important;
    width: 120px !important;
    height: 160px !important;
    aspect-ratio: 3/4 !important; /* Portrait view sa mobile! */
    z-index: 20 !important;
  }

  /* 2. MODE-SHARE (Kapag SOLO ka at nag-share ng screen) */
  .grid-container.mode-share .screen-share-wrap {
    right: 16px !important; /* Full width screen share */
    top: 16px; bottom: 16px;
  }
  .grid-container.mode-share .participants-grid .local-camera {
    position: absolute !important;
    bottom: 100px !important;
    right: 16px !important;
    width: 100px !important;
    height: 140px !important;
    aspect-ratio: 3/4 !important;
    z-index: 20 !important;
  }

  /* 3. MODE-SIDEBAR (May GUEST at may nagpe-present - KATULAD NG GMEET MOBILE!) */
  .grid-container.mode-sidebar .screen-share-wrap {
    right: 16px !important;
    bottom: 172px !important; /* I-angat ang screen share para may space ang cameras sa ibaba */
  }
  .grid-container.mode-sidebar .participants-grid {
    position: absolute !important;
    top: auto !important;
    bottom: 16px !important; 
    left: 16px !important;
    right: 16px !important;
    height: 150px !important; /* Limitadong height para sa isang row lang ang kita agad */
    
    /* GAGAWING GRID PARA BUMABA ANG SUMUNOD NA GUESTS */
    display: grid !important; 
    grid-template-columns: repeat(2, 1fr) !important; /* Laging dalawa-dalawa (2 columns) */
    grid-auto-rows: 140px !important; /* Fixed height ng bawat camera box */
    gap: 8px !important;
    overflow-y: auto !important; /* Scrollable pababa kapag sumobra na sa dalawa ang tao */
    align-content: start !important;
  }
  
  /* I-reset ang mga camera boxes para maging side-by-side sa ibaba */
  .grid-container.mode-sidebar .participants-grid .grid-item,
  .grid-container.mode-sidebar .participants-grid .local-camera,
  .grid-container.mode-sidebar .participants-grid .remote-camera {
    display: block !important; 
    position: relative !important;
    bottom: auto !important;
    right: auto !important;
    left: auto !important;
    width: 100% !important; /* Hayaan na ang grid template mag-hati sa dalawa (1fr) */
    height: 100% !important; 
    aspect-ratio: auto !important;
    z-index: 10 !important;
  }

  .participants-grid:not(.layout-pip) {
    grid-template-columns: repeat(2, 1fr) !important; /* Force 2-columns sa mobile */
    grid-auto-rows: minmax(160px, 1fr) !important; /* Awtomatikong mag-aadjust ang height ng camera */
    overflow-y: auto !important;
  }
}

/* Ayusin din ang lapad sa Mobile View kung mag-collapse ang layout */
@media (max-width: 768px) {
  .ctrl-btn.mic-btn.is-speaking {
    width: 68px;
    gap: 6px;
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

  /* --- DAGDAG: CHAT ENHANCEMENTS CSS --- */
.chat-attach {
  width: 36px; height: 36px; border-radius: 50%; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 0.2s;
}
.chat-attach:hover { background: rgba(255,255,255,0.1); }

/* Para clickable links sa loob ng chat text */
:deep(.chat-inline-link) {
  color: #8ab4f8; text-decoration: underline; word-break: break-all; cursor: pointer;
}
.message-item.own :deep(.chat-inline-link) {
  color: #e8eaed; /* Gawing light gray ang link pag ikaw ang nag-send (blue background) */
}

/* File Attachment Styles */
.msg-file-attachment {
  margin-top: 4px; border-radius: 8px; overflow: hidden; max-width: 200px;
}
.chat-image {
  width: 100%; height: auto; border-radius: 8px; cursor: pointer; border: 1px solid rgba(255,255,255,0.1); transition: opacity 0.2s;
}
.chat-image:hover { opacity: 0.8; }
/* Palitan ang .chat-file-link ng code na ito: */
.chat-file-link {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #3c4043;
  color: #e8eaed;
  padding: 8px 12px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 13px;
  border: 1px solid rgba(255,255,255,0.1);
  word-break: break-all;
  width: fit-content;
  max-width: 100%;
}

.message-item.own .chat-file-link { 
  background: rgba(255,255,255,0.15); 
  color: #fff; 
  border-color: transparent;
}

.chat-file-link:hover { 
  background: rgba(255,255,255,0.2); 
}

/* DAGDAG ITO: Puting background box sa likod ng SVG icon para laging lutang ang kulay */
.file-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  padding: 6px;
  border-radius: 6px;
  flex-shrink: 0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.file-name-text {
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2; 
  line-clamp: 2; 
  -webkit-box-orient: vertical;
}
/* --- END DAGDAG --- */
</style>