<template>
    <div
        class="
            min-h-screen flex items-center
            justify-center bg-slate-950 px-4
        "
    >
        <div
            class="
                w-full max-w-md bg-slate-900 border
                border-slate-800 rounded-3xl p-8
            "
        >
            <h1 class="
                    text-3xl font-bold text-center mb-8 text-white
                "
            >
                Вход
            </h1>

            <form
                class="space-y-5"
                @submit.prevent="login"
            >
                <div>
                    <label
                        class="
                            block text-slate-400 mb-2
                        "
                    >
                        Email
                    </label>

                    <input
                        v-model="form.email"
                        type="email"
                        class=" w-full bg-slate-800 border border-slate-700
                            rounded-xl px-4 py-3 focus:outline-none
                            focus:border-blue-500 text-white
                        "
                    >

                </div>

                <div>

                    <label
                        class="
                            block text-slate-400 mb-2
                        "
                    >
                        Пароль
                    </label>

                    <input
                        v-model="form.password"
                        type="password"
                        class="
                            w-full bg-slate-800 border border-slate-700
                            rounded-xl px-4 py-3 focus:outline-none
                            focus:border-blue-500 text-white
                        "
                    >

                </div>

                <button
                    :disabled="loading"
                    class="
                        w-full bg-blue-600 hover:bg-blue-700
                        transition rounded-xl py-3 font-medium mb-5
                        disabled:opacity-50 cursor-pointer text-white
                    "
                >
                    {{ loading ? 'Вход...' : 'Войти' }}
                </button>

                <TransitionComponent :errorText="error"/>
            </form>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios'
import { reactive, ref } from 'vue'
import router from '../router/index'
import { useAuthStore } from '../store/auth'
import TransitionComponent from '../components/TransitionComponent.vue'

const loading = ref(false)
const error = ref('')

const form = reactive({
    email: '',
    password: ''
})

const login = async () => {
    loading.value = true
    error.value = ''

    try{

        const auth = useAuthStore()

        const res = await axios.post('/api/login', form)

        localStorage.setItem('token', res.data.token)

        auth.user = res.data.user
        auth.loaded = true

        await router.push('/settings')

    }catch(e){

        if (e.response?.status === 422) {
            
            error.value = 'Неверная почта или пароль!'

            setTimeout(() => {
                error.value = ''
            }, 2000)
        }

    }finally{

        loading.value = false
    }
}
</script>