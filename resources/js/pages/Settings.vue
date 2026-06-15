<template>
    <div
        class="
            min-h-screen bg-slate-950 py-16 px-4
        "
    >
        <div
            class="
                max-w-4xl mx-auto bg-slate-900
                border border-slate-800 rounded-3xl p-8
            "
        >
            <h1
                class="
                    text-3xl font-bold mb-8 text-white
                "
            >
                Подключение организации
            </h1>

            <form
                class="space-y-5"
                @submit.prevent="saveOrganization"
            >
                <div>

                    <label
                        class="
                            block text-white mb-2
                        "
                    >
                        Ссылка на организацию в Яндекс.Картах
                    </label>

                    <input
                        v-model="url"
                        type="text"
                        placeholder="https://yandex.ru/maps/org/..."
                        class=" w-full bg-slate-800 border border-slate-700
                            rounded-xl px-4 py-3 focus:outline-none
                            focus:border-blue-500 text-white
                        "
                    >

                </div>

                <button
                    :disabled="loading"
                    class="
                        bg-blue-600 hover:bg-blue-700 transition
                        rounded-xl px-6 py-3 disabled:opacity-50
                        cursor-pointer text-white
                    "
                >
                    {{ loading ? 'Подключаем...' : 'Подключить' }}
                </button>

            </form>

            <div
                v-if="organization"
                class="mt-12"
            >
                <h2
                    class="
                        text-2xl font-bold mb-8 text-white
                    "
                >
                    Информация об организации
                </h2>

                <div
                    class="
                        grid md:grid-cols-3 gap-4
                    "
                >
                    <div
                        class="
                            bg-slate-800 rounded-2xl p-5
                        "
                    >
                        <div class="text-slate-400 mb-2">
                            Рейтинг
                        </div>

                        <div class="text-3xl font-bold text-white">
                            {{ organization.rating ?? '-' }}
                        </div>
                    </div>

                    <div
                        class="
                            bg-slate-800 rounded-2xl p-5
                        "
                    >
                        <div class="text-slate-400 mb-2">
                            Оценок
                        </div>

                        <div class="text-3xl font-bold text-white">
                            {{ organization.ratings_count }}
                        </div>
                    </div>

                    <div
                        class="
                            bg-slate-800 rounded-2xl p-5
                        "
                    >
                        <div class="text-slate-400 mb-2">
                            Отзывов
                        </div>

                        <div class="text-3xl font-bold text-white">
                            {{ organization.reviews_count }}
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="reviews.length"
                class="mt-12"
            >
                <h2
                    class="
                        text-2xl font-bold mb-8 text-white
                    "
                >
                    Отзывы
                </h2>

                <div class="space-y-4">

                    <div
                        v-for="review in reviews"
                        :key="review.id"
                        class="
                            bg-slate-800 rounded-2xl p-5
                        "
                    >
                        <div
                            class="
                                flex justify-between mb-3
                            "
                        >
                            <div class="font-medium text-white">
                                {{ review.author }}
                            </div>

                            <div class="text-yellow-400">
                                ⭐ {{ review.rating }}
                            </div>
                        </div>

                        <div class="text-slate-300">
                            {{ review.text }}
                        </div>

                        <div
                            class="
                                text-sm text-slate-500 mt-3
                            "
                        >
                            {{ review.review_date }}
                        </div>
                    </div>

                </div>

                <div
                    class="
                        flex justify-center gap-2 mt-8
                    "
                >
                    <button
                        @click="changePage(page - 1)"
                        :disabled="page === 1"
                        class="
                            px-4 py-2 rounded-xl bg-slate-800
                            disabled:opacity-50 cursor-pointer text-white
                        "
                    >
                        ←
                    </button>

                    <div
                        class="
                            px-4 py-2 rounded-xl bg-slate-800 text-white
                        "
                    >
                        {{ page }}
                    </div>

                    <button
                        @click="changePage(page + 1)"
                        :disabled="!hasMore"
                        class="
                            px-4 py-2 rounded-xl bg-slate-800
                            disabled:opacity-50 cursor-pointer text-white
                        "
                    >
                        →
                    </button>
                </div>

            </div>

        </div>
    </div>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

const url = ref('')

const loading = ref(false)

const organization = ref(null)

const reviews = ref([])

const page = ref(1)

const hasMore = ref(false)

const loadOrganization = async () => {

    try{

        const res = await axios.get('/api/organization')

        organization.value = res.data

        await loadReviews()

    }catch(e){

        console.log(e)
    }
}

const loadReviews = async () => {

    try{

        const res = await axios.get(`/api/reviews?page=${page.value}`)

        reviews.value = res.data.data

        hasMore.value = res.data.current_page < res.data.last_page

    }catch(e){

        console.log(e)
    }
}

const saveOrganization = async () => {

    loading.value = true

    try{

        await axios.post('/api/organization', {
            url: url.value
        })

        await loadOrganization()

    }catch(e){

        console.log(e)

    }finally{

        loading.value = false
    }
}

const changePage = async (newPage) => {

    page.value = newPage

    await loadReviews()
}

onMounted(() => {
    loadOrganization()
})
</script>