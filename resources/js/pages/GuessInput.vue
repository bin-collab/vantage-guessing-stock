<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps<{
    stocks: Array<{ id: number; symbol: string; name: string }>;
    existingGuesses: Record<number, { guessed_price: number }>;
    canGuess: boolean;
    user: { email: string; uid: string };
}>();

const form = useForm({
    guesses: props.stocks.map(stock => ({
        stock_id: stock.id,
        guessed_price: props.existingGuesses[stock.id]?.guessed_price ?? '',
    })),
});

const submit = () => {
    form.post('/guess', {
        preserveScroll: true,
        onSuccess: () => {
            // Optional success notification
        },
    });
};

// Countdown Logic
const timeLeft = ref('');
let timer: any = null;

const updateCountdown = () => {
    const now = new Date();
    const deadline = new Date();
    deadline.setHours(18, 0, 0, 0);

    if (now >= deadline) {
        timeLeft.value = '已截止';
        return;
    }

    const diff = deadline.getTime() - now.getTime();
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    timeLeft.value = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

onMounted(() => {
    updateCountdown();
    timer = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const todayDateFormatted = computed(() => {
    return new Date().toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
    });
});
</script>

<template>
    <Head title="提交竞猜 - 美股竞猜" />

    <div class="min-h-screen bg-[#0a0a0a] text-white selection:bg-blue-500/30">
        <!-- Header -->
        <header class="border-b border-white/5 bg-black/50 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600"></div>
                    <span class="text-xl font-bold tracking-tight">竞猜中心</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <div class="text-xs text-gray-500">当前用户</div>
                        <div class="text-sm font-medium">{{ user.email }}</div>
                    </div>
                    <Link method="post" href="/logout" as="button" class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium transition-all hover:bg-white/5">
                        退出登录
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-12">
            <!-- Info Card -->
            <div class="mb-8 flex flex-col items-center justify-between gap-6 rounded-3xl border border-white/5 bg-white/5 p-8 backdrop-blur-sm sm:flex-row">
                <div>
                    <h2 class="text-2xl font-bold">{{ todayDateFormatted }}</h2>
                    <p class="mt-1 text-gray-400">请竞猜今日各热门美股的收盘价整数部分</p>
                </div>
                <div class="text-center sm:text-right">
                    <div class="text-sm text-gray-500 mb-1">距离今日竞猜截止还有</div>
                    <div class="text-4xl font-mono font-bold tracking-widest" :class="canGuess ? 'text-blue-500' : 'text-red-500'">
                        {{ timeLeft }}
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div v-for="(stock, index) in stocks" :key="stock.id" 
                        class="group rounded-3xl border border-white/5 bg-white/5 p-6 transition-all hover:bg-white/[0.07]">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-xs font-medium text-gray-500">{{ stock.symbol }}</div>
                                <div class="text-lg font-bold">{{ stock.name }}</div>
                            </div>
                            <div v-if="existingGuesses[stock.id]" class="rounded-full bg-green-500/10 px-3 py-1 text-xs font-bold text-green-500">
                                已提交
                            </div>
                        </div>

                        <div class="relative">
                            <input
                                v-model="form.guesses[index].guessed_price"
                                type="number"
                                :disabled="!canGuess"
                                class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-4 text-2xl font-bold outline-none transition-all focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                placeholder="0"
                            />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-medium">
                                .00 (USD)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex flex-col items-center gap-6">
                    <button
                        type="submit"
                        :disabled="form.processing || !canGuess"
                        class="w-full max-w-md rounded-2xl bg-blue-600 py-5 text-xl font-bold transition-all hover:bg-blue-500 hover:shadow-[0_0_20px_rgba(37,99,235,0.4)] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">正在提交...</span>
                        <span v-else-if="!canGuess">竞猜已结束</span>
                        <span v-else>确认提交今日竞猜</span>
                    </button>

                    <p class="max-w-md text-center text-sm text-gray-500">
                        温馨提示：在每日 6:00 PM 截止前，您可以随时修改并重新提交您的预测值。系统将以最后一次提交为准。
                    </p>
                </div>
            </form>
        </main>
    </div>
</template>

<style scoped>
/* Hide arrow buttons for number input */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>
