import { createRouter, createWebHistory } from 'vue-router'

import Login from '../pages/Login.vue'
import Settings from '../pages/Settings.vue'

const routes = [
    {
        path: '/',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/settings',
        component: Settings,
        meta: { auth: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

import { useAuthStore } from '../store/auth'

router.beforeEach(async (to) => {
    const auth = useAuthStore()

    if (!auth.loaded) {
        await auth.fetchUser()
    }

    if (to.meta.auth && !auth.user) {
        return '/'
    }

    if (to.meta.guest && auth.user) {
        return '/settings'
    }
})

export default router