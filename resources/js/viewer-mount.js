import { createApp } from 'vue'
import ViewerStatus from './Components/ViewerStatus.vue'

const el = document.getElementById('viewer-status')
if (el) {
  const roomId = el.dataset.roomId
  createApp(ViewerStatus, { roomId }).mount(el)
}
