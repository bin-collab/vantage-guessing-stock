<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
}>();
</script>

<template>
    <Head title="美股竞猜活动 - Vantage" />

    <div class="min-h-screen bg-[#0a0a0a] text-white selection:bg-blue-500/30">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full border-b border-white/5 bg-black/50 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600"></div>
                    <span class="text-xl font-bold tracking-tight">Vantage Guessing</span>
                </div>
                <div class="flex items-center gap-6">
                    <Link href="/login" class="rounded-full bg-white px-6 py-2 text-sm font-semibold text-black transition-all hover:bg-gray-200">
                        立即参与
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative pt-32 pb-20">
            <!-- Background Glow -->
            <div class="absolute top-0 left-1/2 -z-10 h-[600px] w-full -translate-x-1/2 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-blue-500/20 via-transparent to-transparent blur-3xl"></div>

            <div class="mx-auto max-w-7xl px-6 text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-sm font-medium text-blue-400">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                    </span>
                    活动日期: 2026年7月6日 - 7月10日
                </div>

                <h1 class="mt-8 text-5xl font-extrabold tracking-tight sm:text-7xl">
                    竞猜美股收盘价 <br />
                    <span class="bg-gradient-to-r from-blue-400 to-indigo-500 bg-clip-text text-transparent">赢取 $500 大奖</span>
                </h1>

                <p class="mx-auto mt-8 max-w-2xl text-lg text-gray-400">
                    竞猜 6 只热门美股每日收盘价。只需猜中整数部分，即可赢取 $50 产品券。累计猜中次数最多者更可获赠 $500 Apple Gift Card！
                </p>

                <div class="mt-12 flex flex-wrap justify-center gap-4">
                    <Link href="/login" class="group relative flex items-center gap-2 overflow-hidden rounded-full bg-blue-600 px-8 py-4 font-bold transition-all hover:bg-blue-500 hover:shadow-[0_0_20px_rgba(37,99,235,0.4)]">
                        开始竞猜
                        <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <a href="#rules" class="rounded-full border border-white/10 bg-white/5 px-8 py-4 font-bold backdrop-blur-sm transition-all hover:bg-white/10">
                        查看规则
                    </a>
                </div>

                <!-- Featured Stocks -->
                <div class="mt-24 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <div v-for="stock in ['TSLA', 'NVIDIA', 'AAPL', 'GOOG', 'META', 'AMAZON']" :key="stock" 
                        class="rounded-2xl border border-white/5 bg-white/5 p-6 backdrop-blur-sm">
                        <div class="text-sm font-medium text-gray-500">{{ stock }}.24H</div>
                        <div class="mt-1 text-xl font-bold">{{ stock }}</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Rules Section -->
        <section id="rules" class="py-24 bg-white/5 backdrop-blur-sm border-t border-white/5">
            <div class="mx-auto max-w-7xl px-6">
                <h2 class="text-3xl font-bold text-center mb-16">活动规则</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-8 rounded-3xl bg-black/40 border border-white/5">
                        <div class="h-12 w-12 rounded-2xl bg-blue-500/10 flex items-center justify-center mb-6">
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-4">如何竞猜</h3>
                        <p class="text-gray-400 leading-relaxed">
                            每日 6:00 PM 前提交当日 6 只股票的收盘价预测。只需猜中整数部分（例如 396.72 猜中 396 即可）。
                        </p>
                    </div>
                    <div class="p-8 rounded-3xl bg-black/40 border border-white/5">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center mb-6">
                            <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-4">即时奖励</h3>
                        <p class="text-gray-400 leading-relaxed">
                            每猜中一次，即可获赠对应产品的 $50 交易券。奖励可累计，多猜多得！
                        </p>
                    </div>
                    <div class="p-8 rounded-3xl bg-black/40 border border-white/5">
                        <div class="h-12 w-12 rounded-2xl bg-purple-500/10 flex items-center justify-center mb-6">
                            <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-4">大奖争夺</h3>
                        <p class="text-gray-400 leading-relaxed">
                            累计猜中次数最多者获 $500 Apple Gift Card。若次数相同，活动期间入金金额高者胜出。
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-white/5 text-center text-gray-500 text-sm">
            <p>&copy; 2026 Vantage International. All rights reserved.</p>
        </footer>
    </div>
</template>
