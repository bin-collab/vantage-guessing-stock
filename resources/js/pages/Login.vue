<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    uid: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('uid'),
    });
};
</script>

<template>
    <Head title="登录 - 美股竞猜" />

    <div class="flex min-h-screen items-center justify-center bg-[#0a0a0a] p-6 text-white selection:bg-blue-500/30">
        <div class="w-full max-w-md">
            <div class="mb-10 text-center">
                <div class="mx-auto h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 mb-6"></div>
                <h1 class="text-3xl font-bold tracking-tight">欢迎回来</h1>
                <p class="mt-2 text-gray-400">请输入您的 Email 和 UID 参与竞猜</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-400 mb-2">Email 地址</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 outline-none transition-all focus:border-blue-500 focus:bg-white/10"
                        placeholder="your@email.com"
                    />
                    <div v-if="form.errors.email" class="mt-2 text-xs text-red-500">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="uid" class="block text-sm font-medium text-gray-400 mb-2">UID</label>
                    <input
                        id="uid"
                        v-model="form.uid"
                        type="text"
                        required
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 outline-none transition-all focus:border-blue-500 focus:bg-white/10"
                        placeholder="您的系统 UID"
                    />
                    <div v-if="form.errors.uid" class="mt-2 text-xs text-red-500">{{ form.errors.uid }}</div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-blue-600 py-4 font-bold transition-all hover:bg-blue-500 disabled:opacity-50"
                >
                    <span v-if="form.processing">正在登录...</span>
                    <span v-else>立即登录</span>
                </button>

                <div v-if="form.errors.message" class="rounded-xl border border-red-500/50 bg-red-500/10 p-4 text-center text-sm text-red-500">
                    {{ form.errors.message }}
                </div>
            </form>

            <p class="mt-10 text-center text-sm text-gray-500">
                还没有参与资格？请联系您的客户经理进行 Opt-in 注册。
            </p>
        </div>
    </div>
</template>
