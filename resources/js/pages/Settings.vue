<template>
    <div
        class="
            min-h-screen bg-slate-950 py-16 px-4
        "
    >
        <div
            class="
                max-w-6xl mx-auto bg-slate-900
                border border-slate-800 rounded-3xl p-8
            "
        >
            <div
                v-if="organizations.length" class="mb-8"
            >
                <div
                    v-for="item in organizations" :key="item.id"
                    class="p-4 rounded-xl mb-3 bg-slate-800 flex justify-between items-center"
                >
                    
                    <div class="font-bold text-xl text-white">

                        <template v-if="item.status === 'pending'">
                            ⏳ Организация добавлена в очередь
                        </template>

                        <template v-else-if="item.status === 'parsing'">
                            🔄 Парсинг отзывов...
                        </template>

                        <template v-else-if="item.status === 'completed'">
                            {{ item.title }}
                        </template>

                        <template v-else>
                            ❌ Не удалось загрузить организацию
                        </template>

                    </div>

                    <div class="flex items-center">
                        <a
                            class="
                                bg-yellow-600 hover:bg-yellow-700 transition
                                rounded-xl px-4 py-2 disabled:opacity-50
                                cursor-pointer text-white mr-3 duration-300
                            "
                            type="button"
                            target="_blank"
                            :href="item.url"
                        >
                            На Yandex
                        </a> 

                        <button
                            class="
                                bg-blue-600 hover:bg-blue-700 transition
                                rounded-xl px-4 py-2 disabled:opacity-50
                                cursor-pointer text-white mr-3 duration-300
                            "
                            type="button"
                            @click.prevent="selectedOrganization = item; page = 1; loadReviews();"
                        >
                            Открыть
                        </button> 

                        <button
                            class="
                                bg-red-600 hover:bg-red-700 transition
                                rounded-xl px-4 py-2 disabled:opacity-50
                                cursor-pointer text-white duration-300
                            "
                            type="button"
                            @click.prevent="deleteOrganization(item.id)"
                        >
                            Удалить
                        </button>               
                    </div>
                </div>
            </div>

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
                <div class="relative">

                    <label
                        class="
                            block text-white mb-2
                        "
                    >
                        Ссылка на организацию в Яндекс картах
                    </label>

                    <input
                        v-model="url"
                        @input="urlError = ''"
                        type="text"
                        placeholder="https://yandex.ru/maps/org/..."
                        :class="[
                            'w-full rounded-xl px-4 py-3 focus:outline-none text-white bg-slate-800 border',
                            urlError ?
                            'border-red-500 focus:border-red-500' :
                            'border-slate-700 focus:border-blue-500'
                        ]"
                    />
                </div>

                <button
                    class="
                        bg-blue-600 hover:bg-blue-700 transition
                        rounded-xl px-6 py-3 disabled:opacity-50
                        cursor-pointer text-white duration-300
                    "
                >
                    Подключить
                </button>
            </form>

            <div v-if="selectedOrganization" class="mt-4">

                <div
                    v-if="selectedOrganization.status === 'pending'"
                    class="text-yellow-400"
                >
                    ⏳ Организация добавлена в очередь
                </div>

                <div
                    v-else-if="selectedOrganization.status === 'parsing'"
                    class="text-blue-400"
                >
                    🔄 Парсинг отзывов...
                </div>

                <div
                    v-else-if="selectedOrganization.status === 'failed'"
                    class="text-red-400"
                >
                    ❌ Не удалось загрузить организацию
                </div>

            </div>

            <div
                v-if="selectedOrganization"
                class="mt-12"
            >
                <h2
                    class="
                        text-2xl font-bold mb-8 text-white
                    "
                >
                    Информация об организации "{{ selectedOrganization.title }}"
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
                            {{ selectedOrganization.rating ?? '-' }}
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
                            {{ selectedOrganization.ratings_count }}
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
                            {{ selectedOrganization.reviews_count }}
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

                <div class="grid md:grid-cols-2 gap-4">

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

                        <div class="text-slate-300 min-h-18 max-h-18 overflow-auto">
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
                        {{ page }} из {{ lastPage }}
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
    <TransitionComponent :errorText="urlError" :successText="successText"/>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'
