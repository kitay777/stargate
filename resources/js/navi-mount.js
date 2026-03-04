import { createApp } from 'vue'
import Navi from './Components/Navi.vue'

const el = document.getElementById('vue-navi')

if (el) {
  const user = JSON.parse(el.dataset.user)
  createApp(Navi, { user }).mount(el)
}