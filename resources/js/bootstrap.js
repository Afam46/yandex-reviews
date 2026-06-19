import axios from 'axios'

axios.defaults.baseURL = import.meta.env.VITE_API_URL

axios.defaults.headers.common.Accept = 'application/json'

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token')

    if (token) {
        config.headers.Authorization =
            `Bearer ${token}`
    }

    return config
})

window.axios = axios

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,

    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,

    forceTLS: false,
    enabledTransports: ['ws'],

    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json',
        },
    },
})