import TransitionComponent from '../components/TransitionComponent.vue'

const url = ref('')
const urlError = ref('')
let urlErrorTimer = ref(null)
const successText = ref('')

const organizations = ref([])
const selectedOrganization = ref(null)

const reviews = ref([])

const page = ref(1)
const lastPage = ref(1)

const hasMore = ref(false)

const loadOrganization = async () => {
    const res = await axios.get('/api/organization')

    organizations.value = res.data

    if (selectedOrganization.value) {
        const fresh = organizations.value.find(o => o.id === selectedOrganization.value.id)

        if (fresh) {
            selectedOrganization.value = fresh
        }
    }
    else if (organizations.value.length) {
        selectedOrganization.value = organizations.value[0]
    }

    if (selectedOrganization.value?.status === 'completed') {
        await loadReviews()
    }
}

const loadReviews = async () => {
    if (!selectedOrganization.value || selectedOrganization.value.status !== 'completed') {
        reviews.value = []
        hasMore.value = false
        return
    }

    const res = await axios.get(
        `/api/organizations/${selectedOrganization.value.id}/reviews?page=${page.value}`
    )

    reviews.value = res.data.data
    lastPage.value = res.data.last_page

    hasMore.value = res.data.current_page < res.data.last_page
}

const saveOrganization = async () => {
    
    if (url.value.trim() === '') {
        showUrlError('Вставьте ссылку!')
        return
    }
    else if (!url.value.includes('yandex')) {
        showUrlError('Ссылка должна вести на Яндекс карты!')
        return
    }

    try {
        const res = await axios.post('/api/organization',{url: url.value})

        const newOrganization = res.data.organization

        url.value = ''

        await loadOrganization()

        selectedOrganization.value = organizations.value.find(o => o.id === newOrganization.id) ?? null

        const parsingOrganizationId = newOrganization.id

        reviews.value = []
        page.value = 1

        const timer = setInterval(async () => {
            await loadOrganization()

            const parsingOrg = organizations.value.find(o => o.id === parsingOrganizationId)

            if (!parsingOrg) {
                clearInterval(timer)
                return
            }

            if (parsingOrg.status === 'completed' || parsingOrg.status === 'failed') {
                clearInterval(timer)

                if (selectedOrganization.value?.id === parsingOrganizationId) {
                    await loadReviews()

                    if (parsingOrg.status === 'completed') {
                        successText.value = 'Организация успешно загружена!'
                        setTimeout(() => { successText.value = '' }, 3000)
                    } else {
                        showUrlError('Не удалось загрузить организацию!')
                    }
                }
            }

        }, 3000)

    } catch(e){

        if (e.response?.status === 429) {
            showUrlError('Слишком много запросов, попробуйте через минуту!')
        }
        else if (e.response?.status === 422) {
            showUrlError(e.response.data.errors.url?.[0] ?? 'Неверная ссылка!')
        }
        else{
            showUrlError('Произошла ошибка при подключении организации!')
        }

        clearErr()
    }
}

const changePage = async (newPage) => {

    page.value = newPage

    await loadReviews()
}

const deleteOrganization = async (id) => {
    await axios.post(`/api/organization/${id}/delete`)

    if (selectedOrganization.value?.id === id) {
        selectedOrganization.value = null
        reviews.value = []
        page.value = 1
    }

    await loadOrganization()
}

const showUrlError = (message) => {
    if (urlErrorTimer) {
        clearTimeout(urlErrorTimer)
        urlErrorTimer = null
    }

    urlError.value = message

    urlErrorTimer = setTimeout(() => {
        urlError.value = ''
        urlErrorTimer = null
    }, 3000)
}

onMounted(() => {
    loadOrganization()
})
</script>