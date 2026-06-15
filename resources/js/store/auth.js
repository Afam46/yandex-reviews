import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loaded: false
    }),

    actions: {
        async fetchUser() {
            try {
                const res = await axios.get('/api/me')
                this.user = res.data
            } catch {
                this.user = null
            } finally {
                this.loaded = true
            }
        }
    }
})