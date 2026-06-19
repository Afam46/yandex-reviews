<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-2 opacity-0"
    >
        <div
            v-if="successText"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl
            border border-green-500/20 bg-green-500/10 p-4 text-green-400 shadow-lg backdrop-blur-sm"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-sm font-medium">{{ successText }}</span>
        </div>
    </Transition>

    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-2 opacity-0"
    >
        <div
            v-if="errorText"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl
            border border-red-500/20 bg-red-500/10 p-4 text-red-400 shadow-lg backdrop-blur-sm"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span class="text-sm font-medium">{{ errorText }}</span>
        </div>
    </Transition>
</template>

<script setup>
defineProps({
    errorText: {
        type: String,
        default: ''
    },
    successText: {
        type: String,
        default: ''
    }
})
</script>