import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  {
    path: '/docs',
    name: 'Docs',

    component: () => import('../views/Docs.vue'),
  },
  {
    path: '/sha1',
    name: 'Sha1',

    component: () => import('../views/Sha1.vue'),
  },
  {
    path: '/install',
    name: 'Install',

    component: () => import('../views/Install.vue'),
  },
]

const router = new VueRouter({
  routes,
})

export default router
