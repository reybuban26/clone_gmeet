import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import HomePage from './views/HomePage.vue'
import PreJoinPage from './views/PreJoinPage.vue'
import MeetingRoom from './views/MeetingRoom.vue'
import LeftMeeting from './views/LeftMeeting.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',                           component: HomePage    },
    { path: '/meeting/:code/prejoin',      component: PreJoinPage },
    { path: '/meeting/:code',              component: MeetingRoom },
    { path: '/meeting/:code/left',         component: LeftMeeting },
  ],
})

createApp(App).use(router).mount('#app')
