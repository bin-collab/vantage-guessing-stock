import { createI18n } from 'vue-i18n';

const messages = {
    zh: {
        language: '中文',
        login: '登入',
        uidPlaceholder: '請輸入你的UID。',
        emailPlaceholder: '請輸入你的註冊電郵地址。',
    },
    en: {
        language: 'English',
        login: 'Login',
        uidPlaceholder: 'Please enter your UID.',
        emailPlaceholder: 'Please enter your registered email address.',
    }
};

export const i18n = createI18n({
    legacy: false, // you must set `false`, to use Composition API
    locale: 'zh',
    fallbackLocale: 'en',
    messages,
});
